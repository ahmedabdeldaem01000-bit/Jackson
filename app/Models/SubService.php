<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubService extends Model
{
    /** @use HasFactory<\Database\Factories\SubServiceFactory> */
    use HasFactory;
    protected $fillable=['name','duration','service_id','price'];
    protected $casts = [
    'duration' => 'integer',
];
        public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
        public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
