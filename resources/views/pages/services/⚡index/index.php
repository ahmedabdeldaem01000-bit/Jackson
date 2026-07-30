<?php

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;

new #[Title('Services')] class extends Component {

    use WithPagination;

    public $search = '';

    public $name = '';
    public $sup_service = '';

    public $sortField = 'name';
    public $sortDirection = 'asc';

    public $perPage = 10;

    public $selected = [];
    public $selectAll = false;

    public $showDeleteModal = false;
    public $serviceToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'name' => ['except' => ''],
        'sup_service' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    /* Filters */

    #[Computed]
    public function services()
    {
        return Service::query()

            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%"))

            ->when($this->name, fn ($q) =>
                $q->where('name', 'like', "%{$this->name}%"))

            ->when($this->sup_service, function ($q) {
                $q->whereHas('subServices', function ($query) {
                 $query->where('name', 'like', "%{$this->sup_service}%");
                });
            })

            ->with('subServices')

            ->orderBy($this->sortField, $this->sortDirection)

            ->paginate($this->perPage);
    }

    /* Sort */

    public function sortBy($field)
    {
        if ($this->sortField === $field) {

            $this->sortDirection =
                $this->sortDirection === 'asc'
                ? 'desc'
                : 'asc';

        } else {

            $this->sortField = $field;
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

    public function updateSupService()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'name',
            'sup_service',
        ]);

        $this->resetPage();
    }

    /* Delete */

    public function confirmDelete($serviceId)
    {
        $this->serviceToDelete = $serviceId;

        $this->showDeleteModal = true;
    }

    public function deleteService()
    {
        if ($this->serviceToDelete) {

            Service::find($this->serviceToDelete)?->delete();

            $this->showDeleteModal = false;

            $this->serviceToDelete = null;

            session()->flash(
                'message',
                'تم حذف الخدمة بنجاح'
            );
        }
    }

    /* Select */

    public function updateSelectAll($value)
    {
        if ($value) {

            $this->selected = $this->services
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();

        } else {

            $this->selected = [];

        }
    }

    public function bulkDelete()
    {
        Service::whereIn('id', $this->selected)->delete();

        $this->selected = [];

        $this->selectAll = false;

        session()->flash(
            'message',
            'تم حذف الخدمات المحددة بنجاح'
        );
    }

};