<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["Create:Announcement","Create:Complaint","Create:Dues","Create:DuesPayment","Create:LetterRequest","Create:LetterType","Create:Role","Create:Transaction","Create:TransactionCategory","Create:User","Delete:Announcement","Delete:Complaint","Delete:Dues","Delete:DuesPayment","Delete:LetterRequest","Delete:LetterType","Delete:Role","Delete:Transaction","Delete:TransactionCategory","Delete:User","DeleteAny:Announcement","DeleteAny:Complaint","DeleteAny:Dues","DeleteAny:DuesPayment","DeleteAny:LetterRequest","DeleteAny:LetterType","DeleteAny:Role","DeleteAny:Transaction","DeleteAny:TransactionCategory","DeleteAny:User","ForceDelete:Announcement","ForceDelete:Complaint","ForceDelete:Dues","ForceDelete:DuesPayment","ForceDelete:LetterRequest","ForceDelete:LetterType","ForceDelete:Role","ForceDelete:Transaction","ForceDelete:TransactionCategory","ForceDelete:User","ForceDeleteAny:Announcement","ForceDeleteAny:Complaint","ForceDeleteAny:Dues","ForceDeleteAny:DuesPayment","ForceDeleteAny:LetterRequest","ForceDeleteAny:LetterType","ForceDeleteAny:Role","ForceDeleteAny:Transaction","ForceDeleteAny:TransactionCategory","ForceDeleteAny:User","Reorder:Announcement","Reorder:Complaint","Reorder:Dues","Reorder:DuesPayment","Reorder:LetterRequest","Reorder:LetterType","Reorder:Role","Reorder:Transaction","Reorder:TransactionCategory","Reorder:User","Replicate:Announcement","Replicate:Complaint","Replicate:Dues","Replicate:DuesPayment","Replicate:LetterRequest","Replicate:LetterType","Replicate:Role","Replicate:Transaction","Replicate:TransactionCategory","Replicate:User","Restore:Announcement","Restore:Complaint","Restore:Dues","Restore:DuesPayment","Restore:LetterRequest","Restore:LetterType","Restore:Role","Restore:Transaction","Restore:TransactionCategory","Restore:User","RestoreAny:Announcement","RestoreAny:Complaint","RestoreAny:Dues","RestoreAny:DuesPayment","RestoreAny:LetterRequest","RestoreAny:LetterType","RestoreAny:Role","RestoreAny:Transaction","RestoreAny:TransactionCategory","RestoreAny:User","Update:Announcement","Update:Complaint","Update:Dues","Update:DuesPayment","Update:LetterRequest","Update:LetterType","Update:Role","Update:Transaction","Update:TransactionCategory","Update:User","View:Announcement","View:Complaint","View:Dues","View:DuesPayment","View:IncomeExpenseChart","View:KasStatsOverview","View:LetterRequest","View:LetterType","View:Role","View:Transaction","View:TransactionCategory","View:User","ViewAny:Announcement","ViewAny:Complaint","ViewAny:Dues","ViewAny:DuesPayment","ViewAny:LetterRequest","ViewAny:LetterType","ViewAny:Role","ViewAny:Transaction","ViewAny:TransactionCategory","ViewAny:User","ViewAny:Family","View:Family","Create:Family","Update:Family","Delete:Family","DeleteAny:Family","Restore:Family","ForceDelete:Family","ForceDeleteAny:Family","RestoreAny:Family","Replicate:Family","Reorder:Family","ViewAny:Resident","View:Resident","Create:Resident","Update:Resident","Delete:Resident","DeleteAny:Resident","Restore:Resident","ForceDelete:Resident","ForceDeleteAny:Resident","RestoreAny:Resident","Replicate:Resident","Reorder:Resident","View:CommunityStatsOverview","View:FundDistributionChart"]},{"name":"pengurus","guard_name":"web","permissions":["Create:Announcement","Create:Complaint","Create:Dues","Create:DuesPayment","Create:LetterRequest","Create:LetterType","Create:Transaction","Create:TransactionCategory","Create:User","Delete:Announcement","Delete:Complaint","Delete:Dues","Delete:DuesPayment","Delete:LetterRequest","Delete:LetterType","Delete:Transaction","Delete:TransactionCategory","Delete:User","DeleteAny:Announcement","DeleteAny:Complaint","DeleteAny:Dues","DeleteAny:DuesPayment","DeleteAny:LetterRequest","DeleteAny:LetterType","DeleteAny:Transaction","DeleteAny:TransactionCategory","DeleteAny:User","ForceDelete:Announcement","ForceDelete:Complaint","ForceDelete:Dues","ForceDelete:DuesPayment","ForceDelete:LetterRequest","ForceDelete:LetterType","ForceDelete:Transaction","ForceDelete:TransactionCategory","ForceDelete:User","ForceDeleteAny:Announcement","ForceDeleteAny:Complaint","ForceDeleteAny:Dues","ForceDeleteAny:DuesPayment","ForceDeleteAny:LetterRequest","ForceDeleteAny:LetterType","ForceDeleteAny:Transaction","ForceDeleteAny:TransactionCategory","ForceDeleteAny:User","Reorder:Announcement","Reorder:Complaint","Reorder:Dues","Reorder:DuesPayment","Reorder:LetterRequest","Reorder:LetterType","Reorder:Transaction","Reorder:TransactionCategory","Reorder:User","Replicate:Announcement","Replicate:Complaint","Replicate:Dues","Replicate:DuesPayment","Replicate:LetterRequest","Replicate:LetterType","Replicate:Transaction","Replicate:TransactionCategory","Replicate:User","Restore:Announcement","Restore:Complaint","Restore:Dues","Restore:DuesPayment","Restore:LetterRequest","Restore:LetterType","Restore:Transaction","Restore:TransactionCategory","Restore:User","RestoreAny:Announcement","RestoreAny:Complaint","RestoreAny:Dues","RestoreAny:DuesPayment","RestoreAny:LetterRequest","RestoreAny:LetterType","RestoreAny:Transaction","RestoreAny:TransactionCategory","RestoreAny:User","Update:Announcement","Update:Complaint","Update:Dues","Update:DuesPayment","Update:LetterRequest","Update:LetterType","Update:Transaction","Update:TransactionCategory","Update:User","View:Announcement","View:Complaint","View:Dues","View:DuesPayment","View:IncomeExpenseChart","View:KasStatsOverview","View:LetterRequest","View:LetterType","View:Transaction","View:TransactionCategory","View:User","ViewAny:Announcement","ViewAny:Complaint","ViewAny:Dues","ViewAny:DuesPayment","ViewAny:LetterRequest","ViewAny:LetterType","ViewAny:Transaction","ViewAny:TransactionCategory","ViewAny:User","ViewAny:Family","View:Family","Create:Family","Update:Family","Delete:Family","DeleteAny:Family","Restore:Family","ForceDelete:Family","ForceDeleteAny:Family","RestoreAny:Family","Replicate:Family","Reorder:Family","ViewAny:Resident","View:Resident","Create:Resident","Update:Resident","Delete:Resident","DeleteAny:Resident","Restore:Resident","ForceDelete:Resident","ForceDeleteAny:Resident","RestoreAny:Resident","Replicate:Resident","Reorder:Resident","View:CommunityStatsOverview","View:FundDistributionChart"]},{"name":"warga","guard_name":"web","permissions":[]}]';
        $directPermissions = '[]';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
