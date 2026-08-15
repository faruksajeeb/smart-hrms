<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class AttendanceStatus extends Model { use HasFactory, SoftDeletes; protected $fillable = ['name','code','description','color','is_working','is_active','sort_order','created_by','updated_by']; protected function casts(): array { return ['is_working'=>'boolean','is_active'=>'boolean']; } }
