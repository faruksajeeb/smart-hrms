<?php

namespace App\Services;

use App\Models\MasterDataItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class EmployeeMasterDataService
{
    /**
     * Master data selections submitted from the employee onboarding form.
     *
     * @var array<string, string>
     */
    public const SELECTION_FIELDS = [
        'company_master_data_id' => MasterDataItem::CATEGORY_COMPANY,
        'branch_master_data_id' => MasterDataItem::CATEGORY_BRANCH,
        'division_master_data_id' => MasterDataItem::CATEGORY_DIVISION,
        'department_master_data_id' => MasterDataItem::CATEGORY_DEPARTMENT,
        'designation_master_data_id' => MasterDataItem::CATEGORY_DESIGNATION,
        'employment_type_master_data_id' => MasterDataItem::CATEGORY_EMPLOYEE_TYPE,
        'bank_master_data_id' => MasterDataItem::CATEGORY_BANK,
        'pay_type_master_data_id' => MasterDataItem::CATEGORY_PAY_TYPE,
        'job_grade_master_data_id' => MasterDataItem::CATEGORY_JOB_GRADE,
        'religion_master_data_id' => MasterDataItem::CATEGORY_RELIGION,
        'blood_group_master_data_id' => MasterDataItem::CATEGORY_BLOOD_GROUP,
        'marital_status_master_data_id' => MasterDataItem::CATEGORY_MARITAL_STATUS,
        'qualification_master_data_id' => MasterDataItem::CATEGORY_QUALIFICATION,
    ];

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function resolveProfileValues(array $input): array
    {
        $items = $this->selectedItems($input);

        return [
            'department' => $items->get(MasterDataItem::CATEGORY_DEPARTMENT)?->name ?? $input['department'] ?? null,
            'designation' => $items->get(MasterDataItem::CATEGORY_DESIGNATION)?->name ?? $input['designation'] ?? null,
            'employment_type' => $items->get(MasterDataItem::CATEGORY_EMPLOYEE_TYPE)?->code
                ?? $items->get(MasterDataItem::CATEGORY_EMPLOYEE_TYPE)?->name
                ?? $input['employment_type'] ?? null,
            'work_location' => $items->get(MasterDataItem::CATEGORY_BRANCH)?->name ?? $input['work_location'] ?? null,
            'bank_name' => $items->get(MasterDataItem::CATEGORY_BANK)?->name ?? $input['bank_name'] ?? null,
            'religion' => $items->get(MasterDataItem::CATEGORY_RELIGION)?->name ?? $input['religion'] ?? null,
            'blood_group' => $items->get(MasterDataItem::CATEGORY_BLOOD_GROUP)?->name ?? $input['blood_group'] ?? null,
            'marital_status' => $items->get(MasterDataItem::CATEGORY_MARITAL_STATUS)?->name ?? $input['marital_status'] ?? null,
            'qualification' => $items->get(MasterDataItem::CATEGORY_QUALIFICATION)?->name ?? $input['qualification'] ?? null,
            'pay_frequency' => $items->get(MasterDataItem::CATEGORY_PAY_TYPE)?->code
                ?? $items->get(MasterDataItem::CATEGORY_PAY_TYPE)?->name
                ?? $input['pay_frequency'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function syncTags(User $employee, array $input): void
    {
        $selected = $this->selectedItems($input);

        $tagIds = collect();

        foreach ($selected as $item) {
            $tagIds = $tagIds->merge($this->ancestorIds($item))->push($item->id);
        }

        $employee->masterDataItems()->sync($tagIds->unique()->values()->all());
    }

    /**
     * @return array<string, array<int, array{id: int, label: string}>>
     */
    public function formOptions(): array
    {
        $items = MasterDataItem::query()
            ->with('parent:id,category,name')
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'category', 'name', 'code', 'parent_id']);

        $options = [];

        foreach (self::SELECTION_FIELDS as $field => $category) {
            $options[$field] = $items
                ->where('category', $category)
                ->map(fn (MasterDataItem $item) => [
                    'id' => $item->id,
                    'label' => $this->optionLabel($item),
                ])
                ->values()
                ->all();
        }

        return $options;
    }

    /**
     * @return array<string, int|null>
     */
    public function selectedIdsForUser(User $employee): array
    {
        $employee->loadMissing('masterDataItems:id,category');

        $selected = [];

        foreach (self::SELECTION_FIELDS as $field => $category) {
            $selected[$field] = $employee->masterDataItems
                ->firstWhere('category', $category)
                ?->id;
        }

        return $selected;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function validationRules(): array
    {
        $rules = [];

        foreach (self::SELECTION_FIELDS as $field => $category) {
            $rules[$field] = ['nullable', 'integer', 'exists:master_data_items,id'];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function validateSelections(array $input): void
    {
        $errors = [];

        foreach (self::SELECTION_FIELDS as $field => $expectedCategory) {
            $value = $input[$field] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $item = MasterDataItem::query()->find($value);

            if ($item === null) {
                continue;
            }

            if ($item->category !== $expectedCategory) {
                $errors[$field] = 'The selected master data item does not match the expected category.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @return Collection<string, MasterDataItem>
     */
    protected function selectedItems(array $input): Collection
    {
        $ids = collect(self::SELECTION_FIELDS)
            ->map(fn (string $category, string $field) => $input[$field] ?? null)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->values()
            ->all();

        if ($ids === []) {
            return collect();
        }

        return MasterDataItem::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('category');
    }

    /**
     * @return Collection<int, int>
     */
    protected function ancestorIds(MasterDataItem $item): Collection
    {
        $ids = collect();
        $current = $item->parent_id
            ? MasterDataItem::query()->find($item->parent_id)
            : null;

        while ($current !== null) {
            $ids->push($current->id);
            $current = $current->parent_id
                ? MasterDataItem::query()->find($current->parent_id)
                : null;
        }

        return $ids;
    }

    protected function optionLabel(MasterDataItem $item): string
    {
        if ($item->parent === null) {
            return $item->name;
        }

        $parentCategory = MasterDataItem::categories()[$item->parent->category] ?? $item->parent->category;

        return "{$parentCategory} · {$item->parent->name} · {$item->name}";
    }
}
