<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of Permissions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->successResponse(
            Permission::get(),
            'Permissions List Fetched'
        );
    }

    /**
     * Store a newly created Permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $form_data = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'required|unique:permissions,slug|max:191',
        ]);
        $permission = Permission::create($form_data);

        return $this->successResponse(
            $permission,
            'Permission Created.'
        );
    }

    /**
     * Display the specified permission by slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        return $this->successResponse(
            Permission::where('slug', $slug)->with('roles')->firstOrFail(),
            'Permission Fetched'
        );
    }

    /**
     * Update existing permissions by slug.
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

        $permission = Permission::where('slug', $slug)->firstOrFail();
        $permission->update([
            'name' => $form_data['name'],
            'slug' => $form_data['slug'],
        ]);

        // take in permissions array
        // givePermissionsTo or withdrawPermissionsTo or refreshPermissions

        return $this->successResponse(
            $permission,
            'Permission Updated.'
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
        $permission = Permission::where('slug', $slug)->firstOrFail();
        Permission::destroy($permission->id);

        return $this->successResponse(
            $permission,
            'Permission Deleted.',
        );
    }

    /**
     * Grant permission to a user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function grant(Request $request)
    {
        $form_data = $request->validate([
            'user_id' => 'required|int',
            'permissions' => 'required|array',
        ]);

        $user = User::findOrFail($form_data['user_id']);
        $res = $user->givePermissionsTo($form_data['permissions']);

        return $this->successResponse(
            $res,
            'User Permissions Granted.',
        );
    }

    /**
     * Remove permission to a user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function revoke(Request $request)
    {
        $form_data = $request->validate([
            'user_id' => 'required|int',
            'permissions' => 'required|array',
        ]);

        $user = User::findOrFail($form_data['user_id']);
        $res = $user->withdrawPermissionsTo($form_data['permissions']);

        return $this->successResponse(
            $res,
            'User Permissions Revoked.',
        );
    }

    /**
     * Refresh permission to a user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refesh(Request $request)
    {
        $form_data = $request->validate([
            'user_id' => 'required|int',
            'permissions' => 'required|array',
        ]);

        $user = User::findOrFail($form_data['user_id']);
        $res = $user->refreshPermissions($form_data['permissions']);

        return $this->successResponse(
            $res,
            'User Permissions Refreshed.',
        );
    }
}
