<?php

namespace App\Http\Controllers\RBAC;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleAccessController extends Controller
{

    /**
     * Role Based Access Control ( RBAC )
     * Grant, Revoke & Change role of a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $form_data = $request->validate([
            '_action' => ['required', 'string', 'max:191', 'in:grant,revoke,change'],
            'user_id' => 'required|int',
            'role_slug' => 'required|string|max:191',
        ]);

        $user = User::with('roles')->findOrFail($form_data['user_id']);
        $role = Role::where('slug', $form_data['role_slug'])->firstOrFail();

        if ($form_data['_action'] == 'grant') {
            return $this->grant($user, $role, $form_data['role_slug']);
        }

        if ($form_data['_action'] == 'revoke') {
            return $this->revoke($user, $role, $form_data['role_slug']);
        }

        if ($form_data['_action'] == 'change') {
            return $this->change($user, $role, $form_data['role_slug']);
        }

        return $this->errorResponse(
            'Action Not Performed.',
            400
        );
    }

    /**
     * Grant Role to a User.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    protected function grant($user, $role, $slug)
    {
        if (!$user->hasRole($slug)) {

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

    /**
     * Revoke Role of a User.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    protected function revoke($user, $role, $slug)
    {
        if ($user->hasRole($slug)) {

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

    /**
     * Change Role of a User.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Role  $role
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    protected function change($user, $role, $slug)
    {
        $prev_role_slug = !empty($user->roles[0]) && $user->roles[0]->slug ? $user->roles[0]->slug : null;

        if (!$user->hasRole($slug)) {

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
}
