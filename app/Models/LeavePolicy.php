<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\LeavePolicyStatus;

#[Fillable([
    'policy_name',
    'policy_code',
    'description',
    'effective_from',
    'effective_to',
    'status',
    'created_by',
    'updated_by',
])]
class LeavePolicy extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'status' => LeavePolicyStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(LeavePolicyDetail::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeavePolicyAssignment::class);
    }

        #effective_from
    public function getEffectiveFromAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');  
    }

    #effective_to
    public function getEffectiveToAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');  
    }
}
