<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineRentalItem extends Model
{
    use HasFactory;

    protected $table = 'machine_rental_items';

    protected $fillable = [
        'machine_rental_id',
        'product_id',
        'uom', // Added UOM field
        'description',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the machine rental that owns the item.
     */
    public function machineRental(): BelongsTo
    {
        return $this->belongsTo(MachineRental::class);
    }

    /**
     * Get the product that owns the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the amount based on quantity and unit price.
     */
    public function calculateAmount(): void
    {
        $this->amount = $this->quantity * $this->unit_price;
    }
}