<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->successResponse(
            Role::with('permissions')->get(),
            'Roles List Fetched'
        );
    }

    /**
     * Store a newly created role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $form_data = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'required|unique:roles,slug|max:191',
        ]);
        $role = Role::create($form_data);

        return $this->successResponse(
            $role,
            'Role Created.'
        );
    }

    /**
     * Display the specified role.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        return $this->successResponse(
            Role::where('slug', $slug)->with('permissions')->firstOrFail(),
            'Role Fetched'
        );
    }

    /**
     * Update the specified role by slug.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $form_data = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'required|string|max:191',
        ]);

        $role = Role::where('slug', $slug)->firstOrFail();
        $role->update([
            'name' => $form_data['name'],
            'slug' => $form_data['slug'],
        ]);

        return $this->successResponse(
            $role,
            'Role Updated.'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $role = Role::where('slug', $slug)->firstOrFail();
        Role::destroy($role->id);

        return $this->successResponse(
            $role,
            'Role Deleted.',
        );
    }

    /**
     * Access Control List ( ACL )
     * Grant, Revoke or Refresh permissions to a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function _ACL(Request $request)
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
     * Role Based Access Control ( RBAC )
     * Grant, Revoke & Change role of a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function _RBAC(Request $request)
    {
        $form_data = $request->validate([
            '_action' => ['required', 'string', 'max:191', 'in:grant,revoke,change'],
            'user_id' => 'required|int',
            'role_slug' => 'required|string|max:191',
        ]);

        $user = User::findOrFail($form_data['user_id']);
        $role = Role::where('slug', $form_data['role_slug'])->firstOrFail();

        if ($form_data['_action'] == 'grant') {

            if (!$user->hasRole($form_data['role_slug'])) {

                $user->roles()->attach($role);
                return $this->successResponse(
                    User::with('roles')->findOrFail($user->id),
                    $role->name . ' Privilege Granted.',
                    201
                );
            };

            return $this->errorResponse(
                'User is already' . $role->name,
                400
            );
        }

        if ($form_data['_action'] == 'revoke') {

            if ($user->hasRole($form_data['role_slug'])) {

                $user->roles()->detach($role);
                return $this->successResponse(
                    User::with('roles')->findOrFail($user->id),
                    $role->name . ' Privilege Revoked.',
                );
            }

            return $this->errorResponse(
                'User is not ' . $role->name,
                400
            );
        }

        if ($form_data['_action'] == 'change') {

            $user = User::with('roles')->findorFail($form_data['user_id']);
            $prev_role_slug = $user->roles && $user->roles[0]->slug ? $user->roles[0]->slug : null;

            if (!$user->hasRole($form_data['role_slug'])) {

                if ($prev_role_slug !== null) {
                    $prev_role = Role::where('slug', $prev_role_slug)->firstOrFail();
                    $user->roles()->detach($prev_role);
                }

                $user->roles()->attach($role);
                return $this->successResponse(
                    User::with('roles')->findOrFail($user->id),
                    'Changed to ' . $role->name . ' Privilege.',
                    201
                );
            }

            return $this->errorResponse(
                'User Already Has ' . $role->name . ' Role.',
                400
            );
        }

        return $this->errorResponse(
            'Action Not Performed.',
            400
        );
    }
}
