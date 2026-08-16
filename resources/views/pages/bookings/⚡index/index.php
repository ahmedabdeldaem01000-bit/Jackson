<?php
use App\Models\Booking;
use App\Models\Employee;
use App\Services\BookingService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
 



new #[Title('Bookings')] class extends Component {
    use WithPagination;
 
    public $search = '';

    public $time = '';
    public $status = '';
 
    public $sortField = 'time';
    public $sortDirection = 'asc';

    public $perPage = 10;

    public $selected = [];
    public $selectAll = false;


    public $showDeleteModal = false;
    public $BookingToDelete = null;
    
    public $BookingToComplete = null;

    protected $queryString = [
        'search' => ['except' => ''],
       
        'time' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'time'],
        'sortDirection' => ['except' => 'asc'],
    ];

    /* Filters */
#[Computed]
public function bookings()
{
    $query = Booking::query();

    $employee = auth('employee')->user();

    if ($employee->hasAnyRole(['employee', 'barber'])) {
        $query->where('employee_id', $employee->id);
    }

    return $query
        ->when($this->search, fn($q) => $q->search($this->search))
        ->when($this->time, fn($q) => $q->where('time', $this->time))
        ->when($this->status, fn($q) => $q->where('status', $this->status))
        ->whereDate('date', today())
        ->orderByRaw('CASE WHEN turn = 0 THEN 1 ELSE 0 END')
        ->orderBy('turn')
        ->orderBy('time')
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);
}

   

    public function sortBy($filed)
    {
        if ($this->sortField === $filed) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $filed;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

        #[Computed]
    public function status()
    {
        return Booking::distinct('status')->pluck('status')->sort();
    }

    /* Reset Filters */
    public function updateSearch()
    {
        $this->resetPage();
    }
    public function updateName()
    {
        $this->resetPage();
    }
    public function updateStatus()
    {
        $this->resetPage();
    }
    public function resetFilters()
    {
        $this->reset(['search', 'time','status']);
        $this->resetPage();
    }





    public function updateSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->bookings->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function bulkDelete()
    {
        Booking::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('message', 'تم مسح جميع الحجوزات المحددة بنجاح');
    }





    public function completed($bookingId, BookingService $bookingService)
    {
        $booking = Booking::findOrFail($bookingId);

        if (!$bookingService->complete($booking)) {

            session()->flash(
                'error',
                'لا يمكن إنهاء الحجز قبل موعده.'
            );

            return;
        }

        session()->flash(
            'success',
            'تم إنهاء الحجز بنجاح.'
        );
    }

    public function cancelled($bookingId, BookingService $bookingService)
    {
        $booking = Booking::findOrFail($bookingId);

        $bookingService->cancel($booking);

        session()->flash(
            'success',
            'تم إلغاء الحجز.'
        );
    }

 
public function confirmDelete($bookingId)
{
    $this->BookingToDelete = $bookingId;

    $this->showDeleteModal = true;
}
public function deleteBooking(BookingService $bookingService)
{
    $bookingService->deleteById($this->BookingToDelete);

    $this->closeDeleteModal();

    session()->flash(
        'success',
        'تم حذف الحجز بنجاح.'
    );
}


 
 public function closeDeleteModal()
{
    $this->showDeleteModal = false;
    $this->BookingToDelete = null;
}
 

};