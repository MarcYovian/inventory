<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search');
        $type = $request->query('type');

        $movements = StockMovement::with(['product', 'user'])
            ->when($search, function ($query, $searchTerm) {
                $query->whereHas('product', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('sku', 'like', '%' . $searchTerm . '%');
                })->orWhere('notes', 'like', '%' . $searchTerm . '%');
            })
            ->when($type, function ($query, $typeValue) {
                $query->where('type', $typeValue);
            })
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'message' => 'Stock movements retrieved successfully',
            'data' => $movements,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockMovementRequest $request)
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        $user = Auth::user();

        try {
            if ($validated['type'] === 'in') {
                $this->stockService->addStock(
                    $product,
                    $validated['quantity'],
                    $user,
                    $validated['notes'] ?? null
                );
            } else {
                $this->stockService->reduceStock(
                    $product,
                    $validated['quantity'],
                    $user,
                    $validated['notes'] ?? null
                );
            }

            $product->refresh();

            return response()->json([
                'message' => $validated['type'] === 'in'
                    ? 'Stock added successfully'
                    : 'Stock reduced successfully',
                'data' => [
                    'product' => $product,
                    'new_stock' => $product->current_stock,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process stock movement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMovement $stockMovement)
    {
        $stockMovement->load(['product', 'user']);

        return response()->json([
            'message' => 'Stock movement retrieved successfully',
            'data' => $stockMovement,
        ], 200);
    }

    /**
     * Get stock movements by product.
     */
    public function byProduct(Request $request, $productId)
    {
        $perPage = $request->query('per_page', 15);

        $product = Product::findOrFail($productId);

        $movements = StockMovement::with('user')
            ->where('product_id', $productId)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'message' => 'Stock movements for product retrieved successfully',
            'data' => [
                'product' => $product,
                'movements' => $movements,
            ],
        ], 200);
    }
}
