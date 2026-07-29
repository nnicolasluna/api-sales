<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
class ProductController extends Controller
{
    //
    public function index()
    {
        $products = Product::select('id', 'name', 'price', 'stock', 'image')->get();

        return response()->json($products, 200);
    }
    public function show($id): JsonResponse
    {
        $product = Product::find($id);
        return response()->json([
            'data' => $product,
        ]);
    }
}
