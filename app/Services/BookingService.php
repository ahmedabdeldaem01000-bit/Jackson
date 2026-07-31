<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * التأكد من وجود تعارض فى الموعد
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
            $query->where('employee_id', $employeeId)
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
            $bookingTime->format('H:i') == $requestedTime->format('H:i')
        ) {
            return 'هذا الموظف لديه حجز بالفعل في هذا الموعد.';
        }

        // نفس العميل ونفس الوقت
        if (
            $booking->user_id == $userId &&
            $bookingTime->format('H:i') == $requestedTime->format('H:i')
        ) {
            return 'هذا العميل لديه حجز بالفعل في هذا الموعد.';
        }

        $minutes = abs(
            $bookingTime->diffInMinutes($requestedTime, false)
        );

        // الموظف خلال 20 دقيقة
        if (
            $booking->employee_id == $employeeId &&
            $minutes < 10
        ) {
            return 'يجب أن يكون هناك فرق 10 دقائق بين حجوزات الموظف.';
        }

        // العميل خلال 20 دقيقة
        if (
            $booking->user_id == $userId &&
            $minutes < 10
        ) {
            return 'هذا العميل لديه حجز آخر خلال أقل من 10 دقائق.';
        }
    }

    return null;
}
    /**
     * إنشاء الحجز
     */
    public function create(array $data): Booking
    {
         return DB::transaction(function () use ($data) {

        if ($this->hasConflict(
            $data['employee_id'],
            $data['date'],
            $data['time'],
            $data['user_id']
        )) {

            throw new \Exception('هذا الموعد محجوز بالفعل.');
        }

        return Booking::create([
            'user_id' => $data['user_id'],
            'employee_id' => $data['employee_id'],
            'service_id' => $data['service_id'],
            'date' => $data['date'],
            'time' => $data['time'],
            'status' => 'pending',
        ]);
            });
    }

    /**
     * تعديل الحجز
     */
    public function update(
        Booking $booking,
        array $data
    ): bool {

        return $booking->update([
            'user_id' => $data['user_id'],
            'employee_id' => $data['employee_id'],
            'service_id' => $data['service_id'],
            'date' => $data['date'],
            'time' => $data['time'],
        ]);
    }

    /**
     * إنهاء الحجز
     */
    public function complete(Booking $booking): bool
    {
            $bookingDate = Carbon::parse($booking->date)
    ->setTimeFromTimeString($booking->time);
        
        if (now()->lt($bookingDate)) {
            return false;
        }

        $booking->update([
            'status' => 'completed'
        ]);

        return true;
    }

    /**
     * إلغاء الحجز
     */
    public function cancel(Booking $booking): bool
    {
        return $booking->update([
            'status' => 'cancelled'
        ]);
    }

    /**
     * حذف الحجز
     */
 public function deleteById(int $id): bool
{
    return Booking::findOrFail($id)->delete();
}
}