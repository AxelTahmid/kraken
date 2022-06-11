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
