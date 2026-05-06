<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Modules\Catalog\Services\BrandService;
use Modules\Catalog\Services\CategoryService;

uses(RefreshDatabase::class);

test('category with products is protected from deletion', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $categoryService = app(CategoryService::class);

    // CategoryService::deleteCategory throws exception for protected categories
    $this->expectException(ValidationException::class);
    $categoryService->deleteCategory($category);
});

test('brand with products is protected from deletion', function () {
    $brand = Brand::factory()->create();
    Product::factory()->create(['brand_id' => $brand->id]);

    $brandService = app(BrandService::class);

    $result = $brandService->deleteBrand($brand);

    expect($result)->toBeFalse();
    expect(Brand::find($brand->id))->not->toBeNull();
});

test('empty category can be deleted', function () {
    $category = Category::factory()->create();
    $categoryService = app(CategoryService::class);

    $result = $categoryService->deleteCategory($category);

    expect($result)->toBeTrue();
    expect(Category::find($category->id))->toBeNull();
});
