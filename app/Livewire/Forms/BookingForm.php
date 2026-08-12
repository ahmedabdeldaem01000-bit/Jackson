<?php

namespace App\Livewire\Forms;

use App\Models\Booking;
use App\Services\BookingService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class BookingForm extends Form
{
    public ?Booking $booking = null;

    #[Validate('required|exists:users,id')]
    public $user_id = '';

    #[Validate('required|exists:employees,id')]
    public $employee_id = '';

    #[Validate('required|exists:sub_services,id')]
    public $service_id = '';

    #[Validate('required|date')]
    public $date = '';

    #[Validate('required|date_format:H:i')]
    public $time = '';

    public $exists = false;

    /**
     * تحميل بيانات الحجز للتعديل
     */
    public ?string $conflictMessage = null;
    public function setBooking(Booking $booking): void
    {
        $this->booking = $booking;

        $this->user_id = $booking->user_id;
        $this->employee_id = $booking->employee_id;
        $this->service_id = $booking->service_id;
        $this->date = $booking->date;
        $this->time = $booking->time;
    }

    /**
     * إنشاء حجز جديد
     */
public function store()
{
    $this->date = now()->toDateString();

    $this->validate();

    $service = app(BookingService::class);

    $this->conflictMessage = $service->hasConflict(
        (int) $this->employee_id,
        $this->date,
        $this->time,
        (int) $this->user_id,
    );

    try {

        $booking = $service->create([
            'user_id' => (int) $this->user_id,
            'employee_id' => (int) $this->employee_id,
            'sub_service_id' => (int) $this->service_id,
            'date' => $this->date,
            'time' => $this->time,
        ]);

        session()->flash(
            'success',
            'تم إنشاء الحجز بنجاح.'
        );

        return $booking;

    } catch (\Throwable $e) {

        report($e);

        $this->addError(
            'time',
            $e->getMessage()
        );

        return null;
    }
}
    /**
     * تعديل الحجز
     */
    public function update()
    {
        $this->validate();

        $service = app(BookingService::class);

        /*
         * التحقق من التعارض فقط
         */
        $conflictMessage = $service->hasConflict(
            (int) $this->employee_id,
            $this->date,
            $this->time,
            (int) $this->user_id,
            $this->booking->id
        );

        try {

            /*
             * تعديل الحجز مهما كان هناك تعارض
             */
            $service->update(
                $this->booking,
                [
                    'user_id' => $this->user_id,
                    'employee_id' => $this->employee_id,
                    'sub_service_id' => $this->service_id,
                    'date' => $this->date,
                    'time' => $this->time,
                ]
            );

        } catch (\Exception $e) {

            $this->addError('time', $e->getMessage());

            return;
        }

        /*
         * رسالة النجاح
         */
        session()->flash(
            'success',
            'تم تعديل الحجز بنجاح.'
        );

        /*
         * رسالة التحذير
         */
        if ($conflictMessage) {
            session()->flash(
                'warning',
                $conflictMessage
            );
        }

        /*
         * تنظيف الفورم
         */
        $this->reset();
    }
}
 
