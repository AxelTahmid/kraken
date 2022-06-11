<?php

namespace App\Http\Controllers\RBAC;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserPermissionController extends Controller
{
    /**
     * Access Control List ( ACL )
     * Grant, Revoke or Refresh permission of a user
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $form_data = $request->validate([
            '_action' => ['required', 'string', 'max:191', 'in:grant,revoke,refresh'],
            'user_id' => 'required|int',
            'permissions' => 'required|array',
        ]);

        $user = User::findOrFail($form_data['user_id']);

        switch ($form_data['_action']) {
            case 'refresh':
                return $this->successResponse(
                    $user->refreshPermissions($form_data['permissions']),
                    'User Permissions Refreshed.',
                    201
                );
            case 'grant':
                // dd($user->can($form_data['permissions']));
                // check if user does not have permission before assigning
                // can() wont work, returns total true/false
                return $this->successResponse(
                    $user->givePermissionsTo($form_data['permissions']),
                    'User Permissions Granted.',
                    201
                );
            case 'revoke':
                // needs proper validation
                return $this->successResponse(
                    $user->withdrawPermissionsTo($form_data['permissions']),
                    'User Permissions Revoked.',
                    201
                );
        };

        return $this->errorResponse(
            $user,
            'Action not performed.',
            400
        );
    }
}
