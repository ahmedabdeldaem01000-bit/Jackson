<?php

use App\Models\Service;
use App\Models\SubService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component {

    public Service $service;

    public string $name = '';

    public array $subServices = [];

    protected function rules()
    {
        return [

            'name' => [
                'required',
                'string',
                'max:255',
                'unique:services,name,' . $this->service->id,
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

    public function mount(Service $service)
    {
        $this->service = $service;

        $this->name = $service->name;

        $this->subServices = $service
            ->subServices
            ->map(function ($sup) {

                return [

                    'id' => $sup->id,

                    'name' => $sup->name,

                    'duration' => $sup->duration,

                ];

            })
            ->toArray();
    }

    public function addSubService()
    {
        $this->subServices[] = [

            'id' => null,

            'name' => '',

            'duration' => '',

        ];
    }

    public function removeSubService($index)
    {
        unset($this->subServices[$index]);

        $this->subServices = array_values($this->subServices);

        if (empty($this->subServices)) {

            $this->addSubService();

        }
    }

    public function update()
    {
        $this->validate();

        DB::transaction(function () {

            $this->service->update([

                'name' => $this->name,

            ]);

            $ids = [];

      foreach ($this->subServices as $sup) {

    if (!empty($sup['id'])) {

        $model = SubService::find($sup['id']);

        $model->update([
            'service_id' => $this->service->id,
            'name'       => $sup['name'],
            'duration'   => $sup['duration'],
        ]);

    } else {

        $model = $this->service->subServices()->create([
            'name'     => $sup['name'],
            'duration' => $sup['duration'],
        ]);

    }

    $ids[] = $model->id;
}

SubService::where('service_id', $this->service->id)
    ->whereNotIn('id', $ids)
    ->delete();

          

        });

        session()->flash(
            'message',
            'تم تعديل الخدمة بنجاح'
        );

        return redirect()->route('admin.services.index');
    }

};
?>

<form wire:submit="update">

    @php($button="تحديث الخدمة")

    @include('pages.services.partials.form')

</form>