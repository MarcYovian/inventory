<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
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

    public function test_can_get_list_of_stock_movements(): void
    {
        $product = Product::factory()->create();

        StockMovement::factory()->count(5)->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/stock-movements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'product_id',
                            'user_id',
                            'type',
                            'quantity',
                            'notes',
                            'created_at',
                            'updated_at',
                            'product',
                            'user'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total'
                ]
            ]);
    }

    public function test_can_filter_stock_movements_by_type(): void
    {
        $product = Product::factory()->create();

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'type' => 'in',
        ]);

        StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'type' => 'out',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/stock-movements?type=in');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals('in', $data[0]['type']);
    }

    public function test_can_search_stock_movements_by_product_name(): void
    {
        $product1 = Product::factory()->create(['name' => 'Laptop Dell']);
        $product2 = Product::factory()->create(['name' => 'Mouse Logitech']);

        StockMovement::factory()->create([
            'product_id' => $product1->id,
            'user_id' => $this->user->id,
        ]);

        StockMovement::factory()->create([
            'product_id' => $product2->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/stock-movements?search=Laptop');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals('Laptop Dell', $data[0]['product']['name']);
    }

    public function test_can_add_stock_to_product(): void
    {
        $product = Product::factory()->create(['current_stock' => 10]);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 5,
                'notes' => 'Adding new stock',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'product',
                    'new_stock'
                ]
            ])
            ->assertJson([
                'message' => 'Stock added successfully',
                'data' => [
                    'new_stock' => 15
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 15,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 5,
        ]);
    }

    public function test_can_reduce_stock_from_product(): void
    {
        $product = Product::factory()->create(['current_stock' => 10]);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => 3,
                'notes' => 'Sold items',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'product',
                    'new_stock'
                ]
            ])
            ->assertJson([
                'message' => 'Stock reduced successfully',
                'data' => [
                    'new_stock' => 7
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 7,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'type' => 'out',
            'quantity' => 3,
        ]);
    }

    public function test_cannot_reduce_more_stock_than_available(): void
    {
        $product = Product::factory()->create(['current_stock' => 5]);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => 10,
                'notes' => 'Trying to reduce too much',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'current_stock' => 5,
        ]);
    }

    public function test_cannot_create_stock_movement_with_invalid_product(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => 99999,
                'type' => 'in',
                'quantity' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_cannot_create_stock_movement_with_invalid_type(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'invalid',
                'quantity' => 5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_cannot_create_stock_movement_with_zero_quantity(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_cannot_create_stock_movement_with_negative_quantity(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => -5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_can_create_stock_movement_without_notes(): void
    {
        $product = Product::factory()->create(['current_stock' => 10]);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 5,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 5,
            'notes' => null,
        ]);
    }

    public function test_can_get_single_stock_movement(): void
    {
        $product = Product::factory()->create();
        $movement = StockMovement::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 10,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/stock-movements/{$movement->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'product_id',
                    'user_id',
                    'type',
                    'quantity',
                    'product',
                    'user'
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $movement->id,
                    'type' => 'in',
                    'quantity' => 10,
                ]
            ]);
    }

    public function test_can_get_stock_movements_by_product(): void
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        StockMovement::factory()->count(3)->create([
            'product_id' => $product1->id,
            'user_id' => $this->user->id,
        ]);

        StockMovement::factory()->count(2)->create([
            'product_id' => $product2->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/products/{$product1->id}/stock-movements");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'product',
                    'movements' => [
                        'data',
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ]
            ]);

        $movements = $response->json('data.movements.data');
        $this->assertCount(3, $movements);
    }

    public function test_returns_404_for_stock_movements_of_nonexistent_product(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/products/99999/stock-movements');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_stock_movements(): void
    {
        $response = $this->getJson('/api/stock-movements');
        $response->assertStatus(401);

        $response = $this->postJson('/api/stock-movements', []);
        $response->assertStatus(401);

        $movement = StockMovement::factory()->create([
            'product_id' => Product::factory()->create()->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/stock-movements/{$movement->id}");
        $response->assertStatus(401);

        $product = Product::factory()->create();
        $response = $this->getJson("/api/products/{$product->id}/stock-movements");
        $response->assertStatus(401);
    }

    public function test_notes_can_exceed_1000_characters_in_api(): void
    {
        $product = Product::factory()->create(['current_stock' => 10]);
        $longNotes = str_repeat('a', 1001);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 5,
                'notes' => $longNotes,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    public function test_stock_movement_records_correct_user(): void
    {
        $product = Product::factory()->create(['current_stock' => 10]);

        $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 5,
            ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'quantity' => 5,
        ]);
    }
}
