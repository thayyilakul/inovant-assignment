<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class CartController extends Controller
{

    public function index()
    {
        $carts = Cart::with('user', 'product.images')->get();
        return view('cart', compact('carts'));
    }

    public function addToCart(Request $request)
    {
        try {
            $user_id = 1;

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $cartItem = Cart::where('user_id', $user_id)
                ->where('product_id', $request->product_id)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $request->quantity;
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id' => $user_id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity
                ]);
            }

            $previousUrl = url()->previous();

            if ($previousUrl && !Str::startsWith($previousUrl, url('/api'))) {
                return redirect()->route('cart-page')->with('success', 'Product added to cart successfully!');
            }

            return response()->json(['message' => 'Product added to cart successfully'], 201);
        } catch (Exception $e) {
            Log::error('Add to cart failed: ' . $e->getMessage());

            $previousUrl = url()->previous();

            if ($previousUrl && !Str::startsWith($previousUrl, url('/api'))) {
                return redirect()->back()->with('error', 'Failed to add product to cart.');
            }

            return response()->json([
                'error' => 'Failed to add product to cart',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function showCart()
    {
        try {
            $user_id = 1;

            $cartItems = Cart::where('user_id', $user_id)
                ->with('product')
                ->get();

            $cartData = $cartItems->map(function ($cartItem) {
                return [
                    'product_name' => $cartItem->product->name,
                    'price' => $cartItem->product->price,
                    'quantity' => $cartItem->quantity,
                    'total_price' => $cartItem->product->price * $cartItem->quantity
                ];
            });

            return response()->json([
                'cart_items' => $cartData,
                'cart_total' => $cartData->sum('total_price'),
                'item_count' => $cartData->count()
            ]);
        } catch (Exception $e) {
            Log::error('Cart retrieval failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to retrieve cart items',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteFromCart($cartId)
    {
        try {
            $user_id = 1;

            $cartItem = Cart::where('user_id', $user_id)
                ->where('id', $cartId)
                ->first();

            if (!$cartItem) {
                return response()->json(['message' => 'Cart item not found'], 404);
            }

            $cartItem->delete();

            return response()->json(['message' => 'Product removed from cart successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting cart item: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to remove item from cart',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function apiCartList()
    {
        try {
            $user_id = 1;

            $cartItems = Cart::where('user_id', $user_id)
                ->with(['product.images'])
                ->get();

            $cartTotal = 0;

            $cartData = $cartItems->map(function ($item) use (&$cartTotal) {
                $product = $item->product;

                $subtotal = $product->price * $item->quantity;
                $cartTotal += $subtotal;

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $subtotal,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image_path);
                    }),
                ];
            });

            return response()->json([
                'cart_items' => $cartData,
                'cart_total' => $cartTotal,
                'item_count' => $cartItems->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching cart items: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to fetch cart items',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
