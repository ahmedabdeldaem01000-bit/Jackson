<?php

namespace App\Livewire\Forms;

use App\Models\Booking;
use App\Services\BookingService;
use Livewire\Attributes\Validate;
use Livewire\Form;

class BookingForm extends Form
{
    public ?Booking $booking = null;


    /*
    |--------------------------------------------------------------------------
    | Customer
    |--------------------------------------------------------------------------
    */

    #[Validate('required|exists:users,id')]
    public $user_id = '';


    /*
    |--------------------------------------------------------------------------
    | Employee
    |--------------------------------------------------------------------------
    */

    #[Validate('required|exists:employees,id')]
    public $employee_id = '';


    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    */

    #[Validate('required|array|min:1')]
    public array $service_ids = [];


    /*
    |--------------------------------------------------------------------------
    | Date / Time
    |--------------------------------------------------------------------------
    */

    #[Validate('required|date')]
    public $date = '';

    #[Validate('required|date_format:H:i')]
    public $time = '';


    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    public $exists = false;

    public ?string $conflictMessage = null;


    /*
    |--------------------------------------------------------------------------
    | Set Booking
    |--------------------------------------------------------------------------
    */

    public function setBooking(Booking $booking): void
    {
        $this->booking = $booking;

        $this->user_id = $booking->user_id;

        $this->employee_id = $booking->employee_id;

        $this->date = $booking->date;

        $this->time = $booking->time;


        /*
        |--------------------------------------------------------------------------
        | Load services from pivot table
        |--------------------------------------------------------------------------
        */

        $this->service_ids = $booking->services()
            ->pluck('sub_services.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Legacy support
        |--------------------------------------------------------------------------
        |
        | لو عندك حجوزات قديمة ولسه sub_service_id موجود
        | نستخدمه كـ fallback.
        |
        */

        if (
            empty($this->service_ids)
            && !empty($booking->sub_service_id)
        ) {
            $this->service_ids = [
                (int) $booking->sub_service_id,
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Create Booking
    |--------------------------------------------------------------------------
    */

    public function store()
    {
        $this->date = now()->toDateString();

        $this->validate();


        $service = app(BookingService::class);


        /*
        |--------------------------------------------------------------------------
        | Conflict Warning
        |--------------------------------------------------------------------------
        */

        $this->conflictMessage = $service->hasConflict(
            (int) $this->employee_id,
            $this->date,
            $this->time,
            (int) $this->user_id,
        );


        try {

            /*
            |--------------------------------------------------------------------------
            | Create Booking + Services
            |--------------------------------------------------------------------------
            */

            $booking = $service->create([
                'user_id' => (int) $this->user_id,

                'employee_id' => (int) $this->employee_id,

                'date' => $this->date,

                'time' => $this->time,

                'service_ids' => array_map(
                    'intval',
                    $this->service_ids
                ),
            ]);


            session()->flash(
                'success',
                'تم إنشاء الحجز بنجاح.'
            );


            return $booking;

        } catch (\Throwable $e) {

            report($e);

            $this->addError(
                'form.time',
                $e->getMessage()
            );

            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Booking
    |--------------------------------------------------------------------------
    */

    public function update()
    {
        $this->validate();


        $service = app(BookingService::class);


        /*
        |--------------------------------------------------------------------------
        | Conflict Warning
        |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | Update Booking + Services
            |--------------------------------------------------------------------------
            */

            $service->update(
                $this->booking,
                [
                    'user_id' => (int) $this->user_id,

                    'employee_id' => (int) $this->employee_id,

                    'date' => $this->date,

                    'time' => $this->time,

                    'service_ids' => array_map(
                        'intval',
                        $this->service_ids
                    ),
                ]
            );

        } catch (\Throwable $e) {

            report($e);

            $this->addError(
                'form.time',
                $e->getMessage()
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        session()->flash(
            'success',
            'تم تعديل الحجز بنجاح.'
        );


        /*
        |--------------------------------------------------------------------------
        | Conflict Warning
        |--------------------------------------------------------------------------
        */

        if ($conflictMessage) {

            session()->flash(
                'warning',
                $conflictMessage
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Reset
        |--------------------------------------------------------------------------
        */

        $this->reset();
    }
}