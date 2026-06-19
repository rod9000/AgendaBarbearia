<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'brand'          => 'nullable|string|max:100',
            'expiry_date'    => 'nullable|date',
            'purchase_price' => 'required|numeric|min:0',
            'quantity'       => 'nullable|integer|min:0',
            'min_stock'      => 'nullable|integer|min:0',
            'supplier'       => 'nullable|string|max:100',
            'sale_price'     => 'nullable|numeric|min:0',
        ]);

        $data['quantity'] = $data['quantity'] ?? 0;

        Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    public function show(Product $product)
    {
        $product->load('stockMovements.user');

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'brand'          => 'nullable|string|max:100',
            'expiry_date'    => 'nullable|date',
            'purchase_price' => 'required|numeric|min:0',
            'quantity'       => 'nullable|integer|min:0',
            'min_stock'      => 'nullable|integer|min:0',
            'supplier'       => 'nullable|string|max:100',
            'sale_price'     => 'nullable|numeric|min:0',
        ]);

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Product $product)
    {
        $product->stockMovements()->delete();
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Produto excluído com sucesso!');
    }

    public function stockReport(Request $request)
    {
        $query = Product::query();

        $filter = $request->get('filter', 'all');
        $supplier = $request->get('supplier');

        if ($filter === 'low_stock') {
            $query->whereRaw('quantity > 0 AND quantity <= min_stock');
        } elseif ($filter === 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        } elseif ($filter === 'expiring') {
            $query->whereNotNull('expiry_date')
                  ->whereBetween('expiry_date', [now(), now()->addDays(30)]);
        }

        if ($supplier) {
            $query->where('supplier', $supplier);
        }

        $products = $query->orderBy('name')->get();

        $totalProducts = Product::count();
        $lowStockCount = Product::whereRaw('quantity > 0 AND quantity <= min_stock')->count();
        $outOfStockCount = Product::where('quantity', '<=', 0)->count();
        $totalStockValue = Product::sum(\DB::raw('quantity * purchase_price'));
        $expiringCount = Product::whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->count();

        $suppliers = Product::whereNotNull('supplier')->distinct()->orderBy('supplier')->pluck('supplier');

        return view('admin.products.stock-report', compact(
            'products', 'totalProducts', 'lowStockCount', 'outOfStockCount',
            'totalStockValue', 'expiringCount', 'suppliers', 'filter', 'supplier'
        ));
    }

    public function movementStore(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:in,out',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['type'] === 'in') {
            $product->addStock($data['quantity'], $data['notes']);
        } else {
            $movement = $product->removeStock($data['quantity'], $data['notes']);
            if (!$movement) {
                return redirect()->back()->with('error', 'Estoque insuficiente!');
            }
        }

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Movimentação registrada com sucesso!');
    }
}
