<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Configuracion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdministracionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_filter_audit_records_by_search_and_date(): void
    {
        $admin = $this->createUser(1, 'admin', 'Administradora');
        $this->actingAs($admin);

        $this->travelTo('2026-10-01 10:00:00');
        $olderRecord = Auditoria::create($this->auditData([
            'accion' => 'USUARIO_CREADO',
            'detalle' => 'Alta de usuario vendedor',
        ]));

        $this->travelTo('2026-11-01 10:00:00');
        $matchingRecord = Auditoria::create($this->auditData([
            'accion' => 'CONFIGURACION_ACTUALIZADA',
            'tabla_afectada' => 'tconfiguracion',
            'detalle' => 'Cambio de empresa',
        ]));
        Auditoria::create($this->auditData([
            'accion' => 'USUARIO_CREADO',
            'detalle' => 'Alta de usuario cajero',
        ]));

        $response = $this->get(route('auditoria.index', [
            'buscar' => 'Cambio',
            'fecha_desde' => '2026-11-01',
            'fecha_hasta' => '2026-11-01',
        ]));

        $response->assertOk()
            ->assertViewHas('auditorias', function ($auditorias) use ($olderRecord, $matchingRecord) {
                return $auditorias->count() === 1
                    && $auditorias->first()->id_auditoria === $matchingRecord->id_auditoria
                    && $auditorias->first()->id_auditoria !== $olderRecord->id_auditoria;
            })
            ->assertSee('Registro de Auditoría');
    }

    public function test_audit_date_filter_rejects_an_end_date_before_the_start_date(): void
    {
        $this->actingAs($this->createUser(1, 'admin', 'Administradora'));

        $this->from(route('auditoria.index'))
            ->get(route('auditoria.index', [
                'fecha_desde' => '2026-11-02',
                'fecha_hasta' => '2026-11-01',
            ]))
            ->assertRedirect(route('auditoria.index'))
            ->assertSessionHasErrors('fecha_hasta');
    }

    public function test_admin_can_view_audit_details_with_escaped_output(): void
    {
        $admin = $this->createUser(1, 'admin', '<script>alert(3)</script>');
        $this->actingAs($admin);
        Auditoria::create($this->auditData([
            'accion' => '<script>alert(1)</script>',
            'detalle' => '<script>alert(2)</script>',
        ]));

        $this->get(route('auditoria.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<script>alert(2)</script>', false)
            ->assertDontSee('<script>alert(3)</script>', false);
    }

    public function test_audit_and_company_settings_require_an_admin(): void
    {
        $employee = $this->createUser(2, 'vendedor', 'Vendedor');
        $this->actingAs($employee);

        $this->get(route('auditoria.index'))->assertForbidden();
        $this->get(route('configuracion.index'))->assertForbidden();
        $this->put(route('configuracion.update'), $this->validConfiguration())
            ->assertForbidden();
    }

    public function test_audit_and_company_settings_require_authentication(): void
    {
        auth()->logout();

        $this->get(route('auditoria.index'))->assertRedirect(route('login'));
        $this->get(route('configuracion.index'))->assertRedirect(route('login'));
        $this->put(route('configuracion.update'), $this->validConfiguration())
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_update_company_settings_and_audit_the_change(): void
    {
        $admin = $this->createUser(1, 'admin', 'Administradora');
        $this->actingAs($admin);
        Configuracion::create($this->validConfiguration([
            'razon_social' => 'Empresa anterior',
        ]));

        $this->put(route('configuracion.update'), $this->validConfiguration([
            'razon_social' => 'Ferretería actualizada S.A.C.',
            'ruc' => '20123456789',
            'igv_porcentaje' => '18.00',
            'mora_porcentaje' => '4.50',
            'dias_recuperacion' => 45,
            'dias_alerta_vencimiento' => 7,
            'cierre_automatico' => '1',
            'id_configuracion' => 999,
            'created_at' => '2000-01-01',
        ]))->assertRedirect(route('configuracion.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tconfiguracion', [
            'id_configuracion' => 1,
            'razon_social' => 'Ferretería actualizada S.A.C.',
            'ruc' => '20123456789',
            'igv_porcentaje' => '18.00',
            'mora_porcentaje' => '4.50',
            'dias_recuperacion' => 45,
            'dias_alerta_vencimiento' => 7,
            'cierre_automatico' => 1,
        ]);
        $this->assertDatabaseCount('tconfiguracion', 1);
        $this->assertDatabaseHas('tauditoria', [
            'id_usuario' => $admin->id_usuario,
            'accion' => 'CONFIGURACION_ACTUALIZADA',
            'tabla_afectada' => 'tconfiguracion',
            'id_registro' => 1,
        ]);
    }

    public function test_admin_can_initialize_company_settings_when_the_record_is_missing(): void
    {
        $admin = $this->createUser(1, 'admin', 'Administradora');
        $this->actingAs($admin);

        $this->get(route('configuracion.index'))
            ->assertOk()
            ->assertSee('Configuración de Empresa');

        $this->put(route('configuracion.update'), $this->validConfiguration())
            ->assertRedirect(route('configuracion.index'));

        $this->assertDatabaseHas('tconfiguracion', [
            'id_configuracion' => 1,
            'razon_social' => 'Ferretería de prueba S.A.C.',
        ]);
        $this->assertDatabaseCount('tconfiguracion', 1);
    }

    public function test_invalid_company_settings_are_rejected_without_writes(): void
    {
        $admin = $this->createUser(1, 'admin', 'Administradora');
        $this->actingAs($admin);
        Configuracion::create($this->validConfiguration());

        $this->from(route('configuracion.index'))
            ->put(route('configuracion.update'), $this->validConfiguration([
                'ruc' => '123',
                'email' => 'no-es-correo',
                'igv_porcentaje' => '101',
                'dias_recuperacion' => -1,
                'cierre_automatico' => 'on',
            ]))
            ->assertRedirect(route('configuracion.index'))
            ->assertSessionHasErrors([
                'ruc',
                'email',
                'igv_porcentaje',
                'dias_recuperacion',
                'cierre_automatico',
            ]);

        $this->assertDatabaseHas('tconfiguracion', [
            'id_configuracion' => 1,
            'razon_social' => 'Ferretería de prueba S.A.C.',
            'ruc' => '20609876543',
        ]);
        $this->assertDatabaseCount('tauditoria', 0);
    }

    private function createUser(int $roleId, string $username, string $name): Usuario
    {
        $role = new Rol([
            'nombre' => $roleId === 1 ? 'Administrador' : 'Vendedor',
            'activo' => true,
        ]);
        $role->id_rol = $roleId;
        $role->save();

        return Usuario::create([
            'id_rol' => $roleId,
            'nombre' => $name,
            'usuario' => $username,
            'email' => "{$username}@example.test",
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function auditData(array $overrides = []): array
    {
        return array_merge([
            'id_usuario' => 1,
            'accion' => 'PRUEBA',
            'tabla_afectada' => 'tusuarios',
            'id_registro' => 1,
            'detalle' => 'Evento de prueba',
            'ip' => '127.0.0.1',
        ], $overrides);
    }

    private function validConfiguration(array $overrides = []): array
    {
        return array_merge([
            'razon_social' => 'Ferretería de prueba S.A.C.',
            'ruc' => '20609876543',
            'direccion' => 'Av. Principal 123, Lima',
            'telefono' => '987654321',
            'email' => 'empresa@example.test',
            'slogan' => 'Herramientas para tus proyectos',
            'igv_porcentaje' => '18.00',
            'mora_porcentaje' => '5.00',
            'dias_recuperacion' => 30,
            'dias_alerta_vencimiento' => 5,
            'cierre_automatico' => '0',
        ], $overrides);
    }
}
