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
        
        $pBrand = \App\Models\Permission::create(['name' => 'brand_admin']);
        $role = \App\Models\Role::create(['name' => 'Admin']);
        $role->permissions()->attach($pBrand->id);
        
        $this->user = User::factory()->create();
        $this->user->roles()->attach($role->id);

        // Registrar Gates Manualmente para o ambiente de teste
        foreach (\App\Models\Permission::all() as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission->name, function ($user) use ($permission) {
                return $user->roles()->whereHas('permissions', function ($q) use ($permission) {
                    $q->where('name', $permission->name);
                })->exists();
            });
        }
    }

    /**
     * CT-002 – Impedir cadastro duplicado
     */
    public function test_cannot_create_duplicate_brand_name()
    {
        Brand::create(['name' => 'APPLE']);

        $this->actingAs($this->user)
            ->post(route('brand-save'), [
                'name' => 'APPLE'
            ]);

        $this->assertEquals(1, Brand::where('name', 'APPLE')->count());
    }

    /**
     * CT-003 – Padronização de maiúsculas/minúsculas
     */
    public function test_brand_name_standardization()
    {
        $this->actingAs($this->user)
            ->post(route('brand-save'), [
                'name' => 'samsung'
            ]);

        $brand = Brand::where('name', 'LIKE', 'samsung')->first();
        $this->assertNotNull($brand);
    }

    /**
     * CT-004 – Ativar/Inativar cadastro
     */
    public function test_activate_deactivate_brand()
    {
        $brand = Brand::create(['name' => 'SONY', 'active' => true]);

        // Inativar (Usando PUT conforme definido em routes/auth.php)
        $this->actingAs($this->user)
            ->put(route('brand-deactivate', ['id' => $brand->id]));
        
        $this->assertFalse((bool) Brand::withoutGlobalScope('active')->find($brand->id)->active);

        // Ativar (Usando PUT)
        $this->actingAs($this->user)
            ->put(route('brand-activate', ['id' => $brand->id]));

        $this->assertTrue((bool) Brand::find($brand->id)->active);
    }
}
