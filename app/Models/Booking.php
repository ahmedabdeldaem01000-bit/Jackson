<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'employee_id',
        'date',
        'time',
        'turn',
        'status',
    ];
    protected $casts = [
    'date' => 'date',
];

    public function services()
    {
        return $this->belongsToMany(
            SubService::class,
            'booking_sub_service'
        )->withTimestamps();
    }
     public function subService()
{
    return $this->belongsTo(SubService::class);
}

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

     public function employee()
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
{
    return $query->where(function ($query) use ($search) {

        $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });

      
 

    });
}

public function getProgressAttribute()
{
    $start =  Carbon::parse($this->date)
        ->setTimeFromTimeString($this->time);

    $duration = $this->service->subServices->first()?->duration;

    $end = $start->copy()->addMinutes($duration);
 
    $now = now();

    if ($now->lessThan($start)) {
        return 0;
    }

    if ($now->greaterThanOrEqualTo($end)) {
        return 100;
    }

    $elapsed = $start->diffInMinutes($now);

    return round(($elapsed / $duration) * 100);
}


public function getRemainingMinutesAttribute()
{
    $start =  Carbon::parse($this->date)
        ->setTimeFromTimeString($this->time);

    $end = $start->copy()->addMinutes($this->subService->duration);

    return max(0, now()->diffInMinutes($end, false));
}


public function getProgressColorAttribute()
{
    return match (true) {
        $this->progress < 30 => 'bg-success',
        $this->progress < 70 => 'bg-warning',
        default => 'bg-danger',
    };
}

}
