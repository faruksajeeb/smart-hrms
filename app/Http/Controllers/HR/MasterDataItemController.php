<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreMasterDataItemRequest;
use App\Http\Requests\HR\UpdateMasterDataItemRequest;
use App\Models\MasterDataItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataItemController extends Controller
{
    public function index(Request $request): Response
    {
        $category = $request->string('category')->toString() ?: MasterDataItem::CATEGORY_COMPANY;
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));

        if (! array_key_exists($category, MasterDataItem::categories())) {
            $category = MasterDataItem::CATEGORY_COMPANY;
        }

        $items = MasterDataItem::query()
            ->with('parent:id,category,name')
            ->where('category', $category)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (MasterDataItem $item) => $this->serializeItem($item));

        return Inertia::render('HR/MasterData/Index', [
            'items' => $items,
            'filters' => [
                'category' => $category,
                'search' => $search,
                'status' => $status,
            ],
            'stats' => [
                'total' => MasterDataItem::count(),
                'active' => MasterDataItem::where('status', MasterDataItem::STATUS_ACTIVE)->count(),
                'inactive' => MasterDataItem::where('status', MasterDataItem::STATUS_INACTIVE)->count(),
                'current_category' => MasterDataItem::where('category', $category)->count(),
            ],
            'options' => $this->options($category),
        ]);
    }

    public function store(StoreMasterDataItemRequest $request): RedirectResponse
    {
        $item = MasterDataItem::create($this->payload($request));

        return to_route('hr.master-data.index', ['category' => $item->category])
            ->with('success', "{$item->name} has been added to master data.");
    }

    public function update(UpdateMasterDataItemRequest $request, MasterDataItem $masterDataItem): RedirectResponse
    {
        $masterDataItem->update($this->payload($request));

        return to_route('hr.master-data.index', ['category' => $masterDataItem->category])
            ->with('success', "{$masterDataItem->name} has been updated.");
    }

    public function destroy(MasterDataItem $masterDataItem): RedirectResponse
    {
        if ($masterDataItem->children()->exists()) {
            return back()->with('error', "{$masterDataItem->name} has child records and cannot be deleted.");
        }

        $category = $masterDataItem->category;
        $name = $masterDataItem->name;

        $masterDataItem->delete();

        return to_route('hr.master-data.index', ['category' => $category])
            ->with('success', "{$name} has been deleted from master data.");
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Request $request): array
    {
        return [
            'category' => $request->string('category')->toString(),
            'parent_id' => $request->input('parent_id') ?: null,
            'code' => $request->filled('code') ? $request->string('code')->toString() : null,
            'name' => $request->string('name')->toString(),
            'description' => $request->input('description'),
            'status' => $request->string('status')->toString(),
            'sort_order' => $request->integer('sort_order'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function options(string $category): array
    {
        $allowedParentCategories = MasterDataItem::parentCategories()[$category] ?? [];

        return [
            'categories' => collect(MasterDataItem::categories())
                ->map(fn (string $label, string $value) => compact('label', 'value'))
                ->values()
                ->all(),
            'statuses' => [
                MasterDataItem::STATUS_ACTIVE,
                MasterDataItem::STATUS_INACTIVE,
            ],
            'parentCategories' => MasterDataItem::parentCategories(),
            'parentOptions' => MasterDataItem::query()
                ->whereIn('category', $allowedParentCategories)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'category', 'name'])
                ->map(fn (MasterDataItem $item) => [
                    'id' => $item->id,
                    'category' => $item->category,
                    'name' => $item->name,
                    'label' => MasterDataItem::categories()[$item->category].' · '.$item->name,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeItem(MasterDataItem $item): array
    {
        return [
            'id' => $item->id,
            'category' => $item->category,
            'category_label' => MasterDataItem::categories()[$item->category] ?? $item->category,
            'parent_id' => $item->parent_id,
            'parent' => $item->parent ? [
                'id' => $item->parent->id,
                'category' => $item->parent->category,
                'name' => $item->parent->name,
                'label' => (MasterDataItem::categories()[$item->parent->category] ?? $item->parent->category).' · '.$item->parent->name,
            ] : null,
            'code' => $item->code,
            'name' => $item->name,
            'description' => $item->description,
            'status' => $item->status,
            'sort_order' => $item->sort_order,
            'created_at' => $item->created_at?->format('M d, Y'),
        ];
    }
}
