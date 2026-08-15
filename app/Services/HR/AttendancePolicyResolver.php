<?php
namespace App\Services\HR;
use App\Models\AttendancePolicyAssignment; use App\Models\User; use Carbon\Carbon;

class AttendancePolicyResolver
{
    private const PRIORITY = ['user_id','designation_id','employment_type','unit_id','section_id','department_id','division_id','branch_id','company_id'];
    public function resolveForEmployee(User $employee, Carbon $date): ?AttendancePolicyAssignment
    {
        $date = $date->toDateString(); $q = AttendancePolicyAssignment::query()->with(['policy.rules'])->where('status','active')->where('effective_from','<=',$date)->where(fn($x)=>$x->whereNull('effective_to')->orWhere('effective_to','>=',$date));
        $assignments = $q->where(function($x) use ($employee) { $x->where('user_id',$employee->id)->orWhere(function($s) use ($employee) { foreach (['designation_id','unit_id','section_id','department_id','division_id','branch_id','company_id'] as $f) if ($employee->{$f}) $s->orWhere($f,$employee->{$f}); }); })->get();
        $employmentType = $employee->employmentType?->code ?? $employee->employmentType?->name;
        $assignments = $assignments->filter(function ($a) use ($employee, $employmentType) { if ($a->user_id) return (int)$a->user_id === (int)$employee->id; if ($a->employment_type && $a->employment_type !== $employmentType) return false; foreach (['company_id','branch_id','division_id','department_id','section_id','unit_id','designation_id'] as $f) if ($a->{$f} && (int)$a->{$f} !== (int)$employee->{$f}) return false; return true; });
        foreach (self::PRIORITY as $field) { $matches = $assignments->filter(fn($a) => $field === 'employment_type' ? (bool)$a->employment_type : (bool)$a->{$field}); if ($matches->count() > 1) throw new \RuntimeException("Overlapping attendance policy assignments for employee {$employee->id} at {$field} scope."); if ($matches->isNotEmpty()) return $matches->first(); }
        return null;
    }
}
