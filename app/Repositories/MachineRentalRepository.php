<?php

namespace App\Repositories;

use App\Models\MachineRental;
use App\Repositories\BaseRepository;

/**
 * Class MachineRentalRepository
 * @package App\Repositories
 * @version [Current Date]
*/

class MachineRentalRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'delivery_order_number',
        'date',
        'customer_id',
        'company_id',
        'lorry_number',
        'issued_by',
        'approved_by',
        'received_by',
        'total_amount',
        'remark'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return MachineRental::class;
    }

    /**
     * Create machine rental with items
     *
     * @param array $attributes
     * @return MachineRental
     */
    public function createWithItems(array $attributes)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($attributes) {
            $machineRental = $this->create($attributes);

            if (isset($attributes['items']) && is_array($attributes['items'])) {
                foreach ($attributes['items'] as $item) {
                    $machineRental->items()->create($item);
                }
            }

            return $machineRental->load('items');
        });
    }

    /**
     * Update machine rental with items
     *
     * @param array $attributes
     * @param int $id
     * @return MachineRental
     */
    public function updateWithItems(array $attributes, $id)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($attributes, $id) {
            $machineRental = $this->update($attributes, $id);

            // Delete existing items and create new ones
            if (isset($attributes['items']) && is_array($attributes['items'])) {
                $machineRental->items()->delete();
                
                foreach ($attributes['items'] as $item) {
                    $machineRental->items()->create($item);
                }
            }

            return $machineRental->load('items');
        });
    }

    /**
     * Find machine rental with relationships
     *
     * @param int $id
     * @param array $with
     * @return MachineRental|null
     */
    public function findWithRelations($id, $with = ['customer', 'company', 'items.product'])
    {
        return $this->model->with($with)->find($id);
    }

    /**
     * Get machine rentals by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByDateRange($startDate, $endDate, $with = ['customer', 'company', 'items.product'])
    {
        return $this->model
            ->with($with)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('delivery_order_number', 'desc')
            ->get();
    }

    /**
     * Get machine rentals by customer
     *
     * @param int $customerId
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCustomer($customerId, $with = ['company', 'items.product'])
    {
        return $this->model
            ->with($with)
            ->where('customer_id', $customerId)
            ->orderBy('date', 'desc')
            ->orderBy('delivery_order_number', 'desc')
            ->get();
    }

    /**
     * Get machine rentals by company
     *
     * @param int $companyId
     * @param array $with
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCompany($companyId, $with = ['customer', 'items.product'])
    {
        return $this->model
            ->with($with)
            ->where('company_id', $companyId)
            ->orderBy('date', 'desc')
            ->orderBy('delivery_order_number', 'desc')
            ->get();
    }

    /**
     * Check if delivery order number exists
     *
     * @param string $deliveryOrderNumber
     * @param int|null $excludeId
     * @return bool
     */
    public function deliveryOrderNumberExists($deliveryOrderNumber, $excludeId = null)
    {
        $query = $this->model->where('delivery_order_number', $deliveryOrderNumber);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get total amount by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalAmountByDateRange($startDate, $endDate)
    {
        return $this->model
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('total_amount');
    }
}