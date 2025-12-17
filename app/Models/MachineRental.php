<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineRental extends Model
{
    use HasFactory;

    protected $table = 'machine_rental';

    protected $fillable = [
        'company_id',
        'delivery_order_number',
        'customer_id',
        'date',
        'issued_by',
        'total_amount',
        'remark',

    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the company that owns the machine rental.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the customer that owns the machine rental.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

     public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
    
    /**
     * Get the items for the machine rental.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MachineRentalItem::class);
    }
}