<?php

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Services\BookingService;
use Carbon\Carbon;

it('generates time slots from an employee schedule', function () {
    $employee = Employee::create([
        'name' => 'Test Barber',
        'email' => 'barber@example.com',
        'phone' => '0505050505',
        'password' => 'password',
        'status' => 'active',
    ]);

    EmployeeSchedule::create([
        'employee_id' => $employee->id,
        'day_of_week' => Carbon::parse('2026-07-20')->dayOfWeek,
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'slot_duration' => 10,
    ]);

    $service = app(BookingService::class);
    $slots = $service->generateTimeSlots($employee, Carbon::parse('2026-07-20'));

    expect($slots)->toHaveCount(7)
        ->and($slots->first()['time'])->toBe('10:00')
        ->and($slots->last()['time'])->toBe('11:00');
});
