<?php

namespace App\Http\Requests\HR;

use App\Models\MasterDataItem;
use Illuminate\Validation\Rule;

class UpdateMasterDataItemRequest extends StoreMasterDataItemRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var MasterDataItem $masterDataItem */
        $masterDataItem = $this->route('master_data_item');

        return [
            'category' => ['required', Rule::in(array_keys(MasterDataItem::categories()))],
            'parent_id' => ['nullable', 'integer', Rule::exists('master_data_items', 'id')],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(MasterDataItem::class, 'code')
                    ->where('category', $this->input('category'))
                    ->ignore($masterDataItem->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(MasterDataItem::class, 'name')
                    ->where('category', $this->input('category'))
                    ->ignore($masterDataItem->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in([MasterDataItem::STATUS_ACTIVE, MasterDataItem::STATUS_INACTIVE])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
