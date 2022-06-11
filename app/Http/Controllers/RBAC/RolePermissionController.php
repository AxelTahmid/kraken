<?php

namespace App\Http\Controllers\RBAC;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RolePermissionController extends Controller
{
    /**
     * Access Control List ( ACL )
     * Grant, Revoke or Refresh permissions to a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $form_data = $request->validate([
            '_action' => ['required', 'string', 'max:191', 'in:grant,revoke,refresh'],
            'role_slug' => 'required|string|max:191',
            'permissions' => 'required|array',
        ]);

        $role = Role::with('permissions')->where('slug', $form_data['role_slug'])->firstOrFail();
        $permissions = Permission::with('roles')->whereIn('slug', $form_data['permissions'])->get();

        if (!count($permissions)) {
            return $this->errorResponse(
                'No Matching Permissions Found.',
                400
            );
        };

        switch ($form_data['_action']) {
            case 'refresh':
                return $this->refresh($permissions, $role);
            case 'grant':
                return $this->grant($permissions, $role);
            case 'revoke':
                return $this->revoke($permissions, $role);
        };

        return $this->errorResponse(
            'Action Not Performed.',
            400
        );
    }


    /**
     * Refresh Permission of a Role.
     *
     * @param  \App\Models\Permission  $permissions
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    protected function refresh($permissions, $role)
    {
        $role->permissions()->detach();
        $role->permissions()->saveMany($permissions);

        return $this->successResponse(
            Role::with('permissions')->findOrFail($role->id),
            'Permissions Refreshed for ' . $role->name . '.',
            201
        );
    }

    /**
     * Grant Permission to a Role.
     *
     * @param  \App\Models\Permission  $permissions
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    protected function grant($permissions, $role)
    {
        foreach ($permissions as $p) {
            if ($p->roles->contains($role)) {
                return $this->errorResponse(
                    'Permission: ' . $p->slug . '; exists for ' . $role->name,
                    400
                );
            }
        }
        $role->permissions()->saveMany($permissions);

        return $this->successResponse(
            Role::with('permissions')->findOrFail($role->id),
            'Permissions Granted to ' . $role->name . '.',
            201
        );
    }

    /**
     * Revoke Permission of a Role.
     *
     * @param  \App\Models\Permission  $permissions
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    protected function revoke($permissions, $role)
    {
        foreach ($permissions as $p) {
            if (!$p->roles->contains($role)) {
                return $this->errorResponse(
                    $role->name . ' does not have permission: ' . $p->slug,
                    400
                );
            }
        }
        $role->permissions()->detach($permissions);

        return $this->successResponse(
            Role::with('permissions')->findOrFail($role->id),
            'Permissions Revoked for ' . $role->name . '.',
        );
    }
}
