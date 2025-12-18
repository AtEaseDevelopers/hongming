<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Product
 * @package App\Models
 * @version June 20, 2023, 6:43 pm +08
 *
 * @property string $code
 * @property string $name
 * @property array $uoms
 * @property integer $status
 * @property integer $countdown_minutes
 */
class Product extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'products';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    

    protected $dates = ['deleted_at'];

    public static $type = [
        0 => 'Material',
        1 => 'Machine',
    ];

    public static $status = [
        0 => 'Inactive',
        1 => 'Active',
    ];

    public function getTypeLabelAttribute()
    {
        return self::$type[$this->attributes['type']] ?? 'Unknown';
    }

    public function getStatusLabelAttribute()
    {
        return self::$status[$this->attributes['status']] ?? 'Unknown';
    }

    public $fillable = [
        'code',
        'name',
        'status',
        'type',
        'countdown',
        'uoms'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'name' => 'string',
        'status' => 'integer',
        'type' => 'integer',
        'countdown' => 'integer',
        'uoms' => 'array'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'code' => 'required|string|max:255|unique:products,code',
        'name' => 'required|string|max:255',
        'status' => 'required',
        'type' => 'required',
        'countdown' => 'nullable|integer|min:1|max:1440',
        'uoms' => 'sometimes|nullable|array',
        'uoms.*.name' => 'required_if:type,1|string|max:50|nullable',
        'uoms.*.price' => 'required_if:type,1|numeric|min:0|nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function deliveryOrderItems(): HasMany
    {
        return $this->hasMany(DeliveryOrderItem::class);
    }

    public function machineRentalItems(): HasMany
    {
        return $this->hasMany(MachineRentalItem::class);
    }

    /**
     * Get the default countdown in minutes for this product
     */
    public function getDefaultCountdown()
    {
        return $this->countdown ?? 60;
    }

    /**
     * Get price for a specific UOM name
     */
    public function getPriceForUom($uomName)
    {
        $uoms = $this->uoms ?? [];
        foreach ($uoms as $uom) {
            if ($uom['name'] === $uomName) {
                return $uom['price'];
            }
        }
        return null;
    }

    /**
     * Get all UOM names as array
     */
    public function getUomNames()
    {
        $uoms = $this->uoms ?? [];
        $names = [];
        foreach ($uoms as $uom) {
            $names[] = $uom['name'];
        }
        return $names;
    }

    /**
     * Get all UOMs with prices as associative array
     */
    public function getUomsWithPrices()
    {
        $uoms = $this->uoms ?? [];
        $result = [];
        foreach ($uoms as $uom) {
            $result[$uom['name']] = $uom['price'];
        }
        return $result;
    }

    /**
     * Get first UOM name (for display purposes)
     */
    public function getFirstUomName()
    {
        $uoms = $this->uoms ?? [];
        return !empty($uoms) ? $uoms[0]['name'] : null;
    }

    /**
     * Get first UOM price (for display purposes)
     */
    public function getFirstUomPrice()
    {
        $uoms = $this->uoms ?? [];
        return !empty($uoms) ? $uoms[0]['price'] : 0;
    }
}