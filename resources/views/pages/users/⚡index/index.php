<?php
use App\Models\Employee;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
 



new #[Title('Users')] class extends Component {
    use WithPagination;
 
    public $search = '';

    public $name = '';
    public $email = '';
    public $phone = '';
 
    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $perPage = 10;

    public $selected = [];
    public $selectAll = false;


    public $showDeleteModal = false;
    public $UserToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'name' => ['except' => ''],
         
        
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    /* Filters */
    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->name, fn($q) => $q->where('name', $this->name))
            ->when($this->email, fn($q) => $q->where('email', $this->email))
            ->when($this->phone, fn($q) => $q->where('phone', $this->phone))
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
 
    public function resetFilters()
    {
        $this->reset(['search', 'name','email']);
        $this->resetPage();
    }



    /*Action Delete & Select */
    public function confirmDelete($userId)
    {
        $this->UserToDelete = $userId;
        $this->showDeleteModal = true;

    }

    public function deleteUser()
    {
        if ($this->UserToDelete) {
            User::find($this->UserToDelete)->delete();
            $this->showDeleteModal = false;
            $this->UserToDelete = null;
            session()->flash('message', 'تم مسح المستخدم بنجاح');
        }

    }

    public function updateSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->users->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function bulkDelete()
    {
        User::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('message', 'تم مسح جميع العملاء المحددين بنجاح');
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