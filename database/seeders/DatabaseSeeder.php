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
        // Insert some stuff
        DB::table('users')->insert(
            array(
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('Admin'),
                'email_verified_at' => Carbon::now()
            )
        );

        // Insert some stuff
        DB::table('roles')->insert(
            array(
                'name' => 'Administrador',
                'active' => true,
            )
        );


        // Insert some stuff
        DB::table('user_role')->insert(
            array(
                'user_id' => DB::table('users')->select('id')->where('email', 'admin@admin.com')->first()->id,
                'role_id' => DB::table('roles')->select('id')->where('name', 'Administrador')->first()->id
            )
        );


        // Insert some stuff
        DB::table('currency')->insert(
            array(
                'name' => 'Real',
                'sigla' => 'BRL',
                'symbol' => 'R$'
            )
        );

        DB::table('broker')->insert([
            ['name' => '4BTS'],
            ['name' => 'Ossa'],
            ['name' => 'Pluralis'],
            ['name' => 'Unidas'],
            ['name' => 'Movida'],
            ['name' => '4BTS USA'],
            ['name' => '4BTS EUR'],
            ['name' => 'CATAR'],
        ]);



        DB::table('customer')->insert([
            'name' => '4BTS',
            'document' => '77.777.777/7777-77',
            'phone' => '61981925127',
            'email' => 'teste@teste.com',
            'responsibleAuthorizing' => 'Responsável pela altorização 4bts',
            'logo' => '/storage/logos/V69xCbFL9xW752FGKz0hu1erWbaxqNKab97EkuV3.png'
        ]);

        DB::table('apto')->insert([
            ['name' => 'DBL'],
            ['name' => 'FIT'],
            ['name' => 'QUA'],
            ['name' => 'SGL'],
            ['name' => 'Suite'],
            ['name' => 'Suite JR'],
            ['name' => 'TRP'],
            ['name' => 'TWIN'],
        ]);

        DB::table('category')->insert([
            ['name' => 'D Luxe'],
            ['name' => 'LUX'],
            ['name' => 'ROH'],
            ['name' => 'STD'],
            ['name' => 'Suite'],
            ['name' => 'Suite JR'],
            ['name' => 'SUP'],
        ]);

        DB::table('regime')->insert([
            ['name' => 'BKF'],
            ['name' => 'PC'],
            ['name' => 'MP'],
            ['name' => 'ALL'],
            ['name' => 'ALB'],
            ['name' => 'NO'],
        ]);

        DB::table('purpose')->insert([
            ['name' => 'Pernoite'],
            ['name' => 'Convidado'],
            ['name' => 'Massagem'],
            ['name' => 'Equipamentos'],
            ['name' => 'Late Out'],
            ['name' => 'Early In'],
            ['name' => 'Rezar'],
            ['name' => 'Day use'],
        ]);


        DB::table('service')->insert([
            ['name' => 'A&B - INT.'],
            ['name' => 'A&B-NAC'],
            ['name' => 'Extras'],
            ['name' => 'Outros'],
        ]);


        DB::table('service_type')->insert([
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
        ]);


        DB::table('local')->insert([
            ['name' => 'Sala Privativa'],
            ['name' => 'Restaurante'],
            ['name' => 'Fora do Hotel'],
            ['name' => 'Area de Eventos'],
            ['name' => 'Sem definição'],
            ['name' => 'No Andar ']
        ]);



        DB::table('crd')->insert([
            ['name' => 'CORPORATIVO CBF', 'number' => '69.215.0001', 'customer_id' =>  DB::table('customer')->select('id')->where('name', '4BTS')->first()->id],
            ['name' => 'EVENTOS CBF', 'number' => '69.215.0002', 'customer_id' =>  DB::table('customer')->select('id')->where('name', '4BTS')->first()->id]
        ]);


        foreach (Constants::PERMISSIONS as $p) {
            DB::table('permission')->insert(
                array(
                    'name' => $p['name'],
                    "title" => $p['title']
                )
            );

            DB::table('role_permission')->insert(
                array(
                    'permission_id' => DB::table('permission')->select('id')->where('name',  $p['name'])->first()->id,
                    'role_id' => DB::table('roles')->select('id')->where('name', 'Administrador')->first()->id
                )
            );
        }
    }
}
