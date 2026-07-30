<?php

use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public array $subServices = [];

    protected function rules()
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255',
                'unique:services,name',
            ],

            'subServices' => [
                'required',
                'array',
                'min:1',
            ],

            'subServices.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'subServices.*.duration' => [
                'required',
                'integer',
                'min:5',
            ],

        ];
    }

    public function mount()
    {
        $this->subServices = [
            [
                'name' => '',
                'duration' => '',
            ]
        ];
    }

    public function addSubService()
    {
        $this->subServices[] = [
            'name' => '',
            'duration' => '',
        ];
    }

    public function removeSubService($index)
    {
        unset($this->subServices[$index]);

        $this->subServices = array_values($this->subServices);

        if (count($this->subServices) === 0) {

            $this->addSubService();

        }
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {

            $service = Service::create([

                'name' => $this->name,

            ]);

            foreach ($this->subServices as $subService) {

                $service->subServices()->create([

                    'name' => $subService['name'],

                    'duration' => $subService['duration'],

                ]);

            }

        });

        session()->flash(
            'message',
            'تم إضافة الخدمة بنجاح'
        );

        return $this->redirectRoute('services.index');
    }
};

?>
<form wire:submit="save">

    @php($button="حفظ الخدمة")

    @include('pages.services.partials.form')

</form>