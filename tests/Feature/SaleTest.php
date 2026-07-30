<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SaleTest extends TestCase
{
    public function test_get_sales_list(): void
    {
        $this->withoutMiddleware();

        $response = $this->getJson('/api/sales');

        $response->assertOk();

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

        $response = $this->getJson('/api/sales/2');

        $response->assertOk();

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
                ],
            ],
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

    /* public function test_delete_sales(): void
    {
        $this->withoutMiddleware();

        $response = $this->deleteJson('/api/sales/1');

        $response->assertOk();

        $response->assertJson([
            'message' => 'Venta eliminada y Se restableció el stock.',
        ]);
    } */
    public function test_create_sale_without_products(): void
    {
        $this->withoutMiddleware();

        $user = User::find(1);

        $this->actingAs($user);

        $response = $this->postJson('/api/sales', [
            'items' => [],
        ]);

        $response->assertStatus(500);

        $response->assertJsonValidationErrors([
            'items',
        ]);
    }
    public function test_create_sale_with_zero_quantity(): void
    {
        $this->withoutMiddleware();

        $user = User::find(1);

        $this->actingAs($user);

        $response = $this->postJson('/api/sales', [
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 0,
                ],
            ],
        ]);

        $response->assertStatus(500);

        $response->assertJsonValidationErrors([
            'items.0.quantity',
        ]);
    }
    public function test_create_sale_with_non_existing_product(): void
    {
        $this->withoutMiddleware();

        $user = User::find(1);

        $this->actingAs($user);

        $response = $this->postJson('/api/sales', [
            'items' => [
                [
                    'product_id' => 999999,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(500);
    }
    public function test_create_sale_without_stock(): void
    {
        $this->withoutMiddleware();

        $user = User::find(1);

        $this->actingAs($user);

        $response = $this->postJson('/api/sales', [
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 999999,
                ],
            ],
        ]);

        $response->assertOk();

        $response->assertJsonStructure([
            'error',
        ]);
    }
}
