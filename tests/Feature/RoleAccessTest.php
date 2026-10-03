<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * REQ 6, 7, 8 — pengujian otomatis untuk role, middleware, route protection, dan policy.
 * Jalankan: php artisan test --filter=RoleAccessTest
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite(); // view memakai @vite; test tidak perlu hasil build npm
    }

    public function test_guest_is_redirected_to_login(): void
    {
        foreach (['/dashboard', '/pesanan', '/kelola/products', '/admin/users'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_catalog_is_public(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_user_role_cannot_access_manage_or_admin_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/kelola/products')->assertForbidden();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }

    public function test_editor_can_manage_products_but_not_users(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/kelola/products')->assertOk();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
    }

    public function test_admin_can_access_everything(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/kelola/products')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    public function test_policy_editor_can_only_edit_and_delete_own_product(): void
    {
        $editorA = User::factory()->editor()->create();
        $editorB = User::factory()->editor()->create();
        $own = Product::factory()->create(['user_id' => $editorA->id]);
        $other = Product::factory()->create(['user_id' => $editorB->id]);

        $this->actingAs($editorA)->get(route('manage.products.edit', $own))->assertOk();
        $this->actingAs($editorA)->get(route('manage.products.edit', $other))->assertForbidden();
        $this->actingAs($editorA)->delete(route('manage.products.destroy', $other))->assertForbidden();
        $this->assertNotSoftDeleted($other);

        $this->actingAs($editorA)->delete(route('manage.products.destroy', $own))
            ->assertRedirect(route('manage.products.index'));
        $this->assertSoftDeleted($own);
    }

    public function test_policy_admin_can_edit_any_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $this->actingAs($admin)->get(route('manage.products.edit', $product))->assertOk();
    }

    public function test_editor_can_create_product_and_owner_is_set_from_session(): void
    {
        $editor = User::factory()->editor()->create();
        $category = Category::factory()->create();

        $this->actingAs($editor)->post(route('manage.products.store'), [
            'name' => 'Produk Uji', 'category_id' => $category->id,
            'price' => 15000, 'stock' => 5, 'is_active' => 1,
            'user_id' => 999, // percobaan memalsukan pemilik -> harus diabaikan
        ])->assertRedirect(route('manage.products.index'));

        $this->assertDatabaseHas('products', ['name' => 'Produk Uji', 'user_id' => $editor->id]);
    }

    public function test_product_validation_errors_are_reported_per_field(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->post(route('manage.products.store'), ['name' => 'ab', 'price' => 'abc'])
            ->assertSessionHasErrors(['name', 'category_id', 'price', 'stock']);
    }

    public function test_registration_always_creates_plain_user_even_if_role_is_injected(): void
    {
        $this->post('/register', [
            'name' => 'Penyusup', 'email' => 'penyusup@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'admin', // mass assignment attack
        ]);

        $this->assertDatabaseHas('users', ['email' => 'penyusup@example.com', 'role' => 'user']);
    }

    public function test_user_cannot_change_roles(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)->patch(route('admin.users.role', $target), ['role' => 'admin'])->assertForbidden();
        $this->assertSame('user', $target->fresh()->role);
    }

    public function test_admin_can_change_role_but_not_own_role(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.role', $target), ['role' => 'editor'])
            ->assertSessionHas('success');
        $this->assertSame('editor', $target->fresh()->role);

        $this->actingAs($admin)->patch(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_role_update_rejects_unknown_role(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.role', $target), ['role' => 'superuser'])
            ->assertSessionHasErrors('role');
    }

    public function test_order_is_only_visible_to_its_owner_or_admin(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $order = Order::create(['order_number' => 'INV-TEST0001', 'user_id' => $owner->id, 'status' => 'pending', 'total' => 0]);

        $this->actingAs($owner)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($admin)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($stranger)->get(route('orders.show', $order))->assertForbidden();
    }
}
