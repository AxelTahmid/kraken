<?php

namespace App\Http\Controllers\RBAC;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PermissionAccessController extends Controller
{
    /**
     * Access Control List ( ACL )
     * Grant, Revoke or Refresh permissions to a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function manageRolePermissions(Request $request)
    {
        $form_data = $request->validate([
            '_action' => ['required', 'string', 'max:191', 'in:grant,revoke,refresh'],
            'role_slug' => 'required|string|max:191',
            'permissions' => 'required|array',
        ]);

        $role = Role::where('slug', $form_data['role_slug'])->firstOrFail();
        $permission_models = Permission::whereIn('slug', $form_data['permissions'])->with('roles')->get();

        if (!count($permission_models)) {
            return $this->errorResponse(
                'No Matching Permissions Found.',
                400
            );
        };

        if ($form_data['_action'] == 'refresh') {

            $role->permissions()->detach();
            $role->permissions()->saveMany($permission_models);

            return $this->successResponse(
                Role::with('permissions')->findOrFail($role->id),
                ' Permissions Refreshed for ' . $role->name . '.',
                201
            );
        };

        if ($form_data['_action'] == 'grant') {

            foreach ($permission_models as $permission) {
                if ($permission->roles->contains($role)) {
                    return $this->errorResponse(
                        'Permission: ' . $permission->slug . '; exists for ' . $role->name,
                        400
                    );
                }
            }
            $role->permissions()->saveMany($permission_models);

            return $this->successResponse(
                Role::with('permissions')->findOrFail($role->id),
                'Permissions Granted to ' . $role->name . '.',
                201
            );
        };

        if ($form_data['_action'] == 'revoke') {

            foreach ($permission_models as $permission) {
                if (!$permission->roles->contains($role)) {
                    return $this->errorResponse(
                        $role->name . ' does not have permission: ' . $permission->slug . '',
                        400
                    );
                }
            }
            $role->permissions()->detach($permission_models);

            return $this->successResponse(
                Role::with('permissions')->findOrFail($role->id),
                'Permissions Revoked for ' . $role->name . '.',
            );
        };
    }


    /**
     * Access Control List ( ACL )
     * Grant, Revoke or Refresh permission of a user
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function manageUserPermissions(Request $request)
    {
        $form_data = $request->validate([
            '_action' => ['required', 'string', 'max:191', 'in:grant,revoke,refresh'],
            'user_id' => 'required|int',
            'permissions' => 'required|array',
        ]);

        $user = User::findOrFail($form_data['user_id']);

        if ($form_data['_action'] == 'refresh') {
            return $this->successResponse(
                $user->refreshPermissions($form_data['permissions']),
                'User Permissions Refreshed.',
                201
            );
        }

        if ($form_data['_action'] == 'grant') {
            // dd($user->can($form_data['permissions']));
            // check if user does not have permission before assigning
            // can() wont work, returns total true/false
            return $this->successResponse(
                $user->givePermissionsTo($form_data['permissions']),
                'User Permissions Granted.',
                201
            );
        }

        if ($form_data['_action'] == 'revoke') {
            // needs proper validation
            return $this->successResponse(
                $user->withdrawPermissionsTo($form_data['permissions']),
                'User Permissions Revoked.',
                201
            );
        }

        return $this->errorResponse(
            $user,
            'Action not performed.',
            400
        );
    }
}
