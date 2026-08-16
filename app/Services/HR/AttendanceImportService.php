<?php
namespace App\Services\HR;
use App\Models\AttendanceRawLog;
use Illuminate\Support\Facades\DB;
class AttendanceImportService { public function import(iterable $rows, int $actorId, string $source='csv'): int { $count=0;foreach($rows as $row){$identifier=(string)($row['employee_identifier']??$row['employee_id']??'');$punchId=$row['punch_identifier']??$row['external_reference']??null;if(!$identifier||!$row['punch_datetime'])continue;if($punchId&&AttendanceRawLog::where(['source'=>$source,'punch_identifier'=>$punchId])->exists())continue;AttendanceRawLog::create(['employee_identifier'=>$identifier,'user_id'=>$row['user_id']??null,'device_id'=>$row['device_id']??null,'punch_identifier'=>$punchId,'punch_datetime'=>$row['punch_datetime'],'punch_type'=>$row['punch_type']??null,'source'=>$source,'device_location'=>$row['device_location']??null,'raw_payload'=>$row,'imported_at'=>now(),'processing_status'=>'pending']);$count++;}return $count;} }
