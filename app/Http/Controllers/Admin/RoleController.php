<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\User;
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
     * give permission to role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function attachPermission(Request $request)
    {
        //     $role = Role::where('slug', $form_data['role'])->firstOrFail();
        //     $role->permissions()->attach($permission);
        //     $message = 'Permission Updated, Role Attached.';

        // _action , role_slug, permission array
        // validate role_slug exists, take permission array, refresh it 
    }


    /**
     * Grant or revoke role to user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function manage(Request $request)
    {
        $form_data = $request->validate([
            '_action' => ['required', 'string', 'max:191', 'in:grant,revoke,change'],
            'user_id' => 'required|int',
            'role_slug' => 'required|string',
        ]);

        $user = User::findOrFail($form_data['user_id']);


        if ($form_data['_action'] == 'grant') {

            if ($user->hasRole($form_data['role_slug'])) {
                return $this->errorResponse(
                    'User Role Exists.',
                    404
                );
            }
            $role = Role::where('slug', $form_data['role_slug'])->with('permissions')->firstOrFail();

            dd($role);

            $user->roles()->attach($role);
            // $user->refreshPermissions($role->permissions())

            return $this->successResponse(
                $user,
                $form_data['role_slug'] . ' Role Granted.',
            );
        }

        if ($form_data['_action'] == 'revoke') {
            if (!$user->hasRole($form_data['role_slug'])) {
                return $this->errorResponse(
                    'User does not have Role.',
                    404
                );
            }

            $role = Role::where('slug', $form_data['role_slug'])->with('permissions')->firstOrFail();

            $user->roles()->detach($role);
            // $user->withdrawPermissionsTo($role->permissions())

            return $this->successResponse(
                $user,
                $form_data['role_slug'] . ' Role Revoked.',
            );
        }
    }
}
