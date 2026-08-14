<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveYearEndProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'processing_year', 'target_year', 'batch_key', 'company_id', 'branch_id', 'status',
        'started_at', 'completed_at', 'processed_by', 'total_employees',
        'total_leave_types', 'total_closing_balance', 'total_carry_forward_days',
        'total_expired_days', 'total_encashment_days', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime', 'completed_at' => 'datetime',
            'total_closing_balance' => 'decimal:2', 'total_carry_forward_days' => 'decimal:2',
            'total_expired_days' => 'decimal:2', 'total_encashment_days' => 'decimal:2',
        ];
    }

    public function items(): HasMany { return $this->hasMany(LeaveYearEndProcessItem::class, 'year_end_process_id'); }
    public function processor(): BelongsTo { return $this->belongsTo(User::class, 'processed_by'); }
    public function company(): BelongsTo { return $this->belongsTo(MasterDataItem::class, 'company_id'); }
    public function branch(): BelongsTo { return $this->belongsTo(MasterDataItem::class, 'branch_id'); }
}
