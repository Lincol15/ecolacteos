<?php

namespace Tests\Feature;

use App\Models\PlantConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSystemConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function companySettings(array $overrides = []): array
    {
        return array_merge([
            'nombre_planta' => 'Ecolácteos Huata S.A.C.',
            'ruc_planta' => '20123456789',
            'direccion_planta' => 'Jr. Los Andes 123, Huata',
            'telefono_planta' => '+51 965 370 052',
            'whatsapp_planta' => '',
            'email_planta' => 'ventas@ecolacteos.pe',
            'horario_atencion' => 'Lunes a Viernes',
            'facebook_url' => '',
            'instagram_url' => '',
        ], $overrides);
    }

    public function test_company_contact_saved_in_config_appears_on_the_public_site(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $this->actingAs($admin)->get(route('admin.config'))->assertOk()->assertSee('Empresa y contacto');

        $this->actingAs($admin)->put(route('admin.config-save-group', 'empresa'), ['settings' => $this->companySettings()])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('+51 965 370 052', PlantConfig::getValue('telefono_planta'));

        auth()->logout();
        $this->get(route('home'))->assertOk()->assertSee('Jr. Los Andes 123, Huata')->assertSee('+51 965 370 052');
        $this->get(route('contact'))->assertOk()->assertSee('ventas@ecolacteos.pe')->assertSee('Lunes a Viernes');
    }

    public function test_config_group_validates_email_and_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $this->actingAs($admin)->put(route('admin.config-save-group', 'empresa'), [
            'settings' => $this->companySettings(['email_planta' => 'no-es-correo']),
        ])->assertSessionHasErrors('settings.email_planta');

        $this->actingAs($admin)->put(route('admin.config-save-group', 'acopio'), [
            'settings' => [
                'precio_litro_leche' => '-1',
                'litros_maximos_entrega' => '10000',
                'dias_pago_semanal' => 'Viernes',
                'bono_volumen_minimo_litros' => '500',
                'bono_calidad_minimo_score' => '90',
            ],
        ])->assertSessionHasErrors('settings.precio_litro_leche');
    }

    public function test_only_admin_can_change_system_config(): void
    {
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);

        $this->actingAs($gerente)->get(route('admin.config'))->assertForbidden();
        $this->actingAs($gerente)->put(route('admin.config-save-group', 'empresa'), ['settings' => $this->companySettings()])->assertForbidden();
    }
}
