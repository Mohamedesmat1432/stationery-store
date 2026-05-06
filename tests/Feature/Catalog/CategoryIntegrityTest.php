<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Catalog\Data\CategoryData;
use Modules\Catalog\Services\CategoryService;
use Modules\Identity\Enums\PermissionName;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => PermissionName::UPDATE_CATEGORIES->value, 'guard_name' => 'web']);
    $user->givePermissionTo(PermissionName::UPDATE_CATEGORIES->value);
    $this->actingAs($user);
});

test('it prevents setting a category as its own parent', function () {
    $category = Category::factory()->create();
    $service = app(CategoryService::class);

    $data = CategoryData::fromCategory($category);
    $data->parent_id = $category->id;

    $updated = $service->updateCategory($category, $data);

    expect($updated->parent_id)->toBeNull();
});

test('it prevents setting a descendant as a parent (circular reference)', function () {
    $root = Category::factory()->create(['name' => 'Root']);
    $child = Category::factory()->create(['name' => 'Child', 'parent_id' => $root->id]);
    $grandchild = Category::factory()->create(['name' => 'Grandchild', 'parent_id' => $child->id]);
    $service = app(CategoryService::class);

    $data = CategoryData::fromCategory($root);
    $data->parent_id = $grandchild->id;

    // Should throw ValidationException
    $service->updateCategory($root, $data);
})->throws(ValidationException::class);

test('it allows setting a valid parent', function () {
    $parent1 = Category::factory()->create(['name' => 'Parent 1']);
    $parent2 = Category::factory()->create(['name' => 'Parent 2']);
    $child = Category::factory()->create(['name' => 'Child', 'parent_id' => $parent1->id]);
    $service = app(CategoryService::class);

    $data = CategoryData::fromCategory($child);
    $data->parent_id = $parent2->id;

    $updated = $service->updateCategory($child, $data);

    expect($updated->parent_id)->toBe($parent2->id);
});
