<?php
namespace App\Console\Commands;
use App\Models\AttendancePayrollPeriod;
use App\Services\HR\AttendancePayrollService;
use Illuminate\Console\Command;
class ProcessAttendancePayrollCommand extends Command { protected $signature='attendance:payroll-process {period}'; protected $description='Generate payroll attendance summaries for a period'; public function handle(AttendancePayrollService $service):int{$period=AttendancePayrollPeriod::findOrFail($this->argument('period'));$this->info('Generated '.$service->process($period,auth()->id()??1).' payroll summaries.');return self::SUCCESS;} }
