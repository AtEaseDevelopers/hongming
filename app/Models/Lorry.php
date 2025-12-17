<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Lorry
 * @package App\Models
 * @version July 23, 2022, 11:31 am UTC
 *
 * @property string $lorryno
 * @property string $type
 * @property number $weightagelimit
 * @property number $commissionlimit
 * @property number $commissionpercentage
 * @property string $permitholder
 * @property integer $status
 * @property string $remark
 * @property string $STR_UDF1
 * @property string $STR_UDF2
 * @property string $STR_UDF3
 * @property integer $INT_UDF1
 * @property integer $INT_UDF2
 * @property integer $INT_UDF3
 * @property boolean $in_use
 */
class Lorry extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'lorrys';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Lorry status constants
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    
    // Lorry usage constants
    const NOT_IN_USE = 0;
    const IN_USE = 1;

    protected $dates = ['deleted_at'];

    public $fillable = [
        'lorryno',
        'jpj_registration',
        'status',
        'remark',
        'in_use' // Add this line
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'lorryno' => 'string',
        'status' => 'integer',
        'remark' => 'string',
        'in_use' => 'boolean' // Add this line
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'lorryno' => 'required|string|max:255|unique:lorrys,lorryno',
        'jpj_registration' => 'nullable|string|max:255',
        'status' => 'required',
        'remark' => 'nullable|string|max:255',
        'in_use' => 'boolean' // Add this line
    ];

    /**
     * Scope to get only available lorries (not in use and active)
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('in_use', self::NOT_IN_USE);
    }

    /**
     * Scope to get lorries currently in use
     */
    public function scopeInUse($query)
    {
        return $query->where('in_use', self::IN_USE);
    }

    /**
     * Scope to get active lorries
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Check if lorry is available for use
     */
    public function isAvailable()
    {
        return $this->status === self::STATUS_ACTIVE && $this->in_use === self::NOT_IN_USE;
    }

    /**
     * Mark lorry as in use
     */
    public function markAsInUse()
    {
        $this->update(['in_use' => self::IN_USE]);
    }

    /**
     * Mark lorry as not in use
     */
    public function markAsAvailable()
    {
        $this->update(['in_use' => self::NOT_IN_USE]);
    }

    /**
     * Relationship with trips
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Relationship with tasks
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get current driver using this lorry (if any)
     */
    public function currentDriver()
    {
        $currentTrip = $this->trips()
            ->where('type', 1) // Active trip (started but not ended)
            ->orderBy('date', 'desc')
            ->first();

        return $currentTrip ? $currentTrip->driver : null;
    }
}