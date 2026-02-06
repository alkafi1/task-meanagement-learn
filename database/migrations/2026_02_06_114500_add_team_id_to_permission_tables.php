<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamKey = $columnNames['team_foreign_key'] ?? 'team_id';

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Roles Table
        if (Schema::hasTable($tableNames['roles'])) {
            if (!Schema::hasColumn($tableNames['roles'], $teamKey)) {
                try {
                    DB::statement("ALTER TABLE `{$tableNames['roles']}` ADD `{$teamKey}` BIGINT UNSIGNED NULL AFTER `id` ");
                    DB::statement("CREATE INDEX `roles_{$teamKey}_index` ON `{$tableNames['roles']}` (`{$teamKey}`)");
                } catch (\Exception $e) {}
            }
            // Try to update index, but don't fail if InnoDB blocks it
            try {
                DB::statement("ALTER TABLE `{$tableNames['roles']}` DROP INDEX `roles_name_guard_name_unique` ");
            } catch (\Exception $e) {}
            try {
                DB::statement("ALTER TABLE `{$tableNames['roles']}` ADD UNIQUE `roles_team_id_name_guard_name_unique` (`{$teamKey}`, `name`, `guard_name`) ");
            } catch (\Exception $e) {}
        }

        // 2. Model Has Permissions
        if (Schema::hasTable($tableNames['model_has_permissions'])) {
            if (!Schema::hasColumn($tableNames['model_has_permissions'], $teamKey)) {
                try {
                    DB::statement("ALTER TABLE `{$tableNames['model_has_permissions']}` ADD `{$teamKey}` BIGINT UNSIGNED NULL ");
                    DB::statement("CREATE INDEX `model_has_permissions_{$teamKey}_index` ON `{$tableNames['model_has_permissions']}` (`{$teamKey}`)");
                } catch (\Exception $e) {}
            }
            try {
                DB::statement("ALTER TABLE `{$tableNames['model_has_permissions']}` DROP PRIMARY KEY ");
            } catch (\Exception $e) {}
            try {
                DB::statement("ALTER TABLE `{$tableNames['model_has_permissions']}` ADD UNIQUE `model_has_permissions_permission_model_type_unique` (`{$teamKey}`, `permission_id`, `{$columnNames['model_morph_key']}`, `model_type`) ");
            } catch (\Exception $e) {}
        }

        // 3. Model Has Roles
        if (Schema::hasTable($tableNames['model_has_roles'])) {
            if (!Schema::hasColumn($tableNames['model_has_roles'], $teamKey)) {
                try {
                    DB::statement("ALTER TABLE `{$tableNames['model_has_roles']}` ADD `{$teamKey}` BIGINT UNSIGNED NULL ");
                    DB::statement("CREATE INDEX `model_has_roles_{$teamKey}_index` ON `{$tableNames['model_has_roles']}` (`{$teamKey}`)");
                } catch (\Exception $e) {}
            }
            try {
                DB::statement("ALTER TABLE `{$tableNames['model_has_roles']}` DROP PRIMARY KEY ");
            } catch (\Exception $e) {}
            try {
                DB::statement("ALTER TABLE `{$tableNames['model_has_roles']}` ADD UNIQUE `model_has_roles_role_model_type_unique` (`{$teamKey}`, `role_id`, `{$columnNames['model_morph_key']}`, `model_type`) ");
            } catch (\Exception $e) {}
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
