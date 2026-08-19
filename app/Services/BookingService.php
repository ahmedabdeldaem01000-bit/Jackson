<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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


            /*
            |--------------------------------------------------------------------------
            | نفس الموظف ونفس الوقت
            |--------------------------------------------------------------------------
            */

            if (
                $booking->employee_id == $employeeId &&
                $bookingTime->format('H:i') === $requestedTime->format('H:i')
            ) {
                return 'هذا الموظف لديه حجز بالفعل في نفس الموعد.';
            }


            /*
            |--------------------------------------------------------------------------
            | نفس العميل ونفس الوقت
            |--------------------------------------------------------------------------
            */

            if (
                $booking->user_id == $userId &&
                $bookingTime->format('H:i') === $requestedTime->format('H:i')
            ) {
                return 'لديك حجز بالفعل في نفس الموعد.';
            }


            $minutes = abs(
                $bookingTime->diffInMinutes(
                    $requestedTime,
                    false
                )
            );


            /*
            |--------------------------------------------------------------------------
            | الموظف لديه حجز خلال أقل من 10 دقائق
            |--------------------------------------------------------------------------
            */

            if (
                $booking->employee_id == $employeeId &&
                $minutes < 10
            ) {
                return 'يوجد حجز آخر للموظف خلال أقل من 10 دقائق.';
            }


            /*
            |--------------------------------------------------------------------------
            | العميل لديه حجز خلال أقل من 10 دقائق
            |--------------------------------------------------------------------------
            */

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
     *
     * Booking واحد يمكن أن يحتوي على خدمة أو أكثر.
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            $serviceIds = $this->normalizeServiceIds(
                $data['service_ids'] ?? []
            );


            /*
            |--------------------------------------------------------------------------
            | Legacy service_id
            |--------------------------------------------------------------------------
            |
            | نحتفظ به مؤقتًا طالما العمود القديم ما زال موجودًا في bookings.
            | نضع أول خدمة فيه فقط.
            |
            */

            $legacyServiceId = $serviceIds[0];


            /*
            |--------------------------------------------------------------------------
            | Create Booking
            |--------------------------------------------------------------------------
            */

            $booking = Booking::create([
                'user_id' => (int) $data['user_id'],

                'employee_id' => (int) $data['employee_id'],

                'sub_service_id' => $legacyServiceId,

                'date' => $data['date'],

                'time' => $data['time'],

                'status' => 'pending',

                'turn' => 0,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Attach All Services
            |--------------------------------------------------------------------------
            */

            $booking->services()->sync($serviceIds);


            /*
            |--------------------------------------------------------------------------
            | Reorder Today's Queue
            |--------------------------------------------------------------------------
            */

            if (
                Carbon::parse($booking->date)->isToday()
            ) {
                $this->reorderTodayTurns();
            }


            /*
            |--------------------------------------------------------------------------
            | Return Fresh Booking
            |--------------------------------------------------------------------------
            */

            return $booking
                ->fresh([
                    'services',
                    'user',
                    'employee',
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
        return DB::transaction(function () use ($booking, $data) {

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            $serviceIds = $this->normalizeServiceIds(
                $data['service_ids'] ?? []
            );


            /*
            |--------------------------------------------------------------------------
            | Legacy service_id
            |--------------------------------------------------------------------------
            */

            $legacyServiceId = $serviceIds[0];


            /*
            |--------------------------------------------------------------------------
            | Original Date
            |--------------------------------------------------------------------------
            */

            $oldDate = $booking->date;


            /*
            |--------------------------------------------------------------------------
            | Update Booking
            |--------------------------------------------------------------------------
            */

            $updated = $booking->update([
                'user_id' => (int) $data['user_id'],

                'employee_id' => (int) $data['employee_id'],

                'sub_service_id' => $legacyServiceId,

                'date' => $data['date'],

                'time' => $data['time'],
            ]);


            if (!$updated) {
                return false;
            }


            /*
            |--------------------------------------------------------------------------
            | Sync Services
            |--------------------------------------------------------------------------
            */

            $booking->services()->sync($serviceIds);


            /*
            |--------------------------------------------------------------------------
            | Reorder Today's Queue
            |--------------------------------------------------------------------------
            */

            if (
                Carbon::parse($oldDate)->isToday() ||
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

            /*
            |--------------------------------------------------------------------------
            | Complete Booking
            |--------------------------------------------------------------------------
            */

            $updated = $booking->update([
                'status' => 'completed',

                'turn' => 0,
            ]);


            if (!$updated) {
                return false;
            }


            /*
            |--------------------------------------------------------------------------
            | Reorder Queue
            |--------------------------------------------------------------------------
            */

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

                'turn' => 0,
            ]);


            if (!$updated) {
                return false;
            }


            if (
                Carbon::parse($booking->date)->isToday()
            ) {
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

            $isToday = Carbon::parse(
                $booking->date
            )->isToday();


            /*
            |--------------------------------------------------------------------------
            | Delete Booking
            |--------------------------------------------------------------------------
            |
            | الـ pivot rows هتتمسح تلقائيًا بسبب cascadeOnDelete
            |--------------------------------------------------------------------------
            */

            $deleted = $booking->delete();


            if (
                $deleted &&
                $isToday
            ) {
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
            ->whereNotIn(
                'status',
                [
                    'completed',
                    'cancelled',
                ]
            )
            ->orderBy('time')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();


        foreach ($bookings as $index => $booking) {

            $booking->update([
                'turn' => $index + 1,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Completed / Cancelled
        |--------------------------------------------------------------------------
        */

        Booking::query()
            ->whereDate('date', today())
            ->whereIn(
                'status',
                [
                    'completed',
                    'cancelled',
                ]
            )
            ->update([
                'turn' => 0,
            ]);
    }


    /**
     * تجهيز service IDs
     */
    protected function normalizeServiceIds(
        array $serviceIds
    ): array {
        $serviceIds = collect($serviceIds)
            ->filter(
                fn ($id) => is_numeric($id)
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->filter(
                fn ($id) => $id > 0
            )
            ->unique()
            ->values()
            ->toArray();


        if (empty($serviceIds)) {
            throw new InvalidArgumentException(
                'يجب اختيار خدمة واحدة على الأقل.'
            );
        }


        return $serviceIds;
    }
}