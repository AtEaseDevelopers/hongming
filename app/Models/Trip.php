<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Trip extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'trips';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'date',
        'driver_id',
        'kelindan_id',
        'lorry_id',
        'task_id',
        'cash',
        'type'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'date' => 'datetime:d-m-Y H:i:s',
        'driver_id' => 'integer',
        'kelindan_id' => 'integer',
        'lorry_id' => 'integer',
        'cash' => 'float',
        'type' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'date' => 'required',
        'driver_id' => 'required',
        'kelindan_id' => 'required',
        'lorry_id' => 'required',
        'task_id' => 'required',
        'type' => 'required',
        'created_at' => 'nullable|nullable',
        'updated_at' => 'nullable|nullable'
    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id', 'id');
    }

    public function kelindan()
    {
        return $this->belongsTo(\App\Models\Kelindan::class, 'kelindan_id', 'id');
    }

    public function lorry()
    {
        return $this->belongsTo(\App\Models\Lorry::class, 'lorry_id', 'id');
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }


    public function isEnded()
    {
        // Check if there's an end trip record for this start trip
        return Trip::where('task_id', $this->task_id)
                ->where('driver_id', $this->driver_id)
                ->where('lorry_id', $this->lorry_id)
                ->where('type', 0) // End trip
                ->where('id', '!=', $this->id) // Exclude current trip
                ->exists();
    }

}
