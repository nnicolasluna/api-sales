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
                'message' => 'Venta creada correctamente',
                'sale_id' => $sale->id,
                'total'   => $sale->total,
            ], 201);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'error'   => $e->getMessage(),
            ]);
        }
    }
    public function index(): JsonResponse
    {
        $sales = Sale::with(['user', 'saleDetails.product'])
            ->latest()
            ->get();

        $data = [];

        foreach ($sales as $sale) {
            $data[] = [
                'id' => $sale->id,
                'user_name' => $sale->user ? $sale->user->name : null,
                'total' => $sale->total,
                'created_at' => $sale->created_at,
                'details' => $sale->saleDetails->map(function ($detail) {
                    return [
                        'product' => $detail->product ? $detail->product->name : null,
                        'quantity' => $detail->quantity,
                        'subtotal' => $detail->subtotal,
                    ];
                }),
            ];
        }

        return response()->json([
            'data' => $data,
        ]);
    }
    public function show($id): JsonResponse
    {
        $sale = Sale::with('saleDetails.product')->find($id);

        return response()->json([
            'data' => $sale,
        ]);
    }
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();
            $sale = Sale::with('saleDetails')->find($id);
            if (!$sale) {
                return response()->json([
                    'error' => 'La venta no existe.'
                ], 404);
            }
            foreach ($sale->saleDetails as $detail) {
                $product = Product::where('id', $detail->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($product) {
                    $product->stock += $detail->quantity;
                    $product->save();
                }
            }
            $sale->saleDetails()->delete();
            $sale->delete();

            DB::commit();

            return response()->json([
                'message' => 'Venta eliminada y Se restableció el stock.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Error al cancelar la venta: ' . $e->getMessage()
            ], 500);
        }
    }
}
