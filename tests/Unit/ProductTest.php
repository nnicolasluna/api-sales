<?php

namespace Tests\Unit;

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
}
