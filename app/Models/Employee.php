<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeesFactory> */
    use HasFactory,HasRoles;
    protected $fillable=['name','phone','status','email','password'];
       public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%$search%")
               
                ->orWhere('email', 'like', "%$search%");
     
        });
    }



};
