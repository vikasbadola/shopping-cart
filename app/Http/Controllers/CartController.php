<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\Product;
use App\Http\Controllers\ApiBaseController as ApiBaseController;
use Exception;

class CartController extends ApiBaseController {

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        if (\Auth::guard('api')->check()) {
            $request->request->add(['user_id' => auth('api')->user()->getKey()]);
        } else {
            $request->request->add(['user_id' => 0]); // Using 0 for guest users
        }
        $validator = Validator::make($request->all(), [
            'session_id' => 'required',
            'category' => 'required|string',
            'product_id' => 'required|numeric',
            'qty' => 'required|numeric',
        ]);
        if (!$request->request->has('order_id')) {
            $request->request->add(['order_id' => $this->generateOrderID('$param')]);
        }
        $request->request->add(['status' => 0]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', ['error' => $validator->errors()->all()], 400);
        }
        //Check if the proudct exist or return 404 not found.
        try {
            Product::findOrFail($request->product_id);
        } catch (Exception $e) {
            return $this->sendError('Product not found', 'The Product you\'re trying to add does not exist.', 404);
        }
        //check if the the same product is already in the Cart, if true update the quantity, if not create a new one.
        $cartItem = Cart::where(['order_id' => $request->order_id, 'product_id' => $request->product_id])->first();
        if ($cartItem) {
            $product = tap(\DB::table('carts')->where(['order_id' => $request->order_id, 'product_id' => $request->product_id]))
                    ->update(['qty' => ($cartItem->qty + $request->qty)])
                    ->first();
        } else {
            $product = Cart::create($request->toArray());
        }
        return $this->sendResponse($product, 'Product added to cart successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id) {
        if (\Auth::guard('api')->check()) {
            $userID = auth('api')->user()->getKey();
        }
        $sessionID = $id; // Session ID from the frontend
        $products = Cart::where(function ($query) use ($userID, $sessionID) {
            $query->where('user_id', $userID)
            ->orWhere('session_id', $sessionID);
        })->where('status', 0)->get();
        return $this->sendResponse($products, 'Cart retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', ['error' => $validator->errors()->all()], 400);
        }
        //Check if the order ID exist or return 404 not found.
        try {
            Cart::where('order_id', $id)->firstOrFail();
        } catch (Exception $e) {
            return $this->sendError('Order ID not found', 'The Order you\'re trying to edit does not exist.', 404);
        }
        //Check if the proudct exist or return 404 not found.
        try {
            Product::findOrFail($request->product_id);
        } catch (Exception $e) {
            return $this->sendError('Product not found', 'The Product you\'re trying to add does not exist.', 404);
        }
        $product = tap(\DB::table('carts')->where(['order_id' => $id, 'product_id' => $request->product_id]))
            ->update(['qty' => $request->qty])
            ->first();
        return $this->sendResponse($product, 'Cart updated successfully.');
    }

    /**
     * Remove the cart item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $product = Cart::find($id);
        if (!$product) {
            return $this->sendError('Cart item ID not found', 'The Product you\'re trying to delete does not exist in the cart.', 404);
        }
        if ($product->delete()) {
            return $this->sendResponse($product, 'Item deleted successfully.');
        } else {
            return $this->sendError('Item can not be deleted.', 'Item can not be deleted.', 404);
        }
    }

    /**
     * Generate new order ID
     * @param type $param
     * @return int
     */
    public function generateOrderID($param) {
        $orderId = Cart::max('order_id');
        if (empty($orderId)) {
            return $orderId = 1;
        } else {
            return $orderId + 1;
        }
    }

}
