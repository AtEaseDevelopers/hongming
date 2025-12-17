<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Class Assign
 * @package App\Models
 * @version June 21, 2023, 6:30 pm +08
 *
 * @property integer $driver_id
 */
class Assign extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'assigns';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'driver_id',
        'delivery_order_id',
        'sequence'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'driver_id' => 'integer',
        'delivery_order_id' => 'integer',
        'sequence' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'driver_id' => 'required',
        'delivery_order_id' => 'required',
        'sequence' => 'required',
        'created_at' => 'nullable|nullable',
        'updated_at' => 'nullable|nullable'
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(\App\Models\DeliveryOrder::class, 'delivery_order_id');
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id');
    }
    
}
