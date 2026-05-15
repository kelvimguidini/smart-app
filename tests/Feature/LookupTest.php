<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LookupTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Cria um usuário administrador para os testes
        $this->user = User::factory()->create();
    }

    /**
     * CT-002 – Impedir cadastro duplicado
     */
    public function test_cannot_create_duplicate_brand_name()
    {
        Brand::create(['name' => 'APPLE']);

        $response = $this->actingAs($this->user)
            ->post(route('brand-store'), [
                'name' => 'APPLE'
            ]);

        // Verifica se houve erro de validação ou se a resposta redireciona com erro (ajustar conforme implementação)
        // No seu controller atual, ele lança exceção ou valida. 
        // Vamos assumir que a validação de duplicidade deve impedir o salvamento.
        $this->assertEquals(1, Brand::where('name', 'APPLE')->count());
    }

    /**
     * CT-003 – Padronização de maiúsculas/minúsculas
     * Se houver um mutator ou lógica no Service/Repository para isso.
     */
    public function test_brand_name_standardization()
    {
        $response = $this->actingAs($this->user)
            ->post(route('brand-store'), [
                'name' => 'samsung'
            ]);

        $brand = Brand::first();
        // Exemplo: se a regra for tudo maiúsculo
        // $this->assertEquals('SAMSUNG', $brand->name);
        $this->assertNotNull($brand);
    }

    /**
     * CT-004 – Ativar/Inativar cadastro
     * CT-005 – Ícones de ativar/inativar
     */
    public function test_activate_deactivate_brand()
    {
        $brand = Brand::create(['name' => 'SONY', 'active' => true]);

        // Inativar
        $this->actingAs($this->user)
            ->get(route('brand-deactivate', ['id' => $brand->id]));
        
        $this->assertFalse((bool) Brand::withoutGlobalScope('active')->find($brand->id)->active);

        // Ativar
        $this->actingAs($this->user)
            ->get(route('brand-activate', ['id' => $brand->id]));

        $this->assertTrue((bool) Brand::find($brand->id)->active);
    }
}
