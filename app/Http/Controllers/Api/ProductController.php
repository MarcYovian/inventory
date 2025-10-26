<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductCreateRequest;
use App\Http\Requests\Api\ProductUpdateRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');

        $products = Product::query()
            ->when($search, function ($query, $searchTerm) {
                return $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('sku', 'like', '%' . $searchTerm . '%');
                });
            })
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateRequest $request)
    {
        $validated = $request->validated();
        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'message' => 'Product retrieved successfully',
            'data' => $product,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $validated = $request->validated();
        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }
}
