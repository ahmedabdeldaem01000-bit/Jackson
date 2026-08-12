<?php

use Livewire\Component;
use App\Models\Booking;
use Carbon\Carbon;
 
use Livewire\Attributes\Title;
new #[Title('Employee show')] class extends Component
{
    public $bookings = [];

    public function mount()
    {
        $this->loadBookings();
    }



public function loadBookings(): void
{
    $this->bookings = Booking::with([
        'user',
        'employee',
        'service',
        'subService',
    ])->get()->map(function ($booking) {
$start = $booking->date
    ->copy()
    ->setTimeFromTimeString($booking->time);

$duration = (int) $booking->service
    ->subServices
    ->first()
    ?->duration;

$end = $start->copy()->addMinutes($duration);

$booking->end_time = $end;
$booking->end_time = $end->format('H:i');
        $now = now();

        // قبل بداية الحجز
        if ($now->lt($start)) {

            $booking->status = 'Waiting';

            $booking->progress = 0;

            $booking->remaining = $now->diffForHumans($start, [
                'syntax' => Carbon::DIFF_ABSOLUTE,
                'parts' => 2,
            ]);

            $booking->progress_color = 'bg-secondary';
        }

        // أثناء الحجز
        elseif ($now->between($start, $end)) {

            $elapsedSeconds = $start->diffInSeconds($now);

            $totalSeconds = $start->diffInSeconds($end);

            $booking->progress = round(($elapsedSeconds / $totalSeconds) * 100);

            $booking->remaining = $end->diffForHumans($now, [
                'syntax' => Carbon::DIFF_ABSOLUTE,
                'parts' => 2,
            ]);

            $booking->status = 'Running';

            $booking->progress_color = 'bg-success';
        }

        // انتهى الحجز
        else {

            $booking->status = 'Finished';

            $booking->progress = 100;

            $booking->remaining = 'انتهى';

            $booking->progress_color = 'bg-primary';
        }

        return $booking;

    });
}
 
};

?>

<section>

    <section class="content-header">
        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">
                    <h1>My Bookings</h1>
                </div>

            </div>

        </div>
    </section>

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Booking History
            </h3>

        </div>

        <div class="card-body"  wire:poll.10s>

            <table class="table table-bordered table-hover">

                <thead>

                <tr>

                    <th>#</th>

                    <th>Customer</th>

                    <th>Employee</th>

                    <th>Service</th>

                    <th>Duration</th>

                    <th>Start</th>
                    <th>end</th>

                    <th>Remaining</th>

                    <th width="220">
                        Progress
                    </th>

                    <th>Status</th>

                </tr>

                </thead>

                <tbody>

                @forelse($bookings as $booking)

                    <tr>

                        <td>
                            {{ $booking->id }}
                        </td>

                        <td>
                            {{ $booking['user']['name'] }}
                        </td>

                        <td>
                            {{ $booking['employee']['name'] }}
                        </td>

                        <td>
                            {{ $booking['service']['name'] }}
                        </td>

                        <td>
                           {{  $booking->service
    ->subServices
    ->first()?->duration }} دقيقة
                        </td>

                        <td>
                            {{ $booking['time'] }}
                        </td>

                    <td>
    {{ $booking->end_time }}
</td>

                        <td>

                            @if($booking->progress == 100)

                                <span class="badge badge-secondary">
                                    Finished
                                </span>

                            @elseif($booking->progress == 0)

                                   {{ $booking->service?->subServices->first()?->duration ?? 0 }} دقيقة

                            @else

                                {{ $booking->remaining_minutes }} دقيقة

                            @endif

                        </td>

                        <td>

                            <div class="progress progress-sm">

                                <div
                                    class="progress-bar {{ $booking->progress_color }}"
                                    style="width: {{ $booking->progress }}%">

                                </div>

                            </div>
                            <small>

                                {{ $booking->progress }}%

                            </small>

                        </td>

                        <td>

                            @if($booking->progress == 0)

                                <span class="badge badge-info">
                                    Waiting
                                </span>

                            @elseif($booking->progress < 100)

                                <span class="badge badge-warning">
                                    In Progress
                                </span>

                            @else

                                <span class="badge badge-success">
                                    Finished
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9" class="text-center">

                            No Bookings Found

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</section>