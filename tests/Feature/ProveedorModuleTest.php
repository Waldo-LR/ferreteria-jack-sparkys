<?php

namespace Tests\Feature;

use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProveedorModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $usuario = new Usuario;
        $usuario->setAttribute('id_usuario', 1);
        $usuario->setAttribute('id_rol', 1);
        $usuario->setAttribute('nombre', 'Administrador de prueba');
        $usuario->setRelation('rol', new Rol(['nombre' => 'Administrador']));

        $this->actingAs($usuario)->withoutVite();
    }

    public function test_provider_page_lists_and_filters_active_providers(): void
    {
        Proveedor::create($this->providerData());
        Proveedor::create($this->providerData([
            'razon_social' => 'Comercial Ferretera',
            'ruc' => '20123456789',
            'telefono' => '014567890',
            'email' => 'ventas@comercial.pe',
        ]));

        $response = $this->get(route('proveedores.index', ['buscar' => 'Truper']));

        $response->assertOk()
            ->assertSee('Importaciones Truper')
            ->assertViewHas('proveedores', function ($proveedores) {
                return $proveedores->count() === 1
                    && $proveedores->first()->razon_social === 'Importaciones Truper';
            })
            ->assertSee('Registrar proveedor');
    }

    public function test_provider_can_be_created_with_an_audit_record(): void
    {
        $response = $this->post(route('proveedores.store'), $this->providerData());

        $response->assertRedirect(route('proveedores.index'));

        $this->assertDatabaseHas('tproveedores', [
            'razon_social' => 'Importaciones Truper',
            'ruc' => '20512345678',
            'activo' => 1,
        ]);
        $this->assertDatabaseHas('tauditoria', [
            'accion' => 'PROVEEDOR_CREADO',
            'tabla_afectada' => 'tproveedores',
            'id_registro' => 1,
        ]);
    }

    public function test_provider_can_be_edited_without_changing_its_ruc(): void
    {
        $proveedor = Proveedor::create($this->providerData());
        $response = $this->put(
            route('proveedores.update', $proveedor->id_proveedor),
            $this->providerData(['razon_social' => 'Importaciones Truper Perú'])
        );

        $response->assertRedirect(route('proveedores.index'));
        $this->assertDatabaseHas('tproveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'razon_social' => 'Importaciones Truper Perú',
            'ruc' => '20512345678',
        ]);
        $this->assertDatabaseHas('tauditoria', [
            'accion' => 'PROVEEDOR_ACTUALIZADO',
            'id_registro' => $proveedor->id_proveedor,
        ]);
    }

    public function test_provider_can_be_deactivated_without_deleting_its_record(): void
    {
        $proveedor = Proveedor::create($this->providerData());

        $response = $this->delete(route('proveedores.destroy', $proveedor->id_proveedor));

        $response->assertRedirect(route('proveedores.index'));
        $this->assertDatabaseHas('tproveedores', [
            'id_proveedor' => $proveedor->id_proveedor,
            'activo' => 0,
        ]);
        $this->assertDatabaseHas('tauditoria', [
            'accion' => 'PROVEEDOR_ELIMINADO',
            'id_registro' => $proveedor->id_proveedor,
        ]);
        $this->get(route('proveedores.index'))->assertDontSee('Importaciones Truper');
    }

    public function test_provider_validation_rejects_duplicate_ruc_and_invalid_email(): void
    {
        Proveedor::create($this->providerData());

        $response = $this->from(route('proveedores.index'))->post(route('proveedores.store'), $this->providerData([
            'email' => 'correo-invalido',
        ]));

        $response->assertRedirect(route('proveedores.index'))
            ->assertSessionHasErrors(['ruc', 'email']);
        $this->assertDatabaseCount('tproveedores', 1);
    }

    public function test_provider_api_validates_and_deactivates_records(): void
    {
        $created = $this->postJson('/api/proveedores', $this->providerData())
            ->assertCreated()
            ->assertJsonPath('data.activo', 1);

        $id = $created->json('data.id_proveedor');

        $this->deleteJson("/api/proveedores/{$id}")
            ->assertOk()
            ->assertJsonPath('message', 'Registro dado de baja correctamente.');

        $this->assertDatabaseHas('tproveedores', [
            'id_proveedor' => $id,
            'activo' => 0,
        ]);
    }

    private function providerData(array $overrides = []): array
    {
        return array_merge([
            'razon_social' => 'Importaciones Truper',
            'ruc' => '20512345678',
            'telefono' => '019876543',
            'email' => 'contacto@truper.com.pe',
            'direccion' => 'Av. Colonial 2300, Lima',
        ], $overrides);
    }
}
