<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class SaleController extends Controller
{
    public function store(StoreSaleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $saleDetailsData = [];
            $productIdList = [];
            $productList = [];

            $items = $request->input('items');
            foreach ($items as $item) {
                $productIdList[] = $item['product_id'];
            }
            $products = Product::whereIn('id', $productIdList)
                ->lockForUpdate()
                ->get();

            foreach ($products as $product) {
                $productList[$product->id] = $product;
            }
            foreach ($items as $item) {
                $product = $productList[$item['product_id']];

                $subtotalAmount = $product->price * $item['quantity'];
                $totalAmount = $totalAmount + $subtotalAmount;

                if ($product->stock < $item['quantity']) {
                    throw new Exception(
                        "No hay suficiente stock para {$product->name}."
                    );
                }
                $product->stock = $product->stock - $item['quantity'];
                $product->save();

                $saleDetailsData[] = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                    'subtotal'   => $subtotalAmount,
                ];
            }

            $sale = Sale::create([
                'user_id' => $request->user()->id,
                'total'   => $totalAmount,
            ]);

            foreach ($saleDetailsData as $key => $detail) {
                $saleDetailsData[$key]['sale_id'] = $sale->id;
            }

            SaleDetail::insert($saleDetailsData);

            DB::commit();

            return response()->json([
                'data'    => $sale->load('saleDetails.product'),
            ]);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'error'   => $e->getMessage(),
            ]);
        }
    }
    public function index(): JsonResponse
    {
        $sales = Sale::get();

        return response()->json([
            'data' => $sales,
        ]);
    }
    public function show($id): JsonResponse
    {
        $sale = Sale::with('saleDetails.product')->find($id);

        return response()->json([
            'data' => $sale,
        ]);
    }
}
