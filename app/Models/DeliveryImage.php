<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DeliveryImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'delivery_order_image_path',
        'proof_of_delivery_image_path',
    ];

    /**
     * Get full URL for delivery order image
     */
    public function getDeliveryOrderImageUrlAttribute()
    {
        if (!$this->delivery_order_image_path) {
            return null;
        }
        
        $cleanPath = str_replace('storage/', '', $this->delivery_order_image_path);
        
        return url($cleanPath);
    }

    /**
     * Get full URL for proof of delivery image
     */
    public function getProofOfDeliveryImageUrlAttribute()
    {
        if (!$this->proof_of_delivery_image_path) {
            return null;
        }
        
        $cleanPath = str_replace('storage/', '', $this->proof_of_delivery_image_path);
        
        return url($cleanPath);
    }

    /**
     * Relationship with Task
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}