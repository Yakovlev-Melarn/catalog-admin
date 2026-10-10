<?php

namespace Tests\Feature;

use App\DTO\AttributeDTO;
use App\Models\Attribute;
use App\Models\User;
use App\Services\AttributeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttributeCrudTest extends TestCase
{
    use RefreshDatabase;

    private AttributeService $service;

    private User $admin;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->service = new AttributeService;

        $this->admin = User::factory()->create();
        $this->manager = User::factory()->create();

        $this->admin->syncRoles(Role::findOrCreate('admin'));
        $this->manager->syncRoles(Role::findOrCreate('manager'));
    }

    public function test_create_text_attribute(): void
    {
        $attribute = $this->service->create(new AttributeDTO(name: 'Вес', type: 'number', isRequired: true));

        $this->assertSame('Вес', $attribute->fresh()->name);
        $this->assertSame('number', $attribute->fresh()->type);
        $this->assertTrue($attribute->fresh()->is_required);
    }

    public function test_create_select_attribute_with_values(): void
    {
        $attribute = $this->service->create(new AttributeDTO(name: 'Цвета', type: 'select', values: ['Красный', 'Синий']));

        $this->assertSame(['Красный', 'Синий'], $attribute->fresh()->values);
    }

    public function test_update_attribute(): void
    {
        $attribute = Attribute::query()->where('name', 'Бренд')->firstOrFail();

        $updated = $this->service->update($attribute, new AttributeDTO(name: 'Производитель', type: 'text'));

        $this->assertSame('Производитель', $updated->fresh()->name);
    }

    /**
     * @throws ValidationException
     */
    public function test_delete_unused_attribute(): void
    {
        $attribute = $this->service->create(new AttributeDTO(name: 'Не используемая'));

        $this->service->delete($attribute);

        $this->assertNull(Attribute::query()->find($attribute->id));
    }

    public function test_cannot_delete_attribute_used_in_products(): void
    {
        $attribute = Attribute::query()->where('name', 'Бренд')->firstOrFail();
        $this->assertNotCount(0, $attribute->products()->get());

        try {
            $this->service->delete($attribute);
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('attribute', $e->errors());
        }

        $this->assertNotNull($attribute->fresh());
    }

    public function test_admin_has_full_access_manager_only_read(): void
    {
        $attribute = Attribute::query()->firstOrFail();

        $this->assertTrue($this->admin->can('create', Attribute::class));
        $this->assertTrue($this->admin->can('update', $attribute));
        $this->assertTrue($this->admin->can('delete', $attribute));

        $this->assertFalse($this->manager->can('view', $attribute));
        $this->assertFalse($this->manager->can('create', Attribute::class));
        $this->assertFalse($this->manager->can('update', $attribute));
        $this->assertFalse($this->manager->can('delete', $attribute));
    }
}
