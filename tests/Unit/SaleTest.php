<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class SaleTest extends TestCase
{
    public function test_get_sales_list(): void
    {
        $this->withoutMiddleware();
        $response = $this->getJson('/api/sales');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_name',
                    'total',
                    'created_at',
                ],
            ],
        ]);
    }
    public function test_get_sales_detail(): void
    {
        $this->withoutMiddleware();
        $response = $this->getJson('/api/sales/1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'total',
                'created_at',
                'updated_at',
                'deleted_at',

                'sale_details' => [
                    '*' => [
                        'id',
                        'sale_id',
                        'product_id',
                        'quantity',
                        'price',
                        'subtotal',

                        'product' => [
                            'id',
                            'name',
                            'price',
                            'stock',
                            'image',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ],
        ]);
    }
    public function test_can_create_sale(): void
    {
        $this->withoutMiddleware();

        $user = User::find(1);
        $this->actingAs($user);
        $response = $this->postJson('/api/sales', [
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                ]
            ]
        ]);
        $response->assertCreated();

        $response->assertJsonStructure([
            'message',
            'sale_id',
            'total',
        ]);

        $response->assertJson([
            'message' => 'Venta creada correctamente',
        ]);
    }
    public function test_delete_sales(): void
    {
        $this->withoutMiddleware();
        $response = $this->deleteJson('/api/sales/1');
        $response->assertOk();
        $response->assertJson([
            'message' => 'Venta eliminada y Se restableció el stock.',
        ]);
    }
}
