<?php

namespace App\Livewire\Forms;

use App\Models\Booking;

use App\Services\BookingService;
use Carbon\Carbon;
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


    public function setBooking(
        Booking $booking
    ): void {

        $this->booking = $booking;

        $this->user_id = $booking->user_id;
        $this->employee_id = $booking->employee_id;
        $this->service_id = $booking->service_id;
        $this->date = $booking->date;
        $this->time = $booking->time;
    }



    public function store()
    {
        $this->date = now()->toDateString();

        $this->validate();

        $service = app(BookingService::class);

        $error = $service->hasConflict(
            $this->employee_id,
            $this->date,
            $this->time,
            $this->user_id,
        );

        if ($error) {

            $this->addError('time', $error);

            return;
        }
        try {

            $service->create([
                'user_id' => $this->user_id,
                'employee_id' => $this->employee_id,
                'service_id' => $this->service_id,
                'date' => $this->date,
                'time' => $this->time,
            ]);

        } catch (\Exception $e) {

            $this->addError('time', $e->getMessage());

            return;
        }

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $service = app(BookingService::class);

        $error = $service->hasConflict(
            $this->employee_id,
            $this->date,
            $this->time,
            $this->user_id,
            $this->booking->id
        );

        if ($error) {

            $this->addError('time', $error);

            return;
        }
        $service->update(
            $this->booking,
            [
                'user_id' => $this->user_id,
                'employee_id' => $this->employee_id,
                'service_id' => $this->service_id,
                'date' => $this->date,
                'time' => $this->time,
            ]
        );
        $this->reset();
    }

}