<?php

namespace Mate\Roles\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RolePermissions extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'role_permissions';

    protected $fillable = [
        'permission',
        'role_id',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('roles.tables_names.role_permissions', 'role_permissions');
    }

    protected static function getDefaultMatrix(): array
    {
        $roles = Role::all();
        $permissions = config('roles.permissions');

        $matrix = [];
        foreach ($roles as $role) {
            $matrix[$role->id] = [];
            foreach ($permissions as $permission) {
                $matrix[$role->id][$permission] = false;
            }
        }

        return $matrix;
    }

    /**
     * An array of roles with their associated permissions
     *
     * @return array
     *               An array with the role id as index of an array of all permissions as index and a boolean as value.
     *
     *   Example:
     *     1 => [
     *      'manage users' => true,
     *      'manage roles' => true,
     *     ],
     *     2 => [
     *      'manage users' => true,
     *      'manage roles' => false,
     *     ]
     */
    public static function getMatrix(): array
    {
        $rolePermissions = RolePermissions::all();
        $matrix = self::getDefaultMatrix();

        foreach ($rolePermissions as $rolePermission) {
            $matrix[$rolePermission->role_id][$rolePermission->permission] = true;
        }

        return $matrix;
    }

    /**
     * Updates the roles permissions matrix based in the array
     * sent from the permission grid table.
     *
     *
     * @throws \Exception
     */
    public static function updateMatrix(array $rolePermissions): void
    {
        DB::beginTransaction();
        try {

            $roles = Role::all();
            foreach ($roles as $role) {
                $forCreate = [];
                $forDelete = [];
                $newRolePermissions = [];

                if (! empty($rolePermissions[$role->id])) {
                    $newRolePermissions = $rolePermissions[$role->id];
                }

                foreach ($newRolePermissions as $permission => $value) {
                    //Transforming the array to be used in the createMany method.
                    $forCreate[$permission] = ['permission' => $permission];
                }

                $newRolePermissionsNames =array_is_list($newRolePermissions)
                    ? $newRolePermissions
                    : array_keys($newRolePermissions);

                foreach ($role->permissions as $permission) {
                    if (! in_array($permission->permission, $newRolePermissionsNames)) {
                        //Added for deletion since is not in the updated list.
                        $forDelete[] = $permission->permission;
                    } else {
                        //No need to create, already inserted.
                        unset($forCreate[$permission->permission]);
                    }
                }


                //Delete the ones that are not in the updated list.
                $role->permissions()->whereIn('permission', $forDelete)->delete();

                //Create the ones that are not in the database.
                $role->permissions()->createMany($forCreate);

            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
