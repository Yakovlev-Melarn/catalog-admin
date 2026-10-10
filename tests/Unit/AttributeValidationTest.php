<?php

namespace Tests\Unit;

use App\DTO\AttributeDTO;
use App\Models\Attribute;
use App\Services\AttributeService;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AttributeValidationTest extends TestCase
{
    use RefreshDatabase;

    private AttributeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->service = new AttributeService;
    }

    public function test_name_is_required(): void
    {
        $this->expectValidationError('name', fn () => $this->service->create(new AttributeDTO(name: '')));
    }

    public function test_type_must_be_known(): void
    {
        $this->expectValidationError('type', fn () => $this->service->create(new AttributeDTO(name: 'Х1', type: 'varchar')));
    }

    public function test_select_attribute_requires_values(): void
    {
        $this->expectValidationError('values', fn () => $this->service->create(new AttributeDTO(name: 'Х2', type: 'select')));
    }

    public function test_name_must_be_unique(): void
    {
        $this->expectValidationError('name', fn () => $this->service->create(new AttributeDTO(name: 'Бренд')));
    }

    public function test_update_keeps_own_name_unique(): void
    {
        $attribute = $this->service->getById(Attribute::query()->where('name', 'Бренд')->firstOrFail()->id);

        $updated = $this->service->update($attribute, new AttributeDTO(name: 'Бренд', type: 'text'));

        $this->assertSame('Бренд', $updated->fresh()->name);
    }

    public function test_valid_attribute_passes(): void
    {
        $attribute = $this->service->create(new AttributeDTO(
            name: 'Объём',
            type: 'select',
            isRequired: true,
            values: ['16 ГБ', '64 ГБ', '128 ГБ'],
        ));

        $this->assertSame('Объём', $attribute->fresh()->name);
        $this->assertSame(['16 ГБ', '64 ГБ', '128 ГБ'], $attribute->fresh()->values);
        $this->assertTrue($attribute->fresh()->is_required);
    }

    private function expectValidationError(string $key, Closure $action): void
    {
        try {
            $action();
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($key, $e->errors());
        }
    }
}
