<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    private function authHeader(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_get_list_of_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'data' => [
                        '*' => ['id', 'sku', 'name', 'description', 'current_stock', 'created_at', 'updated_at']
                    ],
                    'current_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_can_search_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Laptop Dell']);
        Product::factory()->create(['name' => 'Mouse Logitech']);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/products?search=Laptop');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals('Laptop Dell', $data[0]['name']);
    }

    public function test_can_search_products_by_sku(): void
    {
        Product::factory()->create(['sku' => 'LAP-001-123', 'name' => 'Laptop']);
        Product::factory()->create(['sku' => 'MOU-002-456', 'name' => 'Mouse']);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/products?search=LAP-001');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals('LAP-001-123', $data[0]['sku']);
    }

    public function test_can_paginate_products(): void
    {
        Product::factory()->count(25)->create();

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/products?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonPath('data.total', 25);

        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_can_create_product_with_valid_data(): void
    {
        $productData = [
            'sku' => 'LAP-001-123',
            'name' => 'Laptop Dell XPS',
            'description' => 'High performance laptop',
            'current_stock' => 10,
        ];

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'sku', 'name', 'description', 'current_stock']
            ])
            ->assertJson([
                'message' => 'Product created successfully',
                'data' => $productData
            ]);

        $this->assertDatabaseHas('products', $productData);
    }

    public function test_cannot_create_product_with_duplicate_sku(): void
    {
        Product::factory()->create(['sku' => 'LAP-001-123']);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'LAP-001-123',
                'name' => 'Another Laptop',
                'current_stock' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_cannot_create_product_with_invalid_sku_format(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'invalid-sku',
                'name' => 'Laptop',
                'current_stock' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_cannot_create_product_with_negative_stock(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'LAP-001-123',
                'name' => 'Laptop',
                'current_stock' => -5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_stock']);
    }

    public function test_cannot_create_product_without_required_fields(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku', 'name', 'current_stock']);
    }

    public function test_can_get_single_product(): void
    {
        $product = Product::factory()->create([
            'sku' => 'LAP-001-123',
            'name' => 'Laptop Dell',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'sku', 'name', 'description', 'current_stock']
            ])
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'sku' => 'LAP-001-123',
                    'name' => 'Laptop Dell',
                ]
            ]);
    }

    public function test_returns_404_for_nonexistent_product(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/products/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'sku' => 'LAP-001-123',
            'name' => 'Old Name',
        ]);

        $updateData = [
            'sku' => 'LAP-001-456',
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product updated successfully',
                'data' => $updateData
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'LAP-001-456',
            'name' => 'Updated Name',
        ]);
    }

    public function test_cannot_update_product_with_duplicate_sku(): void
    {
        $product1 = Product::factory()->create(['sku' => 'LAP-001-123']);
        $product2 = Product::factory()->create(['sku' => 'LAP-002-456']);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/products/{$product2->id}", [
                'sku' => 'LAP-001-123',
                'name' => 'Updated Product',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_can_update_product_with_same_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'LAP-001-123']);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/products/{$product->id}", [
                'sku' => 'LAP-001-123',
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product deleted successfully',
            ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);

        $response = $this->postJson('/api/products', []);
        $response->assertStatus(401);

        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");
        $response->assertStatus(401);

        $response = $this->putJson("/api/products/{$product->id}", []);
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/products/{$product->id}");
        $response->assertStatus(401);
    }
}
