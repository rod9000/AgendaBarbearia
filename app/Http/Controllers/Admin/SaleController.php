<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('customer', 'user');

        if ($search = $request->get('search')) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $sales = $query->latest()->paginate(15);
        return view('admin.sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('quantity', '>', 0)->orderBy('name')->get();
        return view('admin.sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|in:dinheiro,pix,debito,credito',
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $items = collect($validated['items']);
        $subtotal = $items->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $discount = (float) ($validated['discount'] ?? 0);
        $total = max(0, $subtotal - $discount);

        $sale = Sale::create([
            'customer_id' => $validated['customer_id'],
            'total' => $total,
            'discount' => $discount,
            'payment_method' => $validated['payment_method'],
            'user_id' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->quantity < $item['quantity']) {
                return back()->withErrors("Estoque insuficiente para {$product->name} (disponível: {$product->quantity})")->withInput();
            }

            $sale->products()->attach($item['product_id'], [
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);

            $product->removeStock($item['quantity'], "Venda #{$sale->id} - {$sale->customer->name}");
        }

        return redirect()->route('admin.sales.show', $sale)
            ->with('success', 'Venda registrada com sucesso!');
    }

    public function show(Sale $sale)
    {
        $sale->load('customer', 'user', 'products');
        return view('admin.sales.show', compact('sale'));
    }
}
