<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuarioModuleTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $administrador;

    private Rol $rolPersonal;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::create([
            'nombre' => 'Administrador',
            'activo' => true,
        ]);
        $this->rolPersonal = Rol::create([
            'nombre' => 'Vendedor',
            'activo' => true,
        ]);

        $this->administrador = Usuario::create($this->userData([
            'id_rol' => 1,
            'nombre' => 'Administradora de prueba',
            'usuario' => 'admin-prueba',
            'email' => 'admin@example.test',
        ]));

        $this->actingAs($this->administrador)->withoutVite();
    }

    public function test_admin_can_list_and_filter_users(): void
    {
        $personal = Usuario::create($this->userData([
            'id_rol' => $this->rolPersonal->id_rol,
            'nombre' => 'Vendedor de prueba',
            'usuario' => 'vendedor-prueba',
            'email' => 'vendedor@example.test',
        ]));
        $personal->update(['activo' => false]);

        $this->get(route('usuarios.index', ['estado' => 'inactivo']))
            ->assertOk()
            ->assertSee('Vendedor de prueba')
            ->assertViewHas('usuarios', function ($usuarios) use ($personal) {
                return $usuarios->count() === 1
                    && $usuarios->first()->id_usuario === $personal->id_usuario;
            });
    }

    public function test_admin_can_create_user_with_hashed_password_and_audit(): void
    {
        $this->post(route('usuarios.store'), $this->validSubmission())
            ->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('tusuarios', [
            'usuario' => 'nuevo-personal',
            'email' => 'nuevo@example.test',
            'id_rol' => $this->rolPersonal->id_rol,
            'activo' => 1,
        ]);
        $this->assertTrue(Hash::check(
            'secreto123',
            Usuario::where('usuario', 'nuevo-personal')->value('password')
        ));
        $this->assertDatabaseHas('tauditoria', [
            'id_usuario' => $this->administrador->id_usuario,
            'accion' => 'USUARIO_CREADO',
            'tabla_afectada' => 'tusuarios',
        ]);
    }

    public function test_admin_can_update_user_without_replacing_password_when_blank(): void
    {
        $usuario = Usuario::create($this->userData([
            'id_rol' => $this->rolPersonal->id_rol,
            'nombre' => 'Personal anterior',
            'usuario' => 'personal-anterior',
            'email' => 'anterior@example.test',
        ]));
        $passwordAnterior = $usuario->password;

        $this->put(route('usuarios.update', $usuario->id_usuario), [
            'id_rol' => $this->rolPersonal->id_rol,
            'nombre' => 'Personal actualizado',
            'usuario' => 'personal-anterior',
            'email' => 'anterior@example.test',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('tusuarios', [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => 'Personal actualizado',
            'password' => $passwordAnterior,
        ]);
        $this->assertDatabaseHas('tauditoria', [
            'accion' => 'USUARIO_ACTUALIZADO',
            'id_registro' => $usuario->id_usuario,
        ]);
    }

    public function test_admin_can_deactivate_and_reactivate_user_without_deleting_it(): void
    {
        $usuario = Usuario::create($this->userData([
            'id_rol' => $this->rolPersonal->id_rol,
            'nombre' => 'Personal temporal',
            'usuario' => 'personal-temporal',
            'email' => 'temporal@example.test',
        ]));

        $this->delete(route('usuarios.destroy', $usuario->id_usuario))
            ->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('tusuarios', [
            'id_usuario' => $usuario->id_usuario,
            'activo' => 0,
        ]);
        $this->assertNotNull($usuario->fresh()->fecha_baja);

        $this->delete(route('usuarios.destroy', $usuario->id_usuario))
            ->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('tusuarios', [
            'id_usuario' => $usuario->id_usuario,
            'activo' => 1,
            'fecha_baja' => null,
        ]);
        $this->assertDatabaseCount('tauditoria', 2);
    }

    public function test_user_submission_rejects_duplicate_email_and_inactive_role(): void
    {
        Rol::where('id_rol', $this->rolPersonal->id_rol)->update(['activo' => false]);

        $this->from(route('usuarios.index'))
            ->post(route('usuarios.store'), $this->validSubmission([
                'email' => $this->administrador->email,
            ]))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHasErrors(['email', 'id_rol']);

        $this->assertDatabaseCount('tusuarios', 1);
    }

    public function test_only_administrators_can_access_user_management(): void
    {
        $personal = Usuario::create($this->userData([
            'id_rol' => $this->rolPersonal->id_rol,
            'nombre' => 'Vendedor de prueba',
            'usuario' => 'vendedor-prueba',
            'email' => 'vendedor@example.test',
        ]));

        $this->actingAs($personal)
            ->get(route('usuarios.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_deactivate_self_or_demote_last_admin(): void
    {
        $this->delete(route('usuarios.destroy', $this->administrador->id_usuario))
            ->assertSessionHas('error');

        $this->put(route('usuarios.update', $this->administrador->id_usuario), [
            'id_rol' => $this->rolPersonal->id_rol,
            'nombre' => $this->administrador->nombre,
            'usuario' => $this->administrador->usuario,
            'email' => $this->administrador->email,
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors('id_rol');

        $this->assertDatabaseHas('tusuarios', [
            'id_usuario' => $this->administrador->id_usuario,
            'activo' => 1,
            'id_rol' => 1,
        ]);
    }

    public function test_user_management_requires_authentication(): void
    {
        auth()->logout();

        $this->get(route('usuarios.index'))->assertRedirect(route('login'));
    }

    private function validSubmission(array $overrides = []): array
    {
        return array_merge([
            'id_rol' => $this->rolPersonal->id_rol,
            'nombre' => 'Nuevo personal',
            'usuario' => 'nuevo-personal',
            'email' => 'nuevo@example.test',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'sexo' => 'F',
            'telefono' => '987654321',
            'direccion' => 'Lima',
            'sobre_mi' => 'Personal de prueba',
        ], $overrides);
    }

    private function userData(array $overrides = []): array
    {
        return array_merge([
            'id_rol' => 1,
            'nombre' => 'Usuario de prueba',
            'usuario' => 'usuario-prueba',
            'email' => 'usuario@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ], $overrides);
    }
}
