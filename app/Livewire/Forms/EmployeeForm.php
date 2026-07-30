<?php

namespace App\Livewire\Forms;

use App\Models\Employee;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EmployeeForm extends Form
{

    public ?Employee $employee = null;

    #[Validate('required|min:3')]
    public $name='';

    
 
    
    #[Validate('required|email|unique:employees,email')]
    public $email='';

 

    #[Validate('required')]
    public $phone='';
 

    #[Validate('required|in:active,inactive')]
    public $status='status';
 


public function setEmployee(Employee $employee)
{
    $this->employee = $employee;
    $this->status = $employee->status;
    $this->email = $employee->email;
    $this->phone = $employee->phone;
    $this->name = $employee->name;
 
}


public function store()
{
    $this->validate();
    Employee::create($this->only(['name', 'email', 'phone', 'status']));
    $this->reset();

}

public function update(){
    $this->validate(['email'=>'required|email|unique:employees,email,' . $this->employee->id]);
    $this->employee->update($this->only(['name', 'email', 'phone', 'status']));
    $this->reset();
}

}