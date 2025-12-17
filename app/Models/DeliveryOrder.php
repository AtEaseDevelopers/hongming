<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    // ==================== CONSTANTS & PROPERTIES ====================

    public static $statusOptions = [
        0 => 'Pending Approve',
        1 => 'Ready to Deliver',
        2 => 'Assigned',
        3 => 'InProgress',
        4 => 'Fully Assigned',
        5 => 'Partially Delivered',
        6 => 'Completed',
        7 => 'Cancelled',
    ];

    public static $rules = [
        'date' => 'required|date_format:Y-m-d',
        'dono' => 'required|string|max:255',
        'place_name' => 'required|string|max:255',
        'place_address' => 'required|string|max:255',
        'place_latitude' => 'required|numeric',
        'place_longitude' => 'required|numeric',
        'customer_id' => 'required|exists:customers,id',
        'product_id' => 'required|exists:products,id',
        'company_id' => 'required|exists:companies,id',
        'total_order' => 'required|integer',
        'progress_total' => 'nullable|integer',
        'strength_at' => 'nullable|string|max:255',
        'slump' => 'nullable|string|max:255',
        'remark' => 'nullable|string|max:255',
        'status' => 'sometimes|in:0,1,2,3,4,5,6,7',
    ];

    protected $fillable = [
        'date',
        'dono',
        'customer_id',
        'place_name',
        'place_address',
        'place_latitude',
        'place_longitude',
        'product_id',
        'company_id',
        'total_order',
        'progress_total',
        'strength_at',
        'slump',
        'status',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ==================== STATIC METHODS ====================

    public static function getStatusOptions()
    {
        return self::$statusOptions;
    }

    public static function getAvailableForTaskOptions($forEditing = false)
    {
        return static::availableForTask($forEditing)
                    ->pluck('dono', 'id')
                    ->toArray();
    }

    // ==================== SCOPES ====================

    public function scopeAvailableForTask($query, $forEditing = false)
    {
        return $query->where(function($q) use ($forEditing) {
            $q->whereNotIn('status', [0, 6,7])
              ->whereRaw('progress_total < total_order');
            
            if ($forEditing) {
                $q->orWhereHas('tasks');
            }
        })->orderBy('created_at');
    }

    // ==================== RELATIONSHIPS ====================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'delivery_order_id');
    }

    public function activeTasks(): HasMany
    {
        return $this->tasks()->whereNotIn('status', [2, 3]);
    }

    public function completedTasks(): HasMany
    {
        return $this->tasks()->where('status', 2);
    }

    public function returnedTasks(): HasMany
    {
        return $this->tasks()->where('status', 3);
    }

    // ==================== BUSINESS LOGIC METHODS ====================

   public function deductProgress($quantity): void
    {
        $this->progress_total -= $quantity;
        
        // Ensure we don't go below zero
        if ($this->progress_total < 0) {
            $this->progress_total = 0;
        }
        
        $this->save();
        $this->checkAndUpdateStatus(); 
    }

    public function updateProgress($newProgress): void
    {
        $this->progress_total += $newProgress;
        
        // Ensure we don't exceed total order
        if ($this->progress_total > $this->total_order) {
            $this->progress_total = $this->total_order;
        }
        
        $this->save();
        $this->checkAndUpdateStatus();
    }

    public function checkAndUpdateStatus(): void
    {
        if ($this->status === 7) {
            return;
        }
        
        $remainingQuantity = $this->getRemainingQuantity();
        $hasActiveTasks = $this->hasActiveTasks();
        $hasInProgressTasks = $this->hasInProgressTasks();
        $completedQuantity = $this->getCompletedQuantity();
        $hasReturnedTasks = $this->hasReturnedTasks();

        // Case 1: No remaining quantity and all tasks completed
        if ($remainingQuantity <= 0 && !$hasActiveTasks && $completedQuantity >= $this->total_order) {
            $this->status = 6; // Completed
        }
        // Case 2: No remaining quantity but some tasks are still active
        elseif ($remainingQuantity <= 0 && $hasActiveTasks) {
            $this->status = 4; // Fully Assigned
        }
        // Case 3: Has remaining quantity and tasks are in progress
        elseif ($remainingQuantity > 0 && $hasInProgressTasks) {
            $this->status = 3; // InProgress
        }
        // Case 4: Has remaining quantity and has assigned tasks but none in progress
        elseif ($remainingQuantity > 0 && $hasActiveTasks && !$hasInProgressTasks) {
            $this->status = 2; // Assigned
        }
        // Case 5: Has delivered some quantity but still has balance
        elseif ($completedQuantity > 0 && $completedQuantity < $this->total_order) {
            $this->status = 5; // Partially Delivered
        }
        // Case 6: Has returned tasks (should be marked as InProgress)
        elseif ($hasReturnedTasks && !in_array($this->status, [3, 6, 7])) {
            $this->status = 3; // InProgress
        }
        // Case 7: No tasks assigned but approved
        elseif (!$hasActiveTasks && $this->status != 0) {
            $this->status = 1; // Ready to Deliver
        }
        
        $this->save();
    }

    public function markAsAssigned(): void
    {
        $remainingQuantity = $this->getRemainingQuantity();
        $hasActiveTasks = $this->hasActiveTasks();

        if ($remainingQuantity <= 0 && $hasActiveTasks) {
            $this->status = 4; // Fully Assigned - no balance but tasks not completed
        } elseif ($remainingQuantity > 0 && $hasActiveTasks) {
            $this->status = 2; // Assigned - has balance and has active tasks
        } else {
            $this->status = 1; // Ready to Deliver - no active tasks
        }
        
        $this->save();
    }

    public function markAsInProgress(): void
    {
        // Allow status to be set to InProgress from any status except Pending Approve and Completed
        if (!in_array($this->status, [0, 6,7])) {
            $this->status = 3; // InProgress
            $this->save();
        }
    }

    // ==================== HELPER METHODS ====================

    public function hasActiveTasks(): bool
    {
        // FIXED: Use the raw status values from Task model constants
        return $this->tasks()
            ->whereIn('status', [
                Task::STATUS_NEW, 
                Task::STATUS_DELIVERING
            ])
            ->exists();
    }

    public function hasInProgressTasks(): bool
    {
        // FIXED: Use the raw status value
        return $this->tasks()
            ->where('status', Task::STATUS_DELIVERING)
            ->exists();
    }

    public function hasReturnedTasks(): bool
    {
        // FIXED: Use the raw status value
        return $this->tasks()
            ->where('status', Task::STATUS_RETURNED)
            ->exists();
    }

    public function allTasksCompleted(): bool
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) return false;

        $completedTasks = $this->tasks()
            ->where('status', Task::STATUS_COMPLETED)
            ->count();

        return $totalTasks === $completedTasks;
    }

    /**
     * Get the total quantity currently assigned to drivers (in progress)
     */
    public function getAssignedQuantity(): int
    {
        return $this->tasks()
            ->whereIn('status', [Task::STATUS_NEW, Task::STATUS_DELIVERING])
            ->sum('this_load');
    }

    /**
     * Get the total quantity that has been completed/delivered
     */
    public function getCompletedQuantity(): int
    {
        return $this->progress_total;
    }

    public function getTotalAssignedAndCompletedQuantity(): int
    {
        return $this->getAssignedQuantity() + $this->getCompletedQuantity();
    }

    /**
     * Get the actual remaining quantity available for new assignments
     * This considers both completed deliveries and currently assigned quantities
     */
    public function getRemainingQuantity(): int
    {
        $totalAssignedAndCompleted = $this->getTotalAssignedAndCompletedQuantity();
        return max(0, $this->total_order - $totalAssignedAndCompleted);
    }

    /**
     * Check if the delivery order is fully assigned (no remaining quantity)
     */
    public function isFullyAssigned(): bool
    {
        return $this->getRemainingQuantity() <= 0;
    }

    public function isAvailableForTask(): bool
    {
        return !in_array($this->status, [0, 6,7]) && $this->getRemainingQuantity() > 0;
    }

    public function approve(): bool
    {
        if ($this->status === 0) { // Only approve if status is New
            $this->status = 1; // Change to Ready to Deliver
            $this->save();
            return true;
        }
        return false;
    }
}