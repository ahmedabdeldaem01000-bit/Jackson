<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'الشعر',
                'اللحية',
                'العناية بالبشرة',
                'الصبغات',
                'الباقات',
                'الأطفال',
                'VIP',
                'العريس',
                'العناية بالشعر',
                'الواكس',
                'الكيراتين',
                'الحمام المغربي',
            ]),
        ];
    }
}
