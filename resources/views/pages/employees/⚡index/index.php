<?php
use App\Models\Employee;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
 



new #[Title('Employees')] class extends Component {
    use WithPagination;
 
    public $search = '';

    public $name = '';
    public $status = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $perPage = 10;

    public $selected = [];
    public $selectAll = false;


    public $showDeleteModal = false;
    public $EmployeeToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'name' => ['except' => ''],
         
        'status' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    /* Filters */
    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->name, fn($q) => $q->where('name', $this->name))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
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
        $this->reset(['search', 'name', 'status']);
        $this->resetPage();
    }



    /*Action Delete & Select */
    public function confirmDelete($employeeId)
    {
        $this->EmployeeToDelete = $employeeId;
        $this->showDeleteModal = true;

    }

    public function deleteEmployee()
    {
        if ($this->EmployeeToDelete) {
            Employee::find($this->EmployeeToDelete)->delete();
            $this->showDeleteModal = false;
            $this->EmployeeToDelete = null;
            session()->flash('message', 'تم مسح العامل بنجاح');
        }

    }

    public function updateSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->employees->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function bulkDelete()
    {
        Employee::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('message', 'تم مسح جميع العاملين المحددين بنجاح');
    }





 
    public function getFilteredEmployees()
    {
        return Employee::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->email, fn($q) => $q->where('email', $this->email))
            ->when($this->name, fn($q) => $q->where('name', $this->name))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();
    }

 

};