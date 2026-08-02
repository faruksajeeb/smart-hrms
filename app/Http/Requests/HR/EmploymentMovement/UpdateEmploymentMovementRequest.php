<?php

namespace App\Http\Requests\HR\EmploymentMovement;

use App\Enums\EmploymentMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmploymentMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage attendance');
    }

    public function rules(): array
    {
        $movementType = $this->input('event_type');

        $rules = [
            'event_type' => [
                'required',
                'string',
                Rule::in(EmploymentMovementType::values()),
            ],
            'effective_from' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'effective_to' => [
                'nullable',
                'date',
                'after:effective_from',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'company_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'company'),
            ],
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'branch'),
            ],
            'cluster_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'cluster'),
            ],
            'division_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'division'),
            ],
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'department'),
            ],
            'section_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'section'),
            ],
            'unit_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'unit'),
            ],
            'designation_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'designation'),
            ],
            'employment_type_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'employee_type'),
            ],
            'reporting_manager_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];

        if ($movementType) {
            $type = EmploymentMovementType::from($movementType);
            $fields = $type->fields();

            if (in_array('company', $fields)) {
                $rules['company_id'] = array_merge(['required'], $rules['company_id']);
            }
            if (in_array('branch', $fields)) {
                $rules['branch_id'] = array_merge(['required'], $rules['branch_id']);
            }
            if (in_array('designation', $fields)) {
                $rules['designation_id'] = array_merge(['required'], $rules['designation_id']);
            }
            if (in_array('employment_type', $fields)) {
                $rules['employment_type_id'] = array_merge(['required'], $rules['employment_type_id']);
            }
            if (in_array('reporting_manager', $fields)) {
                $rules['reporting_manager_id'] = array_merge(['required'], $rules['reporting_manager_id']);
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'event_type' => 'Movement Type',
            'effective_from' => 'Effective From',
            'effective_to' => 'Effective To',
            'reason' => 'Reason',
            'remarks' => 'Remarks',
            'company_id' => 'Company',
            'branch_id' => 'Branch',
            'cluster_id' => 'Cluster',
            'division_id' => 'Division',
            'department_id' => 'Department',
            'section_id' => 'Section',
            'unit_id' => 'Unit',
            'designation_id' => 'Designation',
            'employment_type_id' => 'Employment Type',
            'reporting_manager_id' => 'Reporting Manager',
        ];
    }
}
