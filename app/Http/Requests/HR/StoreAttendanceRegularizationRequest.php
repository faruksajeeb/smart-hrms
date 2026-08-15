<?php
namespace App\Http\Requests\HR;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreAttendanceRegularizationRequest extends FormRequest
{
    public function authorize(): bool { return (bool)$this->user(); }
    public function rules(): array { return ['attendance_daily_record_id'=>['required','exists:attendance_daily_records,id'],'regularization_type'=>['required',Rule::in(['missing_punch','correct_in','correct_out','correct_in_out','attendance_status','shift_correction','other'])],'requested_in'=>['nullable','date'],'requested_out'=>['nullable','date','after_or_equal:requested_in'],'requested_status'=>['nullable','string','max:40'],'reason'=>['required','string','min:5','max:2000'],'remarks'=>['nullable','string','max:2000']]; }
}
