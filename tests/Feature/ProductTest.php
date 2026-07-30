<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_get_products_list(): void
    {
        $this->withoutMiddleware();
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'price',
                'stock',
                'image',
            ],
        ]);
    }
    public function test_get_products_detail(): void
    {
        $this->withoutMiddleware();
        $response = $this->getJson('/api/products/1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'price',
                'stock',
                'image',
                'created_at',
                'updated_at',
            ],
        ]);
    }
    public function test_products_without_authentication(): void
    {
        $response = $this->getJson('/api/products');
        $response->assertUnauthorized();
    }
    public function test_products_with_authentication(): void
    {
        $user = User::find(1);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/products');
        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'price',
                'stock',
                'image',
            ],
        ]);
    }
    public function test_get_product_detail_without_authentication(): void
    {
        $response = $this->getJson('/api/products/1');

        $response->assertUnauthorized();
    }
    public function test_product_detail_with_authentication(): void
    {
        Sanctum::actingAs(
            User::find(1)
        );

        $response = $this->getJson('/api/products/1');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'price',
                'stock',
                'image',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
