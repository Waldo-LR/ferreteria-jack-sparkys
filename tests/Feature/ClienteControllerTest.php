<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_clients_page_searches_and_paginates_active_clients(): void
    {
        $this->actingAs($this->createAdministrator());
        $matchedClient = $this->createClient('45891234', 'Constructora San Martín', 'contacto@example.test', '987654321');
        $this->createClient('20123456789', 'Ferretería Central', 'central@example.test', '912345678', 'RUC');

        for ($number = 3; $number <= 17; $number++) {
            $this->createClient(
                (string) (60000000 + $number),
                'Cliente '.$number,
                "cliente{$number}@example.test",
                '900000000'
            );
        }

        Cliente::create([
            'tipo_documento' => 'DNI',
            'dni_ruc' => '70000000',
            'nombres' => 'Cliente inactivo',
            'activo' => false,
        ]);

        $this->get(route('clientes.index', ['buscar' => '987654321']))
            ->assertOk()
            ->assertSee('Gestión de Clientes')
            ->assertSee('Registrar cliente')
            ->assertSee('Constructora San Martín')
            ->assertDontSee('Ferretería Central')
            ->assertDontSee('Cliente inactivo')
            ->assertViewHas('clientes', function ($clients) use ($matchedClient): bool {
                return $clients->total() === 1
                    && $clients->count() === 1
                    && $clients->first()->is($matchedClient);
            });

        $this->get(route('clientes.index'))
            ->assertOk()
            ->assertViewHas('clientes', function ($clients): bool {
                return $clients->total() === 17
                    && $clients->count() === 15
                    && $clients->hasPages();
            });
    }

    public function test_valid_customer_registration_persists_and_confirms_success(): void
    {
        $this->actingAs($this->createAdministrator())
            ->post(route('clientes.store'), [
                'tipo_documento' => 'RUC',
                'dni_ruc' => '20123456789',
                'nombres' => 'Comercial Andina S.A.C.',
                'email' => 'ventas@example.test',
                'telefono' => '987654321',
                'direccion' => 'Av. Principal 123',
            ])
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success', 'Cliente registrado exitosamente.');

        $this->assertDatabaseHas('tclientes', [
            'tipo_documento' => 'RUC',
            'dni_ruc' => '20123456789',
            'nombres' => 'Comercial Andina S.A.C.',
            'email' => 'ventas@example.test',
            'activo' => true,
        ]);
    }

    public function test_duplicate_customer_document_is_rejected_without_creating_a_record(): void
    {
        $this->createClient('45891234', 'Cliente existente');

        $this->actingAs($this->createAdministrator())
            ->from(route('clientes.index'))
            ->post(route('clientes.store'), [
                'tipo_documento' => 'DNI',
                'dni_ruc' => '45891234',
                'nombres' => 'Cliente duplicado',
            ])
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHasErrors('dni_ruc');

        $this->assertDatabaseCount('tclientes', 1);
        $this->assertDatabaseMissing('tclientes', ['nombres' => 'Cliente duplicado']);
    }

    private function createAdministrator(): Usuario
    {
        $role = new Rol([
            'nombre' => 'Administrador',
            'activo' => true,
        ]);
        $role->id_rol = 1;
        $role->save();

        return Usuario::create([
            'id_rol' => $role->id_rol,
            'nombre' => 'Administradora de prueba',
            'usuario' => 'admin-clientes',
            'email' => 'admin-clientes@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function createClient(
        string $document,
        string $name,
        ?string $email = null,
        ?string $phone = null,
        string $documentType = 'DNI'
    ): Cliente {
        return Cliente::create([
            'tipo_documento' => $documentType,
            'dni_ruc' => $document,
            'nombres' => $name,
            'email' => $email,
            'telefono' => $phone,
            'activo' => true,
        ]);
    }
}
