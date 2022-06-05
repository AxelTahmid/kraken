<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // $user = $request->user();
    // dd($user->hasRole('developer')); //will return true, if user has role
    // dd($user->givePermissionsTo('create-tasks'));// will return permission, if not null
    // dd($user->can('create-tasks')); // will return true, if user has permission

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
            'name' => 'string|max:191',
            'slug' => 'string|max:191',
            // 'role' => 'string|max:191',
        ]);
        $permission = Permission::where('slug', $slug)->firstOrFail();
        $message = 'Permision Found.';

        if (isset($form_data['name'])  || isset($form_data['slug'])) {
            $permission->update([
                'name' => $form_data['name'],
                'slug' => $form_data['slug'],
            ]);
            $message = 'Permission Updated.';
        }
        // if (isset($form_data['role'])) {
        //     $role = Role::where('slug', $form_data['role'])->firstOrFail();
        //     $permission->roles()->attach($role);
        //     $message = 'Permission Updated, Role Attached.';
        // };

        return $this->successResponse(
            $permission,
            $message
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
}
