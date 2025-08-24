<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductListing;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductListingController extends Controller
{

    public function index(): Response
    {
        $productListings = ProductListing::with(['product', 'seller'])->get();
        
        return Inertia::render('product-listings/Index', [
            'productListings' => $productListings,
        ]);
    }

    public function create(): Response
    {
        $products = Product::with('seller')->get();
        
        return Inertia::render('product-listings/Create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:product_listings,sku|max:255',
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        ProductListing::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'product_id' => $request->product_id,
            'seller_id' => $product->seller_id,
            'price' => $product->price,
        ]);

        return redirect()->route('product-listings.index')
            ->with('message', 'Product listing created successfully.');
    }

    public function edit(ProductListing $productListing): Response
    {
        $products = Product::with('seller')->get();
        
        return Inertia::render('product-listings/Create', [
            'productListing' => $productListing->load(['product', 'seller']),
            'products' => $products,
            'isEditing' => true,
        ]);
    }

    public function update(Request $request, ProductListing $productListing)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:product_listings,sku,' . $productListing->id . '|max:255',
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $productListing->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'product_id' => $request->product_id,
            'seller_id' => $product->seller_id,
            'price' => $product->price,
        ]);

        return redirect()->route('product-listings.index')
            ->with('message', 'Product listing updated successfully.');
    }

    public function destroy(ProductListing $productListing)
    {
        $productListing->delete();

        return redirect()->route('product-listings.index')
            ->with('message', 'Product listing deleted successfully.');
    }
}
