<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * التأكد من وجود تعارض في الموعد
     */
    public function hasConflict(
        int $employeeId,
        string $date,
        string $time,
        int $userId,
        ?int $ignoreBooking = null
    ): ?string {
        $requestedTime = Carbon::parse($date)
            ->setTimeFromTimeString($time);

        $bookings = Booking::query()
            ->whereDate('date', $date)
            ->where('status', 'pending')
            ->where(function ($query) use ($employeeId, $userId) {
                $query
                    ->where('employee_id', $employeeId)
                    ->orWhere('user_id', $userId);
            })
            ->when($ignoreBooking, function ($query) use ($ignoreBooking) {
                $query->where('id', '!=', $ignoreBooking);
            })
            ->get();

        foreach ($bookings as $booking) {

            $bookingTime = Carbon::parse($booking->date)
                ->setTimeFromTimeString($booking->time);

            // نفس الموظف ونفس الوقت
            if (
                $booking->employee_id == $employeeId &&
                $bookingTime->format('H:i') === $requestedTime->format('H:i')
            ) {
                return 'هذا الموظف لديه حجز بالفعل في نفس الموعد.';
            }

            // نفس العميل ونفس الوقت
            if (
                $booking->user_id == $userId &&
                $bookingTime->format('H:i') === $requestedTime->format('H:i')
            ) {
                return 'لديك حجز بالفعل في نفس الموعد.';
            }

            $minutes = abs(
                $bookingTime->diffInMinutes($requestedTime, false)
            );

            // الموظف لديه حجز خلال أقل من 10 دقائق
            if (
                $booking->employee_id == $employeeId &&
                $minutes < 10
            ) {
                return 'يوجد حجز آخر للموظف خلال أقل من 10 دقائق.';
            }

            // العميل لديه حجز خلال أقل من 10 دقائق
            if (
                $booking->user_id == $userId &&
                $minutes < 10
            ) {
                return 'لديك حجز آخر خلال أقل من 10 دقائق.';
            }
        }

        return null;
    }

    /**
     * إنشاء الحجز
     */
    public function create(array $data): Booking
    {
        // dd($data);
        return DB::transaction(function () use ($data) {

            $booking = Booking::create([
                'user_id'     => $data['user_id'],
                'employee_id' => $data['employee_id'],
                'sub_service_id'  => $data['sub_service_id'],
                'date'        => $data['date'],
                'time'        => $data['time'],
                'status'      => 'pending',
                'turn'        => 0,
            ]);

            // الـ Queue بتاعة اليوم الحالي فقط
            if (Carbon::parse($booking->date)->isToday()) {
                $this->reorderTodayTurns();
            }

            return $booking->fresh();
        });
    }

    /**
     * تعديل الحجز
     */
    public function update(Booking $booking, array $data): bool
    {
        return DB::transaction(function () use ($booking, $data) {

            $updated = $booking->update([
                'user_id'     => $data['user_id'],
                'employee_id' => $data['employee_id'],
                'sub_service_id'  => $data['service_id'],
                'date'        => $data['date'],
                'time'        => $data['time'],
            ]);

            if (!$updated) {
                return false;
            }

            if (
                Carbon::parse($booking->date)->isToday() ||
                Carbon::parse($data['date'])->isToday()
            ) {
                $this->reorderTodayTurns();
            }

            return true;
        });
    }

    /**
     * إنهاء الحجز
     */
    public function complete(Booking $booking): bool
    {
        if ($booking->status !== 'pending') {
            return false;
        }

        $bookingDate = Carbon::parse($booking->date)
            ->setTimeFromTimeString($booking->time);

        if (now()->lt($bookingDate)) {
            return false;
        }

        return DB::transaction(function () use ($booking) {

            // الأول نخليه completed
            
            $updated = $booking->update([
                'status' => 'completed',
                'turn'   => 0,
            ]);

            if (!$updated) {
                return false;
            }

            // بعدها نعيد ترتيب باقي الطابور
            $this->reorderTodayTurns();

            return true;
        });
    }

    /**
     * إلغاء الحجز
     */
    public function cancel(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking) {

            $updated = $booking->update([
                'status' => 'cancelled',
                'turn'   => 0,
            ]);

            if (!$updated) {
                return false;
            }

            if (Carbon::parse($booking->date)->isToday()) {
                $this->reorderTodayTurns();
            }

            return true;
        });
    }

    /**
     * حذف الحجز
     */
    public function deleteById(int $id): bool
    {
        return DB::transaction(function () use ($id) {

            $booking = Booking::findOrFail($id);

            $isToday = Carbon::parse($booking->date)->isToday();

            $deleted = $booking->delete();

            if ($deleted && $isToday) {
                $this->reorderTodayTurns();
            }

            return $deleted;
        });
    }

    /**
     * إعادة ترتيب أدوار حجوزات اليوم
     */
    public function reorderTodayTurns(): void
    {
        $bookings = Booking::query()
            ->whereDate('date', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('time')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($bookings as $index => $booking) {
            $booking->update([
                'turn' => $index + 1,
            ]);
        }

        // أي completed أو cancelled يبقى turn = 0
        Booking::query()
            ->whereDate('date', today())
            ->whereIn('status', ['completed', 'cancelled'])
            ->update([
                'turn' => 0,
            ]);
    }
}