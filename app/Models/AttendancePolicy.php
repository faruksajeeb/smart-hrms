<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Relations\HasOne; use Illuminate\Database\Eloquent\Relations\HasMany;
class AttendancePolicy extends Model { use HasFactory, SoftDeletes; protected $fillable = ['policy_name','policy_code','description','effective_from','effective_to','status','created_by','updated_by']; protected function casts(): array { return ['effective_from'=>'date','effective_to'=>'date']; } public function rules(): HasOne { return $this->hasOne(AttendancePolicyRule::class); } public function assignments(): HasMany { return $this->hasMany(AttendancePolicyAssignment::class); } }
