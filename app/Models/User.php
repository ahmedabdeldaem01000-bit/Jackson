<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
#[Fillable(['name', 'email', 'password','phone','avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable ,HasRoles;

        protected string $guard_name = 'web';
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
       public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%$search%")
               
                ->orWhere('email', 'like', "%$search%");
     
        });
    }

    public function bookings(): HasMany
{
    return $this->hasMany(Booking::class);
}

protected function avatar(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value 
            ? asset('storage/' . $value) // مسار الصورة لو موجودة
            : asset('images/logo4-removebg-preview.png') // مسار الصورة الافتراضية
    );
}
}
