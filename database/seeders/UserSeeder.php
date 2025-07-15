<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name'              => 'Administrador',
                'email'             => 'vitervolopezcaballero@gmail.com',
                'password'          => Hash::make('Password'),
                'institution_id'    => null,
                'email_verified_at' => now(),
                'created_at'        => now()
            ],
            [
                'name'              => 'Gloria Editor',
                'email'             => 'vitervo.lc@cenidet.tecnm.mx',
                'password'          => Hash::make('Password'),
                'institution_id'    => 1,
                'email_verified_at' => now(),
                'created_at'        => now()
            ],
            [
                'name'              => 'Rodolfo Editor',
                'email'             => 'veranocientifico@cenidet.tecnm.mx',
                'password'          => Hash::make('Password'),
                'institution_id'    => null,
                'email_verified_at' => now(),
                'created_at'        => now()
            ],
            [
                'name'              => 'Astorga Revisor',
                'email'             => 'vitervo@cenidet.edu.mx',
                'password'          => Hash::make('Password'),
                'institution_id'    => null,
                'email_verified_at' => now(),
                'created_at'        => now()
            ],
            [
                'name'              => 'Adan Revisor',
                'email'             => 'viterlopezcaballero@gmail.com',
                'password'          => Hash::make('Password'),
                'institution_id'    => null,
                'email_verified_at' => now(),
                'created_at'        => now()
            ],
            [
                'name'              => 'Vitervo Postulante',
                'email'             => 'consejo.ingenieria@cenidet.tecnm.mx',
                'password'          => Hash::make('Password'),
                'institution_id'    => null,
                'email_verified_at' => now(),
                'created_at'        => now()
            ],
            [
                'name'              => 'Norita Financiero',
                'email'             => 'rf@cenidet.tecnm.mx',
                'password'          => Hash::make('Password'),
                'institution_id'    => null,
                'email_verified_at' => now(),
                'created_at'        => now()
            ],
        ]);
        // Roles del sistema
        $admin = Role::create(['name' => 'Admin', 'description' => 'Administrador']);
        $editor = Role::create(['name' => 'Editor', 'description' => 'Editor']);
        $reviewer = Role::create(['name' => 'Revisor', 'description' => 'Revisor']);
        $postulant = Role::create(['name' => 'Postulante', 'description' => 'Postulante']);
        $financial = Role::create(['name' => 'Finanzas', 'description' => 'Finanzas']);

        User::find(1)->assignRole($admin);
        User::find(2)->assignRole($editor);
        User::find(3)->assignRole($editor);
        User::find(4)->assignRole($reviewer);
        User::find(5)->assignRole($reviewer);
        User::find(6)->assignRole($postulant);
        User::find(7)->assignRole($financial);
    }
}
