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
     * Grant or Revoke permissions to a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function managePermissions(Request $request)
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
            'role_slug' => 'required|string|max:191',
        ]);

        $user = User::findOrFail($form_data['user_id']);
        $role = Role::where('slug', $form_data['role_slug'])->firstOrFail();
        // $role = Role::where('slug', $form_data['role_slug'])->with('permissions')->firstOrFail();

        // dd($user, $role, $user_role);
        dd();


        if ($form_data['_action'] == 'grant') {

            if (!$user->hasRole($form_data['role_slug'])) {
                return $this->successResponse(
                    $user->roles()->attach($role),
                    $form_data['role_slug'] . ' Role Granted.',
                    201
                );
            };

            return $this->errorResponse(
                'User Role Exists.',
                400
            );
        }

        if ($form_data['_action'] == 'revoke') {

            if ($user->hasRole($form_data['role_slug'])) {
                return $this->successResponse(
                    $user->roles()->detach($role),
                    $form_data['role_slug'] . ' Role Revoked.',
                );
            }

            return $this->errorResponse(
                'User does not have Role.',
                400
            );
        }

        if ($form_data['_action'] == 'change') {

            $user = User::with('roles')->findorFail($form_data['user_id']);
            $prev_role_slug = $user->roles[0] && $user->roles[0]->slug ? $user->roles[0]->slug : null;

            if (!$user->hasRole($form_data['role_slug'])) {

                if ($prev_role_slug !== null) {
                    $prev_role = Role::where('slug', $prev_role_slug)->firstOrFail();
                    $user->roles()->detach($prev_role);
                }

                return $this->successResponse(
                    $user->roles()->attach($role),
                    'Role Changed.',
                    201
                );
            }

            return $this->errorResponse(
                'User Already Has Role.',
                400
            );
        }
    }
}
