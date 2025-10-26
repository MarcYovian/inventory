<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationEdgeCasesTest extends TestCase
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

    // SKU Validation Tests
    public function test_sku_must_match_pattern(): void
    {
        $invalidSkus = [
            'abc',
            'ABC-123',
            'ABC-DEF-GHI',
            'abc-def-123',
            'ABC--123',
            'ABC-DEF-',
            '-ABC-DEF-123',
            'ABCDE-FGHIJ-12345', // Too long segments
        ];

        foreach ($invalidSkus as $sku) {
            $response = $this->withHeaders($this->authHeader())
                ->postJson('/api/products', [
                    'sku' => $sku,
                    'name' => 'Test Product',
                    'current_stock' => 10,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        }
    }

    public function test_register_email_format_validation(): void
    {
        $invalidEmails = [
            'notanemail',
            'missing@domain',
            '@nodomain.com',
            'spaces in@email.com',
            'double@@email.com',
        ];

        foreach ($invalidEmails as $email) {
            $response = $this->postJson('/api/register', [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        }
    }

    public function test_register_name_max_255_characters(): void
    {
        // Valid: 255 characters
        $name255 = str_repeat('a', 255);
        $response = $this->postJson('/api/register', [
            'name' => $name255,
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        // Invalid: 256 characters
        $name256 = str_repeat('a', 256);
        $response = $this->postJson('/api/register', [
            'name' => $name256,
            'email' => 'test2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // Edge Case: Empty Strings
    public function test_empty_strings_are_validated(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => '',
                'name' => '',
                'current_stock' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku', 'name', 'current_stock']);
    }

    // Edge Case: Whitespace Only
    public function test_whitespace_only_values_are_validated(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => '   ',
                'name' => '   ',
                'current_stock' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku', 'name']);
    }

    // Edge Case: SQL Injection Attempts
    public function test_sql_injection_attempts_are_handled(): void
    {
        $sqlInjectionStrings = [
            "'; DROP TABLE products; --",
            "1' OR '1'='1",
            "admin'--",
            "' OR 1=1--",
        ];

        foreach ($sqlInjectionStrings as $index => $malicious) {
            $response = $this->withHeaders($this->authHeader())
                ->postJson('/api/products', [
                    'sku' => "ABC-DEF-{$index}",
                    'name' => $malicious,
                    'current_stock' => 10,
                ]);

            // Should create successfully but sanitized
            $response->assertStatus(201);
        }

        // Verify table still exists
        $this->assertDatabaseCount('products', count($sqlInjectionStrings));
    }

    // Edge Case: XSS Attempts
    public function test_xss_attempts_are_stored_safely(): void
    {
        $xssStrings = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            'javascript:alert("XSS")',
        ];

        foreach ($xssStrings as $index => $xss) {
            $response = $this->withHeaders($this->authHeader())
                ->postJson('/api/products', [
                    'sku' => "ABC-DEF-{$index}",
                    'name' => $xss,
                    'description' => $xss,
                    'current_stock' => 10,
                ]);

            $response->assertStatus(201);

            // Verify data stored as-is (will be escaped on output)
            $this->assertDatabaseHas('products', [
                'name' => $xss,
            ]);
        }
    }

    // Edge Case: Unicode Characters
    public function test_unicode_characters_in_fields(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-123',
                'name' => '产品名称 🚀',
                'description' => 'Описание продукта ñ é ü',
                'current_stock' => 10,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => '产品名称 🚀',
        ]);
    }

    // Edge Case: Very Long Request
    public function test_handles_maximum_payload_sizes(): void
    {
        $product = Product::factory()->create(['current_stock' => 100]);

        // Create movement with max-length notes
        $maxNotes = str_repeat('a', 1000);

        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 999999,
                'notes' => $maxNotes,
            ]);

        $response->assertStatus(201);
    }

    public function test_valid_sku_patterns(): void
    {
        $validSkus = [
            'A-B-1',
            'AB-CD-12',
            'ABC-DEF-123',
            'ABCD-EFGH-1234',
            'A1B2-C3D4-12345',
        ];

        foreach ($validSkus as $index => $sku) {
            $response = $this->withHeaders($this->authHeader())
                ->postJson('/api/products', [
                    'sku' => $sku,
                    'name' => "Test Product {$index}",
                    'current_stock' => 10,
                ]);

            $response->assertStatus(201);
        }
    }

    public function test_sku_max_length_15_characters(): void
    {
        // Valid: exactly 15 characters
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABCD-EFGH-12345', // 15 characters
                'name' => 'Test Product',
                'current_stock' => 10,
            ]);

        $response->assertStatus(201);

        // Invalid: 16 characters
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABCD-EFGH-123456', // 16 characters
                'name' => 'Test Product 2',
                'current_stock' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_sku_case_sensitivity(): void
    {
        Product::factory()->create(['sku' => 'ABC-DEF-123']);

        // Try to create with different case (should fail if unique)
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'abc-def-123',
                'name' => 'Test Product',
                'current_stock' => 10,
            ]);

        // Since regex requires uppercase, this should fail validation
        $response->assertStatus(422);
    }

    // Product Name Validation Tests
    public function test_product_name_max_255_characters(): void
    {
        // Valid: 255 characters
        $name255 = str_repeat('a', 255);
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-123',
                'name' => $name255,
                'current_stock' => 10,
            ]);

        $response->assertStatus(201);

        // Invalid: 256 characters
        $name256 = str_repeat('a', 256);
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-124',
                'name' => $name256,
                'current_stock' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_product_name_with_special_characters(): void
    {
        $specialNames = [
            'Product™ with trademark',
            'Product © with copyright',
            'Product & Accessories',
            'Product (Version 2.0)',
            'Product - Model X',
            'Product / Service',
            'Produit français',
            'Producto español',
            '产品名称',
            'مُنتَج',
        ];

        foreach ($specialNames as $index => $name) {
            $response = $this->withHeaders($this->authHeader())
                ->postJson('/api/products', [
                    'sku' => "ABC-DEF-{$index}",
                    'name' => $name,
                    'current_stock' => 10,
                ]);

            $response->assertStatus(201);
        }
    }

    // Description Validation Tests
    public function test_description_max_5000_characters(): void
    {
        // Valid: 5000 characters
        $desc5000 = str_repeat('a', 5000);
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-123',
                'name' => 'Test Product',
                'description' => $desc5000,
                'current_stock' => 10,
            ]);

        $response->assertStatus(201);

        // Invalid: 5001 characters
        $desc5001 = str_repeat('a', 5001);
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-124',
                'name' => 'Test Product 2',
                'description' => $desc5001,
                'current_stock' => 10,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_description_is_nullable(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-123',
                'name' => 'Test Product',
                'current_stock' => 10,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'sku' => 'ABC-DEF-123',
            'description' => null,
        ]);
    }

    // Stock Validation Tests
    public function test_current_stock_boundary_values(): void
    {
        // Valid: 0
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-123',
                'name' => 'Test Product',
                'current_stock' => 0,
            ]);

        $response->assertStatus(201);

        // Valid: Large number
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-124',
                'name' => 'Test Product 2',
                'current_stock' => 999999999,
            ]);

        $response->assertStatus(201);

        // Invalid: Negative
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/products', [
                'sku' => 'ABC-DEF-125',
                'name' => 'Test Product 3',
                'current_stock' => -1,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_stock']);
    }

    public function test_current_stock_must_be_integer(): void
    {
        $invalidStocks = [
            'ten',
            '10.5',
            10.5,
            'abc',
            null,
        ];

        foreach ($invalidStocks as $index => $stock) {
            $response = $this->withHeaders($this->authHeader())
                ->postJson('/api/products', [
                    'sku' => "ABC-DEF-{$index}",
                    'name' => 'Test Product',
                    'current_stock' => $stock,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_stock']);
        }
    }

    // Stock Movement Validation Tests
    public function test_stock_movement_quantity_boundary_values(): void
    {
        $product = Product::factory()->create(['current_stock' => 100]);

        // Valid: 1 (minimum)
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 1,
            ]);

        $response->assertStatus(201);

        // Valid: Large number
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 999999,
            ]);

        $response->assertStatus(201);

        // Invalid: 0
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        // Invalid: Negative
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => -5,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_stock_movement_notes_max_1000_characters(): void
    {
        $product = Product::factory()->create(['current_stock' => 100]);

        // Valid: 1000 characters
        $notes1000 = str_repeat('a', 1000);
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 5,
                'notes' => $notes1000,
            ]);

        $response->assertStatus(201);

        // Invalid: 1001 characters
        $notes1001 = str_repeat('a', 1001);
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/stock-movements', [
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => 5,
                'notes' => $notes1001,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    public function test_stock_movement_type_must_be_in_or_out(): void
    {
        $product = Product::factory()->create(['current_stock' => 100]);

        $invalidTypes = ['IN', 'OUT', 'add', 'remove', 'stock', 'adjustment', ''];

        foreach ($invalidTypes as $type) {
            $response = $this->withHeaders($this->authHeader())
                ->postJson('/api/stock-movements', [
                    'product_id' => $product->id,
                    'type' => $type,
                    'quantity' => 5,
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
        }
    }

    // Update Validation Tests
    public function test_update_product_allows_same_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'ABC-DEF-123']);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/products/{$product->id}", [
                'sku' => 'ABC-DEF-123', // Same SKU
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
    }

    public function test_update_product_prevents_duplicate_sku(): void
    {
        $product1 = Product::factory()->create(['sku' => 'ABC-DEF-123']);
        $product2 = Product::factory()->create(['sku' => 'ABC-DEF-456']);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/products/{$product2->id}", [
                'sku' => 'ABC-DEF-123', // Duplicate
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    // Register Validation Tests
    public function test_register_password_confirmation_mismatch(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
