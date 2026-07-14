<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'document_type',
    'file',
    'original_filename',
    'mime_type',
    'expiry_date',
    'remarks',
])]
class EmployeeDocument extends Model
{
    use HasFactory;

    public const TYPE_PHOTO = 'photo';

    public const TYPE_CV = 'cv';

    public const TYPE_NID = 'nid';

    public const TYPE_PASSPORT = 'passport';

    public const TYPE_CERTIFICATES = 'certificates';

    public const TYPE_APPOINTMENT_LETTER = 'appointment_letter';

    public const TYPE_JOINING_LETTER = 'joining_letter';

    /** @return array<string, string> */
    public static function types(): array
    {
        return [
            self::TYPE_PHOTO => 'Photo',
            self::TYPE_CV => 'CV',
            self::TYPE_NID => 'NID',
            self::TYPE_PASSPORT => 'Passport',
            self::TYPE_CERTIFICATES => 'Certificates',
            self::TYPE_APPOINTMENT_LETTER => 'Appointment Letter',
            self::TYPE_JOINING_LETTER => 'Joining Letter',
        ];
    }

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    protected function label(): Attribute
    {
        return Attribute::get(fn () => self::types()[$this->document_type] ?? $this->document_type);
    }
}
