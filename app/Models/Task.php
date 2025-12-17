<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    // ==================== CONSTANTS & PROPERTIES ====================

    public $table = 'tasks';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const STATUS_NEW = 0;
    const STATUS_DELIVERING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_RETURNED = 3;

    public static $statusOptions = [
        self::STATUS_NEW => 'New',
        self::STATUS_DELIVERING => 'Delivering',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_RETURNED => 'Returned',
    ];

    public $fillable = [
        'date',
        'task_number',
        'trip_id',
        'driver_id',
        'lorry_id',
        'company_id',
        'delivery_order_id',
        'status',
        'this_load',
        'start_time',
        'end_time',
        'time_taken',  
        'countdown',
        'is_late',
        'return_reason',
        'return_remarks',
    ];

    protected $casts = [
        'id' => 'integer',
        'date' => 'date:Y-m-d',
        'lorry_id' => 'integer',
        'driver_id' => 'integer',
        'customer_id' => 'integer',
        'invoice_id' => 'integer',
        'status' => 'integer',
        'is_late' => 'boolean',
        'this_load' => 'integer',
    ];

    // ==================== BOOT & EVENTS ====================
    
    public function getStatusAttribute($value): string
    {
        return self::$statusOptions[$value] ?? 'Unknown';
    }

    public function getStatusValue(): int
    {
        return $this->attributes['status'];
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($task) {
            $task->handleTaskCreated();
        });

        static::updated(function ($task) {
            $task->handleTaskUpdated();
        });
    }

    // ==================== EVENT HANDLERS ====================

    protected function handleTaskCreated(): void
    {
        if (!$this->deliveryOrder) {
            return;
        }
        
        // When task is created, add progress to delivery order
        $this->deliveryOrder->updateProgress($this->this_load);
        $this->deliveryOrder->checkAndUpdateStatus();
    }

    protected function handleTaskUpdated(): void
    {
        if (!$this->isDirty('status') || !$this->deliveryOrder) {
            return;
        }
        
        $oldStatus = $this->getOriginal('status');
        $newStatus = $this->getStatusValue();
        
        // Handle status change logic
        $this->handleStatusChange($oldStatus, $newStatus);
    }

    // ==================== CENTRALIZED STATUS HANDLER ====================

    protected function handleStatusChange($oldStatus, $newStatus): void
    {
        // Handle RETURNED status - deduct progress
        if ($newStatus === self::STATUS_RETURNED && $oldStatus !== self::STATUS_RETURNED) {
            $this->deliveryOrder->deductProgress($this->this_load);
            $this->ensureEndTime();
        }
        // Handle changing FROM RETURNED to another status - add progress back
        elseif ($oldStatus === self::STATUS_RETURNED && $newStatus !== self::STATUS_RETURNED) {
            $this->deliveryOrder->updateProgress($this->this_load);
        }
        // Handle DELIVERING status - mark as in progress
        elseif ($newStatus === self::STATUS_DELIVERING) {
            $this->deliveryOrder->markAsInProgress();
        }
        // Handle COMPLETED status - just ensure end time
        elseif ($newStatus === self::STATUS_COMPLETED) {
            $this->ensureEndTime();
        }
        
        // Always update delivery order status
        $this->deliveryOrder->checkAndUpdateStatus();
    }

    // ==================== HELPER METHODS ====================

    protected function ensureEndTime(): void
    {
        if (!$this->end_time) {
            $this->end_time = now();
            $this->saveQuietly(); // Save without triggering events
        }
    }

    // ==================== RELATIONSHIPS ====================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function lorry(): BelongsTo
    {
        return $this->belongsTo(Lorry::class, 'lorry_id');
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    // ==================== BUSINESS LOGIC METHODS ====================

    public function canReturn(): bool
    {
        return in_array($this->getStatusValue(), [self::STATUS_DELIVERING, self::STATUS_COMPLETED]);
    }

    public function startTrip($startTime = null): bool
    {
        if ($this->getStatusValue() !== self::STATUS_NEW) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_DELIVERING,
            'start_time' => $startTime ?: now(),
        ]);

        return true;
    }

    public function endTrip($endTime = null): bool
    {
        if ($this->getStatusValue() !== self::STATUS_DELIVERING) {
            return false;
        }

        $endTime = $endTime ?: now();
        
        // Calculate time taken
        $start = Carbon::parse($this->start_time);
        $timeTakenInSeconds = $endTime->diffInSeconds($start);
        
        $hours = floor($timeTakenInSeconds / 3600);
        $minutes = floor(($timeTakenInSeconds % 3600) / 60);
        $seconds = $timeTakenInSeconds % 60;
        $timeTaken = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        // Check if late
        $countdownMinutes = $this->getCountdownInMinutes();
        $timeTakenMinutes = $timeTakenInSeconds / 60;
        $isLate = $countdownMinutes && ($timeTakenMinutes > $countdownMinutes);

        $this->update([
            'status' => self::STATUS_COMPLETED,
            'end_time' => $endTime,
            'time_taken' => $timeTaken,
            'is_late' => $isLate,
        ]);

        return true;
    }

    public function markAsReturned(string $reason, ?string $remarks = null, $returnTime = null): bool
    {
        if (!$this->canReturn()) {
            return false;
        }

        $returnTime = $returnTime ?: now();

        $updateData = [
            'status' => self::STATUS_RETURNED,
            'return_reason' => $reason,
            'return_remarks' => $remarks,
            'end_time' => $returnTime,
        ];
        
        // Calculate time taken if start_time exists
        if ($this->start_time) {
            $start = Carbon::parse($this->start_time);
            $timeTakenInSeconds = $returnTime->diffInSeconds($start);
            
            $hours = floor($timeTakenInSeconds / 3600);
            $minutes = floor(($timeTakenInSeconds % 3600) / 60);
            $seconds = $timeTakenInSeconds % 60;
            $updateData['time_taken'] = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            
            // Check if late
            $countdownMinutes = $this->getCountdownInMinutes();
            $timeTakenMinutes = $timeTakenInSeconds / 60;
            $updateData['is_late'] = $countdownMinutes && ($timeTakenMinutes > $countdownMinutes);
        }

        return $this->update($updateData);
    }

    // ==================== COUNTDOWN METHODS ====================

    public function getCountdownInMinutes()
    {
        if (!$this->countdown) {
            return null;
        }
        
        try {
            $time = Carbon::createFromFormat('H:i:s', $this->countdown);
            return ($time->hour * 60) + $time->minute;
        } catch (\Exception $e) {
            return $this->fallbackCountdownConversion();
        }
    }

    protected function fallbackCountdownConversion()
    {
        $time = explode(':', $this->countdown);
        $hours = (int)($time[0] ?? 0);
        $minutes = (int)($time[1] ?? 0);
        return ($hours * 60) + $minutes;
    }

    public function setCountdownAttribute($value)
    {
        if (is_numeric($value)) {
            $value = (int) $value;
            $time = Carbon::createFromTime(
                floor($value / 60),
                $value % 60,
                0
            );
            $this->attributes['countdown'] = $time->format('H:i:s');
        } else {
            $this->attributes['countdown'] = $value;
        }
    }

    // ==================== ACCESSORS & MUTATORS ====================

    public function getDateAttribute($value): string
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
    
    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }
    
    public static function getStatusOptions(): array
    {
        return self::$statusOptions;
    }

    // ==================== OTHER HELPER METHODS ====================

    public function getEffectiveDeliveredQuantity(): float
    {
        return $this->getStatusValue() === self::STATUS_RETURNED 
            ? max(0, $this->this_load - ($this->return_quantity ?? 0))
            : $this->this_load;
    }
    
    public function getTimeTakenFormatted(): string
    {
        if (!$this->time_taken) {
            return 'N/A';
        }

        if (is_string($this->time_taken)) {
            $time = explode(':', $this->time_taken);
            $hours = (int)($time[0] ?? 0);
            $minutes = (int)($time[1] ?? 0);
            
            if ($hours > 0) {
                return sprintf('%dh %dm', $hours, $minutes);
            }
            return sprintf('%dm', $minutes);
        }
        
        $hours = floor($this->time_taken / 60);
        $minutes = $this->time_taken % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        }

        return sprintf('%dm', $minutes);
    }

    public function getEstimatedCompletionTime()
    {
        if (!$this->start_time || !$this->countdown) {
            return null;
        }

        $countdownMinutes = $this->getCountdownInMinutes();
        if (!$countdownMinutes) {
            return null;
        }

        return Carbon::parse($this->start_time)->addMinutes($countdownMinutes);
    }

    public function getCountdownFormatted(): string
    {
        $minutes = $this->getCountdownInMinutes();
        
        if (!$minutes) {
            return 'N/A';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $mins);
        }

        return sprintf('%dm', $mins);
    }

    public function deliveryImage()
    {
        return $this->hasOne(DeliveryImage::class, 'task_id');
    }
}