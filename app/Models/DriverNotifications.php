<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverNotifications extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'title', 
        'message',
        'type',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}