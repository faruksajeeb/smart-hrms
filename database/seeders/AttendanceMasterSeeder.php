<?php
namespace Database\Seeders;
use App\Models\AttendanceStatus; use Illuminate\Database\Seeder;
class AttendanceMasterSeeder extends Seeder { public function run(): void { foreach ([['present','Present',true],['absent','Absent',false],['late','Late',true],['early_out','Early Out',true],['late_early_out','Late & Early Out',true],['half_day','Half Day',true],['holiday','Holiday',false],['weekly_off','Weekly Off',false],['leave','Leave',false],['missing_punch','Missing Punch',true],['incomplete','Incomplete',true],['on_duty','On Duty',true],['work_from_home','Work From Home',true],['off_day','Off Day',false]] as $i=>$row) AttendanceStatus::updateOrCreate(['code'=>$row[0]],['name'=>$row[1],'is_working'=>$row[2],'is_active'=>true,'sort_order'=>$i]); } }
