<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Models\Code;

class Customer extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'customers';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $appends = [
        'GroupDescription',
    ];

    public $fillable = [
        'code',
        'company',
        'chinese_name',
        'paymentterm',
        'group',
        'agent_id',
        'supervisor_id',
        'phone',
        'address',
        'status',
        'sst',
        'tin',
        'place_name',
        'place_address',
        'place_latitude',
        'place_longitude',
        'google_place_id',
        'destinate_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'company' => 'string',
        'paymentterm' => 'integer',
        'group' => 'string',
        'agent_id' => 'integer',
        'supervisor_id' => 'integer',
        'phone' => 'string',
        'address' => 'string',
        'sst' => 'string',
        'tin' => 'string',
        'place_latitude' => 'decimal:8',
        'place_longitude' => 'decimal:8',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'code' => 'required|string|max:255|unique:customers,code',
        'company' => 'required|string|max:255',
        'paymentterm' => 'nullable',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:65535',
        'status' => 'required',
        'sst' => 'nullable|string|max:255',
        'tin' => 'nullable|string|max:255',
        'place_name' => 'nullable|string|max:255',
        'place_address' => 'nullable|string',
        'place_latitude' => 'nullable|numeric',
        'place_longitude' => 'nullable|numeric',
        'google_place_id' => 'nullable|string|max:255',
        'destinate_id' => 'nullable|string|max:255',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function agent()
    {
        return $this->belongsTo(\App\Models\Agent::class, 'agent_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function groups()
    {
        return $this->belongsTo(\App\Models\Code::class, 'group', 'value')->where('code','customer_group');
    }

    public function supervisor()
    {
        return $this->belongsTo(\App\Models\Supervisor::class, 'supervisor_id', 'id');
    }

    public function foc(){
        return $this->hasMany(\App\Models\foc::class, 'customer_id', 'id');
    }

    public function activefoc(){
        return $this->foc()->where('startdate','<=',date('Y-m-d H:i:s'))->where('enddate','>',date('Y-m-d H:i:s'))->where('status',1);
    }

    public function specialprice(){
        return $this->hasMany(\App\Models\SpecialPrice::class, 'customer_id', 'id');
    }

    public function normalprice(){
        return $this->specialprice()->hasMany(\App\Models\Product::class);
    }

    public function getGroupDescriptionAttribute(){
        return Code::where('code','customer_group')->whereRaw('find_in_set(codes.value, "'.$this->group.'")')->select(DB::raw("GROUP_CONCAT(codes.description) as group_descr"))->get()->first()->group_descr ?? '';
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function machineRentals(): HasMany
    {
        return $this->hasMany(MachineRental::class);
    }

    /**
     * Check if customer has destination data
     */
    public function hasDestination(): bool
    {
        return !empty($this->place_name) && 
               !empty($this->place_address) && 
               !is_null($this->place_latitude) && 
               !is_null($this->place_longitude);
    }

    /**
     * Get destination data as array
     */
    public function getDestinationData(): array
    {
        return [
            'place_name' => $this->place_name,
            'place_address' => $this->place_address,
            'place_latitude' => $this->place_latitude,
            'place_longitude' => $this->place_longitude,
            'google_place_id' => $this->google_place_id,
            'destinate_id' => $this->destinate_id,
        ];
    }
}