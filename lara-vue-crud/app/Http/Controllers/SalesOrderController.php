<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalesOrderController extends Controller
{
    public function index(): Response
    {
        $salesOrders = SalesOrder::with(['customer', 'admin'])->get();
        
        return Inertia::render('sales-orders/Index', [
            'salesOrders' => $salesOrders,
        ]);
    }

    public function create(): Response
    {
        $customers = Customer::all();
        $products = Product::all();
        
        return Inertia::render('sales-orders/Create', [
            'customers' => $customers,
            'products' => $products,
            'admin_id' => auth()->id(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|unique:sales_orders,order_id|max:255',
            'order_date' => 'required|date',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.units' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0',
            'items.*.taxes' => 'required|numeric|min:0',
        ]);

        // Calculate item totals
        $items = collect($request->items)->map(function ($item) {
            $total = ($item['units'] * $item['unit_price']) - $item['discount'] + $item['taxes'];
            return array_merge($item, ['total' => $total]);
        })->toArray();

        $salesOrder = SalesOrder::create([
            'order_id' => $request->order_id,
            'order_date' => $request->order_date,
            'status' => $request->status,
            'customer_id' => $request->customer_id,
            'admin_id' => auth()->id(),
            'items' => $items,
            'subtotal' => 0,
            'discount' => 0,
            'taxes' => 0,
            'grand_total' => 0,
        ]);

        // Calculate and update totals
        $salesOrder->calculateTotals();
        $salesOrder->save();

        return redirect()->route('sales-orders.index')
            ->with('message', 'Sales order created successfully.');
    }

    public function edit(SalesOrder $salesOrder): Response
    {
        $customers = Customer::all();
        $products = Product::all();
        
        return Inertia::render('sales-orders/Create', [
            'salesOrder' => $salesOrder->load(['customer', 'admin']),
            'customers' => $customers,
            'products' => $products,
            'isEditing' => true,
            'admin_id' => auth()->id(),
        ]);
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $request->validate([
            'order_id' => 'required|string|unique:sales_orders,order_id,' . $salesOrder->id . '|max:255',
            'order_date' => 'required|date',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.units' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'required|numeric|min:0',
            'items.*.taxes' => 'required|numeric|min:0',
        ]);

        // Calculate item totals
        $items = collect($request->items)->map(function ($item) {
            $total = ($item['units'] * $item['unit_price']) - $item['discount'] + $item['taxes'];
            return array_merge($item, ['total' => $total]);
        })->toArray();

        $salesOrder->update([
            'order_id' => $request->order_id,
            'order_date' => $request->order_date,
            'status' => $request->status,
            'customer_id' => $request->customer_id,
            'admin_id' => auth()->id(),
            'items' => $items,
            'subtotal' => 0,
            'discount' => 0,
            'taxes' => 0,
            'grand_total' => 0,
        ]);

        // Calculate and update totals
        $salesOrder->calculateTotals();
        $salesOrder->save();

        return redirect()->route('sales-orders.index')
            ->with('message', 'Sales order updated successfully.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        $salesOrder->delete();

        return redirect()->route('sales-orders.index')
            ->with('message', 'Sales order deleted successfully.');
    }
}
