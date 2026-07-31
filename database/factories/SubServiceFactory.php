<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\SubService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Testing\Fakes\Fake;

/**
 * @extends Factory<SubService>
 */
class SubServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = SubService::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'price' =>Fake()->numberBetween(20,50),

            'name' => fake()->randomElement([
                'قص كلاسيك',
                'قص أطفال',
                'قص بالمقص',
                'قص ماكينة',
                'تحديد لحية',
                'حلاقة كاملة',
                'تنظيف بشرة',
                'ماسك',
                'صبغة شعر',
                'صبغة لحية'
            ]),

            'duration' => fake()->randomElement([
                15,
                20,
                30,
                45,
                60
            ]),
        ];
    }

}
