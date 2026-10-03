<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur beli langsung. Jalankan: php artisan test --filter=CheckoutTest
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function newAddress(): array
    {
        return [
            'recipient'   => 'Budi Santoso',
            'phone'       => '081234567890',
            'street'      => 'Jl. Merdeka No. 10',
            'city'        => 'Medan',
            'province'    => 'Sumatera Utara',
            'postal_code' => '20111',
        ];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $product = Product::factory()->create();

        $this->get(route('checkout.create', $product))->assertRedirect('/login');
        $this->post(route('checkout.store', $product), [])->assertRedirect('/login');
    }

    public function test_checkout_page_opens_for_logged_in_user(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs(User::factory()->create())
            ->get(route('checkout.create', $product))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_user_can_buy_with_new_address(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 10]);

        $response = $this->actingAs($user)->post(route('checkout.store', $product), $this->newAddress() + [
            'quantity'       => 3,
            'payment_method' => 'qris',
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('pending', $order->status);
        $this->assertEquals(300000, (float) $order->total);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'method' => 'qris', 'status' => 'unpaid']);
        $this->assertDatabaseHas('addresses', ['user_id' => $user->id, 'recipient' => 'Budi Santoso', 'is_default' => true]);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_user_can_buy_with_saved_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create();
        $product = Product::factory()->create(['stock' => 4]);

        $this->actingAs($user)->post(route('checkout.store', $product), [
            'quantity'       => 1,
            'address_id'     => $address->id,
            'payment_method' => 'cod',
        ])->assertRedirect();

        $this->assertSame(1, $user->addresses()->count()); // tidak membuat alamat baru
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'address_id' => $address->id]);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_cannot_buy_more_than_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->actingAs(User::factory()->create())
            ->post(route('checkout.store', $product), $this->newAddress() + [
                'quantity'       => 5,
                'payment_method' => 'qris',
            ])->assertSessionHasErrors('quantity');

        $this->assertSame(0, Order::count());
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_cannot_use_another_users_address(): void
    {
        $other = Address::factory()->for(User::factory())->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs(User::factory()->create())
            ->post(route('checkout.store', $product), [
                'quantity'       => 1,
                'address_id'     => $other->id,
                'payment_method' => 'qris',
            ])->assertSessionHasErrors('address_id');

        $this->assertSame(0, Order::count());
    }

    public function test_new_address_fields_are_required_without_saved_address(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs(User::factory()->create())
            ->post(route('checkout.store', $product), ['quantity' => 1, 'payment_method' => 'qris'])
            ->assertSessionHasErrors(['recipient', 'phone', 'street', 'city', 'province', 'postal_code']);
    }

    public function test_inactive_product_cannot_be_bought(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('checkout.create', $product))
            ->assertNotFound();
    }
}
