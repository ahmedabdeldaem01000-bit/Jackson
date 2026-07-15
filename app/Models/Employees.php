<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class Employees extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeesFactory> */
    use HasFactory,HasRoles;
    protected $fillable=['name','phone','status','email'];
       public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    
}
