<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $admin = Role::where('name', 'Admin')->first();
    $postulant = Role::where('name', 'Postulante')->first();
    $reviewer = Role::where('name', 'Revisor')->first();
    $editor = Role::where('name', 'Editor')->first();
    $financial = Role::where('name', 'Finanzas')->first();
    $adminRevista = Role::where('name', 'Admin-Revista')->first();

    $admin->givePermissionTo(Permission::where('module_key', 'seg')->get());
    $admin->givePermissionTo(Permission::where('module_key', 'cat')->get());
    $admin->givePermissionTo(Permission::where('module_key', 'priv')->get());
    $admin->givePermissionTo(Permission::where('module_key', 'div')->get());

    $adminRevista->givePermissionTo(Permission::where('module_key', 'priv')->get());
    $adminRevista->givePermissionTo(Permission::where('module_key', 'div')->get());

    //Permiso del menú de gráficas
    $admin->givePermissionTo('menu.graficas');
    $adminRevista->givePermissionTo('menu.graficas');
    $editor->givePermissionTo('menu.graficas');

    $postulant->givePermissionTo('article.index', 'article.store', 'article.update');
    $reviewer->givePermissionTo('article.index', 'articleReview.index', 'articleReview.update');
    $editor->givePermissionTo('article.index', 'article.update', 'article.evaluate', 'articleReview.index', 'articleReview.store', 'articleReview.update', 'articleReview.delete');

    $postulant->givePermissionTo('paymentVoucher.index', 'paymentVoucher.store', 'paymentVoucher.update');
    $financial->givePermissionTo('paymentVoucher.index', 'paymentVoucher.validate');
    }
}
