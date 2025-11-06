<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Http\Requests\StoreProductsRequest;
use App\Http\Requests\UpdateProductsRequest;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::with('user:id,name,email')->get();
        return response()->json([
            'status' => 'success',
            'data' => $products
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductsRequest $request)
    {
        $validated = $request->validated();

        //validate user_id exists
        if(!isset($validated['user_id'])){
           $validated['user_id'] = $request->input('auth_user_id');
        }

        $product= Products::create($validated);

        //Load user relationship
        $product->load('user:name,email');
        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductsRequest $request, Products $products)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $products)
    {
        //
    }
}
