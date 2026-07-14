<?php

use App\Models\MasterDataItem;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('hr users can manage master data with valid parent records', function () {
    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->get(route('hr.master-data.index'))
        ->assertOk();

    $this->actingAs($hr)->post(route('hr.master-data.store'), [
        'category' => MasterDataItem::CATEGORY_COMPANY,
        'parent_id' => null,
        'code' => 'ACME',
        'name' => 'Acme Group',
        'description' => 'Group company.',
        'status' => MasterDataItem::STATUS_ACTIVE,
        'sort_order' => 1,
    ])->assertRedirect(route('hr.master-data.index', ['category' => MasterDataItem::CATEGORY_COMPANY], absolute: false));

    $company = MasterDataItem::where('code', 'ACME')->firstOrFail();

    $this->actingAs($hr)->post(route('hr.master-data.store'), [
        'category' => MasterDataItem::CATEGORY_BRANCH,
        'parent_id' => $company->id,
        'code' => 'DHK',
        'name' => 'Dhaka Branch',
        'description' => null,
        'status' => MasterDataItem::STATUS_ACTIVE,
        'sort_order' => 1,
    ])->assertRedirect(route('hr.master-data.index', ['category' => MasterDataItem::CATEGORY_BRANCH], absolute: false));

    $branch = MasterDataItem::where('code', 'DHK')->firstOrFail();
    expect($branch->parent_id)->toBe($company->id);

    $this->actingAs($hr)->put(route('hr.master-data.update', $branch), [
        'category' => MasterDataItem::CATEGORY_BRANCH,
        'parent_id' => $company->id,
        'code' => 'DHK-01',
        'name' => 'Dhaka Main Branch',
        'description' => 'Main office branch.',
        'status' => MasterDataItem::STATUS_ACTIVE,
        'sort_order' => 2,
    ])->assertRedirect(route('hr.master-data.index', ['category' => MasterDataItem::CATEGORY_BRANCH], absolute: false));

    expect($branch->refresh()->code)->toBe('DHK-01');

    $this->actingAs($hr)->delete(route('hr.master-data.destroy', $company))
        ->assertSessionHas('error');

    $this->actingAs($hr)->delete(route('hr.master-data.destroy', $branch))
        ->assertRedirect(route('hr.master-data.index', ['category' => MasterDataItem::CATEGORY_BRANCH], absolute: false));

    expect(MasterDataItem::whereKey($branch->id)->exists())->toBeFalse();
});

test('master data rejects invalid parent category', function () {
    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $bank = MasterDataItem::create([
        'category' => MasterDataItem::CATEGORY_BANK,
        'code' => 'BANK',
        'name' => 'Example Bank',
        'status' => MasterDataItem::STATUS_ACTIVE,
        'sort_order' => 1,
    ]);

    $this->actingAs($hr)->post(route('hr.master-data.store'), [
        'category' => MasterDataItem::CATEGORY_BRANCH,
        'parent_id' => $bank->id,
        'code' => 'BAD',
        'name' => 'Invalid Branch',
        'description' => null,
        'status' => MasterDataItem::STATUS_ACTIVE,
        'sort_order' => 1,
    ])->assertSessionHasErrors('parent_id');
});

test('master data actions require essential permissions', function () {
    $viewPermission = Permission::findOrCreate('master-data.view-master-data', 'web');
    Permission::findOrCreate('master-data.create-master-data', 'web');

    $role = Role::findOrCreate(User::ROLE_HR, 'web');
    $role->syncPermissions([$viewPermission]);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->get(route('hr.master-data.index'))
        ->assertOk();

    $this->actingAs($hr)->post(route('hr.master-data.store'), [
        'category' => MasterDataItem::CATEGORY_COMPANY,
        'parent_id' => null,
        'code' => 'NOPE',
        'name' => 'Blocked Company',
        'description' => null,
        'status' => MasterDataItem::STATUS_ACTIVE,
        'sort_order' => 1,
    ])->assertForbidden();
});
