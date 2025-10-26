<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTest extends TestCase
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

    public function test_complete_product_lifecycle(): void
    {
        // 1. Create product
        $createResponse = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'LAP-001-123',
                'name' => 'Laptop Dell XPS',
                'description' => 'High performance laptop',
                'current_stock' => 10,
            ]);

        $createResponse->assertStatus(201);
        $productId = $createResponse->json('data.id');

        // 2. Get product
        $getResponse = $this->withHeaders($this->authHeader())
            ->getJson("/api/products/{$productId}");

        $getResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'sku' => 'LAP-001-123',
                    'current_stock' => 10,
                ]
            ]);

        // 3. Update product
        $updateResponse = $this->withHeaders($this->authHeader())
            ->putJson("/api/products/{$productId}", [
                'sku' => 'LAP-001-456',
                'name' => 'Updated Laptop Dell XPS',
                'description' => 'Updated description',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'sku' => 'LAP-001-456',
                    'name' => 'Updated Laptop Dell XPS',
                ]
            ]);

        // 4. Add stock
        $addStockResponse = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $productId,
                'type' => 'in',
                'quantity' => 5,
                'notes' => 'New shipment arrived',
            ]);

        $addStockResponse->assertStatus(201)
            ->assertJson([
                'data' => [
                    'new_stock' => 15,
                ]
            ]);

        // 5. Reduce stock
        $reduceStockResponse = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $productId,
                'type' => 'out',
                'quantity' => 3,
                'notes' => 'Sold 3 units',
            ]);

        $reduceStockResponse->assertStatus(201)
            ->assertJson([
                'data' => [
                    'new_stock' => 12,
                ]
            ]);

        // 6. Get stock movements for product
        $movementsResponse = $this->withHeaders($this->authHeader())
            ->getJson("/api/products/{$productId}/stock-movements");

        $movementsResponse->assertStatus(200);
        $movements = $movementsResponse->json('data.movements.data');
        $this->assertCount(2, $movements);

        // 7. Verify final stock
        $finalResponse = $this->withHeaders($this->authHeader())
            ->getJson("/api/products/{$productId}");

        $finalResponse->assertJson([
            'data' => [
                'current_stock' => 12,
            ]
        ]);

        // 8. Delete product
        $deleteResponse = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/products/{$productId}");

        $deleteResponse->assertStatus(200);

        // 9. Verify deletion
        $verifyResponse = $this->withHeaders($this->authHeader())
            ->getJson("/api/products/{$productId}");

        $verifyResponse->assertStatus(404);
    }

    public function test_multiple_users_can_manage_stock_independently(): void
    {
        $user1 = User::factory()->create(['name' => 'User One']);
        $token1 = $user1->createToken('test-token')->plainTextToken;

        $user2 = User::factory()->create(['name' => 'User Two']);
        $token2 = $user2->createToken('test-token')->plainTextToken;

        $product = Product::factory()->create([
            'sku' => 'LAP-001-123',
            'name' => 'Laptop Dell XPS',
            'description' => 'High performance laptop',
            'current_stock' => 100
        ]);

        // User 1 adds stock
        $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 10,
                'notes' => 'User 1 adding stock',
            ])
            ->assertStatus(201);

        // User 2 reduces stock
        $this->withHeader('Authorization', "Bearer {$token2}")
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => 5,
                'notes' => 'User 2 reducing stock',
            ])
            ->assertStatus(201);

        // Verify both movements recorded with correct users
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $user1->id,
            'type' => 'in',
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $user2->id,
            'type' => 'out',
            'quantity' => 5,
        ]);

        // Verify final stock
        $product->refresh();
        $this->assertEquals(105, $product->current_stock);
    }

    public function test_concurrent_stock_movements_maintain_consistency(): void
    {
        $product = Product::factory()->create([
            'sku' => 'LAP-001-123',
            'name' => 'Laptop Dell XPS',
            'description' => 'High performance laptop',
            'current_stock' => 50
        ]);

        // Simulate multiple concurrent stock reductions
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->withHeaders($this->authHeader())
                ->postJson('/api/stock-movements', [
                    'product_id' => $product->id,
                    'type' => 'out',
                    'quantity' => 5,
                    'notes' => "Transaction {$i}",
                ]);
        }

        // All should succeed
        foreach ($responses as $response) {
            $response->assertStatus(201);
        }

        // Verify final stock
        $product->refresh();
        $this->assertEquals(25, $product->current_stock);

        // Verify all movements recorded
        $this->assertDatabaseCount('stock_movements', 5);
    }

    public function test_search_and_filter_workflow(): void
    {
        // Create products
        $laptop = Product::factory()->create([
            'sku' => 'LAP-001-123',
            'name' => 'Laptop Dell',
            'current_stock' => 10
        ]);

        $mouse = Product::factory()->create([
            'sku' => 'MOU-002-456',
            'name' => 'Mouse Logitech',
            'current_stock' => 50
        ]);

        // Create stock movements
        StockMovement::factory()->create([
            'product_id' => $laptop->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 5,
        ]);

        StockMovement::factory()->create([
            'product_id' => $mouse->id,
            'user_id' => $this->user->id,
            'type' => 'out',
            'quantity' => 10,
        ]);

        // Search products by name
        $searchResponse = $this->withHeaders($this->authHeader())
            ->getJson('/api/products?search=Laptop');

        $searchResponse->assertStatus(200);
        $this->assertCount(1, $searchResponse->json('data.data'));

        // Search products by SKU
        $skuResponse = $this->withHeaders($this->authHeader())
            ->getJson('/api/products?search=MOU-002');

        $skuResponse->assertStatus(200);
        $this->assertCount(1, $skuResponse->json('data.data'));

        // Filter stock movements by type
        $filterResponse = $this->withHeaders($this->authHeader())
            ->getJson('/api/stock-movements?type=in');

        $filterResponse->assertStatus(200);
        $movements = $filterResponse->json('data.data');
        $this->assertCount(1, $movements);
        $this->assertEquals('in', $movements[0]['type']);

        // Search stock movements by product
        $movementSearchResponse = $this->withHeaders($this->authHeader())
            ->getJson('/api/stock-movements?search=Laptop');

        $movementSearchResponse->assertStatus(200);
        $this->assertCount(1, $movementSearchResponse->json('data.data'));
    }

    public function test_pagination_across_all_endpoints(): void
    {
        // Create multiple products
        Product::factory()->count(25)->create();

        // Test product pagination
        $page1 = $this->withHeaders($this->authHeader())
            ->getJson('/api/products?per_page=10&page=1');

        $page1->assertStatus(200)
            ->assertJsonPath('data.per_page', 10)
            ->assertJsonPath('data.current_page', 1);

        $this->assertCount(10, $page1->json('data.data'));

        $page2 = $this->withHeaders($this->authHeader())
            ->getJson('/api/products?per_page=10&page=2');

        $page2->assertStatus(200)
            ->assertJsonPath('data.current_page', 2);

        // Create stock movements
        $product = Product::factory()->create();
        for ($i = 0; $i < 20; $i++) {
            StockMovement::factory()->create([
                'product_id' => $product->id,
                'user_id' => $this->user->id,
            ]);
        }

        // Test stock movement pagination
        $movementPage1 = $this->withHeaders($this->authHeader())
            ->getJson('/api/stock-movements?per_page=5&page=1');

        $movementPage1->assertStatus(200)
            ->assertJsonPath('data.per_page', 5);

        $this->assertCount(5, $movementPage1->json('data.data'));
    }

    public function test_error_handling_workflow(): void
    {
        // Try to create product with invalid data
        $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'invalid',
                'name' => '',
                'current_stock' => -5,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sku', 'name', 'current_stock']);

        // Create valid product
        $product = Product::factory()->create(['current_stock' => 5]);

        // Try to reduce more stock than available
        $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => 10,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        // Verify stock unchanged
        $product->refresh();
        $this->assertEquals(5, $product->current_stock);

        // Try to access non-existent product
        $this->withHeaders($this->authHeader())
            ->getJson('/api/products/99999')
            ->assertStatus(404);

        // Try to update non-existent product
        $this->withHeaders($this->authHeader())
            ->putJson('/api/products/99999', [
                'sku' => 'LAP-001-123',
                'name' => 'Test',
            ])
            ->assertStatus(404);
    }

    public function test_authentication_workflow(): void
    {
        // Register new user
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertStatus(201);
        $token = $registerResponse->json('data.token');

        // Use token to get user data
        $userResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user');

        $userResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]
            ]);

        // Logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200);

        // Try to use token after logout (should fail)
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user')
            ->assertStatus(401);

        // Login again
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['token']
            ]);
    }

    public function test_stock_movement_history_tracking(): void
    {
        $product = Product::factory()->create(['current_stock' => 100]);

        // Perform multiple operations
        $operations = [
            ['type' => 'in', 'quantity' => 10, 'notes' => 'Restock'],
            ['type' => 'out', 'quantity' => 5, 'notes' => 'Sale'],
            ['type' => 'in', 'quantity' => 20, 'notes' => 'New shipment'],
            ['type' => 'out', 'quantity' => 15, 'notes' => 'Bulk order'],
        ];

        foreach ($operations as $operation) {
            $this->withHeaders($this->authHeader())
                ->postJson('/api/stock-movements', [
                    'product_id' => $product->id,
                    'type' => $operation['type'],
                    'quantity' => $operation['quantity'],
                    'notes' => $operation['notes'],
                ])
                ->assertStatus(201);
        }

        // Get all movements for product
        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/products/{$product->id}/stock-movements");

        $response->assertStatus(200);
        $movements = $response->json('data.movements.data');

        // Verify all operations recorded
        $this->assertCount(4, $movements);

        // Verify final stock calculation
        // 100 + 10 - 5 + 20 - 15 = 110
        $product->refresh();
        $this->assertEquals(110, $product->current_stock);
    }
}
