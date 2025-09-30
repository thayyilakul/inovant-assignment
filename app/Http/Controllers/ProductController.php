<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('images')->get();
        return view('product.index', compact('products'));
    }

    public function list()
    {
        $products = Product::with('images')->get();
        return view('product.list', compact('products'));
    }

    public function create()
    {
        return view('product.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'images' => 'required|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif'
            ]);

            $product = Product::create([
                'user_id' => 1,
                'name' => $request->name,
                'price' => $request->price
            ]);

            foreach ($request->images as $image) {
                $path = $image->store('products_images', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path
                ]);
            }

            $previousUrl = url()->previous();

            if($previousUrl == 'http://localhost:8000'){
                return response()->json(['message' => 'Product added successfully'], 201);
            }

            if ($previousUrl && !Str::startsWith($previousUrl, url('/api'))) {
                return redirect()->route('product-list')->with('success', 'Product created successfully!');
            }

            return response()->json(['message' => 'Product added successfully'], 201);
        } catch (Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());

            $previousUrl = url()->previous();

            if ($previousUrl && !Str::startsWith($previousUrl, url('/api'))) {
                return redirect()->back()->withInput()->with('error', 'Failed to create product.');
            }

            return response()->json([
                'error' => 'Failed to create product',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $product = Product::with('images')->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif'
            ]);

            $product = Product::findOrFail($id);

            $product->update($request->only(['name', 'price']));

            if ($request->has('images')) {
                $productImages = ProductImage::where('product_id', $product->id)->get();

                foreach ($productImages as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }

                foreach ($request->images as $image) {
                    $path = $image->store('products_images', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path
                    ]);
                }
            }

            $previousUrl = url()->previous();

            if($previousUrl == 'http://localhost:8000'){
                return response()->json(['message' => 'Product added successfully'], 201);
            }

            if ($previousUrl && !Str::startsWith($previousUrl, url('/api'))) {
                return redirect()->route('product-list')->with('success', 'Product updated successfully!');
            }

            return response()->json($product->load('images'));
        } catch (Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());

            $previousUrl = url()->previous();

            if ($previousUrl && !Str::startsWith($previousUrl, url('/api'))) {
                return redirect()->back()->withInput()->with('error', 'Failed to update product.');
            }

            return response()->json([
                'error' => 'Failed to update product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $product->images()->delete();

            $product->delete();

            $previousUrl = url()->previous();

            if($previousUrl == 'http://localhost:8000'){
                return response()->json(['message' => 'Product added successfully'], 201);
            }

            if ($previousUrl && !Str::startsWith($previousUrl, url('/api'))) {
                return redirect()->route('product-list')->with('success', 'Product deleted successfully!');
            }

            return response()->json(null, 204);
        } catch (Exception $e) {
            Log::error('Product deletion failed: ' . $e->getMessage());

            $previousUrl = url()->previous();

            if ($previousUrl && !Str::startsWith($previousUrl, url('/api'))) {
                return redirect()->back()->with('error', 'Failed to delete product.');
            }

            return response()->json([
                'error' => 'Failed to delete product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        return view('product.edit', compact('product'));
    }
}
