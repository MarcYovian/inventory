<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockMovementRequest;
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
        $tableHeads = ['#', 'Date', 'Product', 'SKU', 'Type', 'Qty', 'User', 'Notes'];
        $search = $request->query('search');

        $movements = StockMovement::with(['product', 'user']) // Eager load relasi
            ->when($search, function ($query, $searchTerm) {
                $query->whereHas('product', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('sku', 'like', '%' . $searchTerm . '%');
                })->orWhere('notes', 'like', '%' . $searchTerm . '%');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.stock.index', [
            'tableHeads' => $tableHeads,
            'movements' => $movements,
            'search' => $search
        ]);
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('admin.stock.create', [
            'products' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockMovementRequest $request)
    {
        $validated = $request->validated();

        try {
            $product = Product::findOrFail($validated['product_id']);
            $user = Auth::user();

            if ($validated['type'] === 'in') {
                $this->stockService->addStock(
                    $product,
                    $validated['quantity'],
                    $user,
                    $validated['notes'] ?? null
                );
                $message = 'Stock added successfully. New stock: ' . $product->fresh()->current_stock;
            } else {
                $this->stockService->reduceStock(
                    $product,
                    $validated['quantity'],
                    $user,
                    $validated['notes'] ?? null
                );
                $message = 'Stock reduced successfully. New stock: ' . $product->fresh()->current_stock;
            }

            return redirect()->route('stock-management.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
