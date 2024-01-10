<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use BezhanSalleh\FilamentShield\Support\Utils;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = [
            [
                "name" => "super_admin",
                "guard_name" => "web",
                "permissions" => [
                    "view_product",
                    "view_any_product",
                    "create_product",
                    "update_product",
                    "restore_product",
                    "restore_any_product",
                    "replicate_product",
                    "reorder_product",
                    "delete_product",
                    "delete_any_product",
                    "force_delete_product",
                    "force_delete_any_product",
                    "view_project",
                    "view_any_project",
                    "create_project",
                    "update_project",
                    "restore_project",
                    "restore_any_project",
                    "replicate_project",
                    "reorder_project",
                    "delete_project",
                    "delete_any_project",
                    "force_delete_project",
                    "force_delete_any_project",
                    "view_role",
                    "view_any_role",
                    "create_role",
                    "update_role",
                    "delete_role",
                    "delete_any_role",
                    "view_test",
                    "view_any_test",
                    "create_test",
                    "update_test",
                    "restore_test",
                    "restore_any_test",
                    "replicate_test",
                    "reorder_test",
                    "delete_test",
                    "delete_any_test",
                    "force_delete_test",
                    "force_delete_any_test",
                    "view_user",
                    "view_any_user",
                    "create_user",
                    "update_user",
                    "restore_user",
                    "restore_any_user",
                    "replicate_user",
                    "reorder_user",
                    "delete_user",
                    "delete_any_user",
                    "force_delete_user",
                    "force_delete_any_user",
                    "page_Themes",
                    "widget_Account",
                    "widget_StatsOverview",
                    "widget_LatestUsersActivity"
                ]
            ]
        ];
        $directPermissions = [];

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(array $rolesWithPermissions): void
    {
        foreach ($rolesWithPermissions as $rolePlusPermission) {
            $role = Utils::getRoleModel()::firstOrCreate([
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name']
            ]);

            if (!blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect();

                collect($rolePlusPermission['permissions'])
                    ->each(function ($permission) use ($permissionModels) {
                        $permissionModels->push(
                            Utils::getPermissionModel()::firstOrCreate([
                                'name' => $permission,
                                'guard_name' => 'web'
                            ])
                        );
                    });
                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(array $directPermissions): void
    {
        foreach ($directPermissions as $permission) {
            if (Utils::getPermissionModel()::whereName($permission)->doesntExist()) {
                Utils::getPermissionModel()::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
