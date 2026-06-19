<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Middleware\Constants;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Insert Admin user if not exists
        if (!DB::table('users')->where('email', 'admin@admin.com')->exists()) {
            DB::table('users')->insert(
                array(
                    'name' => 'Admin',
                    'email' => 'admin@admin.com',
                    'password' => Hash::make('Admin'),
                    'email_verified_at' => Carbon::now()
                )
            );
        }

        // Insert Administrador role if not exists
        if (!DB::table('roles')->where('name', 'Administrador')->exists()) {
            DB::table('roles')->insert(
                array(
                    'name' => 'Administrador',
                    'active' => true,
                )
            );
        }

        // Insert user_role mapping if not exists
        $adminUser = DB::table('users')->where('email', 'admin@admin.com')->first();
        $adminRole = DB::table('roles')->where('name', 'Administrador')->first();
        if ($adminUser && $adminRole) {
            $exists = DB::table('user_role')
                ->where('user_id', $adminUser->id)
                ->where('role_id', $adminRole->id)
                ->exists();
            if (!$exists) {
                DB::table('user_role')->insert(
                    array(
                        'user_id' => $adminUser->id,
                        'role_id' => $adminRole->id
                    )
                );
            }
        }

        // Insert currency BRL if not exists
        if (!DB::table('currency')->where('sigla', 'BRL')->exists()) {
            DB::table('currency')->insert(
                array(
                    'name' => 'Real',
                    'sigla' => 'BRL',
                    'symbol' => 'R$'
                )
            );
        }

        // Insert brokers
        $brokers = [
            ['name' => '4BTS'],
            ['name' => 'Ossa'],
            ['name' => 'Pluralis'],
            ['name' => 'Unidas'],
            ['name' => 'Movida'],
            ['name' => '4BTS USA'],
            ['name' => '4BTS EUR'],
            ['name' => 'CATAR'],
        ];
        foreach ($brokers as $broker) {
            if (!DB::table('broker')->where('name', $broker['name'])->exists()) {
                DB::table('broker')->insert($broker);
            }
        }

        // Insert customer 4BTS if not exists
        if (!DB::table('customer')->where('name', '4BTS')->exists()) {
            DB::table('customer')->insert([
                'name' => '4BTS',
                'document' => '77.777.777/7777-77',
                'phone' => '61981925127',
                'color' => '#e9540d',
                'email' => 'teste@teste.com',
                'responsibleAuthorizing' => 'Responsável pela altorização 4bts',
                'logo' => '/storage/logos/V69xCbFL9xW752FGKz0hu1erWbaxqNKab97EkuV3.png'
            ]);
        }

        // Insert apto types
        $aptos = [
            ['name' => 'DBL'],
            ['name' => 'FIT'],
            ['name' => 'QUA'],
            ['name' => 'SGL'],
            ['name' => 'Suite'],
            ['name' => 'Suite JR'],
            ['name' => 'TRP'],
            ['name' => 'TWIN'],
        ];
        foreach ($aptos as $apto) {
            if (!DB::table('apto')->where('name', $apto['name'])->exists()) {
                DB::table('apto')->insert($apto);
            }
        }

        // Insert categories
        $categories = [
            ['name' => 'D Luxe'],
            ['name' => 'LUX'],
            ['name' => 'ROH'],
            ['name' => 'STD'],
            ['name' => 'Suite'],
            ['name' => 'Suite JR'],
            ['name' => 'SUP'],
        ];
        foreach ($categories as $cat) {
            if (!DB::table('category')->where('name', $cat['name'])->exists()) {
                DB::table('category')->insert($cat);
            }
        }

        // Insert regimes
        $regimes = [
            ['name' => 'BKF'],
            ['name' => 'PC'],
            ['name' => 'MP'],
            ['name' => 'ALL'],
            ['name' => 'ALB'],
            ['name' => 'NO'],
        ];
        foreach ($regimes as $reg) {
            if (!DB::table('regime')->where('name', $reg['name'])->exists()) {
                DB::table('regime')->insert($reg);
            }
        }

        // Insert purposes
        $purposes = [
            ['name' => 'Pernoite'],
            ['name' => 'Convidado'],
            ['name' => 'Massagem'],
            ['name' => 'Equipamentos'],
            ['name' => 'Late Out'],
            ['name' => 'Early In'],
            ['name' => 'Rezar'],
            ['name' => 'Day use'],
        ];
        foreach ($purposes as $purp) {
            if (!DB::table('purpose')->where('name', $purp['name'])->exists()) {
                DB::table('purpose')->insert($purp);
            }
        }

        // Insert services
        $services = [
            ['name' => 'A&B - INT.'],
            ['name' => 'A&B-NAC'],
            ['name' => 'Extras'],
            ['name' => 'Outros'],
        ];
        foreach ($services as $srv) {
            if (!DB::table('service')->where('name', $srv['name'])->exists()) {
                DB::table('service')->insert($srv);
            }
        }

        // Insert service halls
        $serviceHalls = [
            ['name' => 'Salões de Eventos'],
            ['name' => 'Aluguel de Equipamentos'],
            ['name' => 'Coffe Break'],
            ['name' => 'Outros'],
            ['name' => 'Shows'],
            ['name' => 'Big Screen TV'],
            ['name' => 'Projector TV'],
            ['name' => 'Led TV'],
        ];
        foreach ($serviceHalls as $hall) {
            if (!DB::table('service_hall')->where('name', $hall['name'])->exists()) {
                DB::table('service_hall')->insert($hall);
            }
        }

        // Insert purpose halls
        $purposeHalls = [
            ['name' => 'Reuniões'],
            ['name' => 'Conferência Imprensa'],
            ['name' => 'Refeições'],
            ['name' => 'Equipamentos'],
            ['name' => 'Evento'],
            ['name' => 'Preleção'],
        ];
        foreach ($purposeHalls as $phall) {
            if (!DB::table('purpose_hall')->where('name', $phall['name'])->exists()) {
                DB::table('purpose_hall')->insert($phall);
            }
        }

        // Insert service types
        $serviceTypes = [
            ['name' => 'Cafe Manhã'],
            ['name' => 'Almoço'],
            ['name' => 'Jantar'],
            ['name' => 'Lanche'],
            ['name' => 'Ceia'],
            ['name' => 'Lanche Box'],
            ['name' => 'Lanche Especial'],
            ['name' => 'Gelo'],
            ['name' => 'Cooffe Break'],
            ['name' => 'Garçons'],
        ];
        foreach ($serviceTypes as $stype) {
            if (!DB::table('service_type')->where('name', $stype['name'])->exists()) {
                DB::table('service_type')->insert($stype);
            }
        }

        // Insert locals
        $locals = [
            ['name' => 'Sala Privativa'],
            ['name' => 'Restaurante'],
            ['name' => 'Fora do Hotel'],
            ['name' => 'Area de Eventos'],
            ['name' => 'Sem definição'],
            ['name' => 'No Andar ']
        ];
        foreach ($locals as $loc) {
            if (!DB::table('local')->where('name', $loc['name'])->exists()) {
                DB::table('local')->insert($loc);
            }
        }

        // Insert service adds
        $serviceAdds = [
            ['name' => 'Lavanderia'],
            ['name' => 'Gelo'],
            ['name' => 'Frigobar'],
            ['name' => 'Internet'],
            ['name' => 'Massagem'],
            ['name' => 'Segurança'],
            ['name' => 'Outros'],
        ];
        foreach ($serviceAdds as $sadd) {
            if (!DB::table('service_add')->where('name', $sadd['name'])->exists()) {
                DB::table('service_add')->insert($sadd);
            }
        }

        // Insert measures
        $measures = [
            ['name' => 'Kilo'],
            ['name' => 'Unidade'],
            ['name' => 'Exclusiva'],
            ['name' => 'MHZ'],
        ];
        foreach ($measures as $meas) {
            if (!DB::table('measure')->where('name', $meas['name'])->exists()) {
                DB::table('measure')->insert($meas);
            }
        }

        // Insert frequencies
        $frequencies = [
            ['name' => 'Diário'],
            ['name' => 'Por Hora'],
            ['name' => 'Semanal'],
            ['name' => 'Up Load'],
            ['name' => 'Down Load'],
            ['name' => 'Regular'],
            ['name' => 'Só Agua'],
            ['name' => 'Patrocinador'],
        ];
        foreach ($frequencies as $freq) {
            if (!DB::table('frequency')->where('name', $freq['name'])->exists()) {
                DB::table('frequency')->insert($freq);
            }
        }

        // Insert crds
        $btsCustomer = DB::table('customer')->where('name', '4BTS')->first();
        if ($btsCustomer) {
            $crds = [
                ['name' => 'CORPORATIVO CBF', 'number' => '69.215.0001', 'customer_id' =>  $btsCustomer->id],
                ['name' => 'EVENTOS CBF', 'number' => '69.215.0002', 'customer_id' =>  $btsCustomer->id]
            ];
            foreach ($crds as $crd) {
                $exists = DB::table('crd')
                    ->where('name', $crd['name'])
                    ->where('number', $crd['number'])
                    ->where('customer_id', $crd['customer_id'])
                    ->exists();
                if (!$exists) {
                    DB::table('crd')->insert($crd);
                }
            }
        }

        // Insert permissions
        foreach (Constants::PERMISSIONS as $p) {
            if (!DB::table('permission')->where('name', $p['name'])->exists()) {
                DB::table('permission')->insert(
                    array(
                        'name' => $p['name'],
                        "title" => $p['title']
                    )
                );
            }

            $perm = DB::table('permission')->select('id')->where('name',  $p['name'])->first();
            $role = DB::table('roles')->select('id')->where('name', 'Administrador')->first();
            if ($perm && $role) {
                $exists = DB::table('role_permission')
                    ->where('permission_id', $perm->id)
                    ->where('role_id', $role->id)
                    ->exists();
                if (!$exists) {
                    DB::table('role_permission')->insert(
                        array(
                            'permission_id' => $perm->id,
                            'role_id' => $role->id
                        )
                    );
                }
            }
        }
    }
}
