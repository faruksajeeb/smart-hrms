<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAnalysis extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const PROVIDER_OPENAI = 'openai';

    public const PROVIDER_GOOGLE = 'google';

    /**
     * @return list<string>
     */
    public static function providers(): array
    {
        return [
            self::PROVIDER_OPENAI,
            self::PROVIDER_GOOGLE,
        ];
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'prompt',
        'ai_provider',
        'response',
        'status',
        'error_message',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }

    public function getCompletedAtAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i');
    }
}
