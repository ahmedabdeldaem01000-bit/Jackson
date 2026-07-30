<?php
use App\Models\Booking;
use App\Models\Employee;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
 



new #[Title('Bookings')] class extends Component {
    use WithPagination;
 
    public $search = '';

    public $time = '';
 
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
        return Booking::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->time, fn($q) => $q->where('time', $this->time))
           
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
        $this->reset(['search', 'time']);
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





 
 
 

};