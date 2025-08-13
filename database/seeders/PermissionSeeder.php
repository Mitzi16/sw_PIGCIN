<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // PERMISOS DE MODULOS DEL SISTEMA (visibilidad en el menu)
        Permission::create(['name' => 'menu.seguridad', 'guard_name' => 'web', 'description' => 'Administración de Seguridad', 'module_key' => 'seg']);
        Permission::create(['name' => 'menu.catalogo', 'guard_name' => 'web', 'description' => 'Administración catálogos', 'module_key' => 'cat']);
        Permission::create(['name' => 'menu.graficas', 'guard_name' => 'web', 'description' => 'Administración de Graficas', 'module_key' => 'cat']);

        // PERMISOS DE SEGURIDAD
        Permission::create(['name' => 'module.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'module.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'module.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'module.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'seg']);

        Permission::create(['name' => 'permission.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'permission.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'permission.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'permission.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'seg']);

        Permission::create(['name' => 'role.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'role.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'role.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'role.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'seg']);

        Permission::create(['name' => 'user.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'user.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'user.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'seg']);
        Permission::create(['name' => 'user.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'seg']);

        // CATALOGOS
        Permission::create(['name' => 'knowledgeArea.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'knowledgeArea.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'knowledgeArea.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'knowledgeArea.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'cat']);

        Permission::create(['name' => 'knowledgeSubArea.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'knowledgeSubArea.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'knowledgeSubArea.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'knowledgeSubArea.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'cat']);

        Permission::create(['name' => 'criterion.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'criterion.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'criterion.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'criterion.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'cat']);

        Permission::create(['name' => 'institution.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'institution.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'institution.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'institution.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'cat']);

        Permission::create(['name' => 'articleType.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'articleType.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'articleType.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'articleType.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'cat']);


        Permission::create(['name' => 'call.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'call.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'call.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'cat']);
        Permission::create(['name' => 'call.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'cat']);

        // DIVULGACION
        Permission::create(['name' => 'article.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'article.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'article.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'article.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'article.evaluate', 'guard_name' => 'web', 'description' => 'Evaluar Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'article.all', 'guard_name' => 'web', 'description' => 'Acceso a todos los Registros', 'module_key' => 'priv']);

        Permission::create(['name' => 'articleReview.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'articleReview.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'articleReview.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'articleReview.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'div']);

        Permission::create(['name' => 'paymentVoucher.index', 'guard_name' => 'web', 'description' => 'Leer Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'paymentVoucher.store', 'guard_name' => 'web', 'description' => 'Crear Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'paymentVoucher.update', 'guard_name' => 'web', 'description' => 'Actualizar Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'paymentVoucher.delete', 'guard_name' => 'web', 'description' => 'Eliminar Registros', 'module_key' => 'div']);
        Permission::create(['name' => 'paymentVoucher.validate', 'guard_name' => 'web', 'description' => 'Validar Registros', 'module_key' => 'div']);
    }
}
