<?php

namespace App\Http\Requests\HR\EmployeeTransfer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage attendance');
    }

    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'to_company_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'company'),
            ],
            'to_branch_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'branch'),
            ],
            'to_cluster_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'cluster'),
            ],
            'to_division_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'division'),
            ],
            'to_department_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'department'),
            ],
            'to_section_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'section'),
            ],
            'to_unit_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_items', 'id')->where('category', 'unit'),
            ],
            'effective_from' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'transfer_reason' => [
                'required',
                Rule::in([
                    'promotion',
                    'business_requirement',
                    'department_restructure',
                    'branch_relocation',
                    'employee_request',
                    'temporary_assignment',
                    'project_assignment',
                    'administrative_decision',
                    'other',
                ]),
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_id' => 'Employee',
            'to_company_id' => 'Company',
            'to_branch_id' => 'Branch',
            'to_cluster_id' => 'Cluster',
            'to_division_id' => 'Division',
            'to_department_id' => 'Department',
            'to_section_id' => 'Section',
            'to_unit_id' => 'Unit',
            'effective_from' => 'Effective From',
            'transfer_reason' => 'Transfer Reason',
            'remarks' => 'Remarks',
        ];
    }
}
