<?php

namespace App\Http\Requests\HR;

use App\Models\MasterDataItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMasterDataItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(array_keys(MasterDataItem::categories()))],
            'parent_id' => ['nullable', 'integer', Rule::exists('master_data_items', 'id')],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(MasterDataItem::class, 'code')->where('category', $this->input('category')),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(MasterDataItem::class, 'name')->where('category', $this->input('category')),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in([MasterDataItem::STATUS_ACTIVE, MasterDataItem::STATUS_INACTIVE])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $category = $this->input('category');
            $parentId = $this->input('parent_id');
            $allowedParents = MasterDataItem::parentCategories()[$category] ?? [];

            if ($parentId && $allowedParents === []) {
                $validator->errors()->add('parent_id', 'This category does not support parent records.');

                return;
            }

            if (! $parentId || $allowedParents === []) {
                return;
            }

            $parent = MasterDataItem::query()->find($parentId);

            if (! $parent || ! in_array($parent->category, $allowedParents, true)) {
                $validator->errors()->add('parent_id', 'The selected parent is not valid for this category.');
            }
        });
    }
}
