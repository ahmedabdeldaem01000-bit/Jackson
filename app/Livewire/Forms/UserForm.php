<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{

    public ?User $user = null;

    #[Validate('required|min:3')]
    public $name='';

    
 
    
    #[Validate('required|email|unique:users,email')]
    public $email='';
    
#[Validate('required|string|confirmed')]
public $password = '';

public $password_confirmation = '';

 

    #[Validate('required')]
    public $phone='';
 

 


public function setUser(User $user)
{
    $this->user = $user;
    $this->name = $user->name;
    $this->email = $user->email;
    $this->phone = $user->phone;
    $this->password = $user->password;
 
}


public function store()
{
    $this->validate();
    User::create($this->only(['name', 'email', 'phone', 'password']));
    $this->reset();

}

public function update(){
    $this->validate(['email'=>'required|email|unique:users,email,' . $this->user->id]);
    $this->user->update($this->only(['name', 'email', 'phone', 'password']));
    $this->reset();
}

}