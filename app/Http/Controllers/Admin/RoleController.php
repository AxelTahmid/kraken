<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $slug)
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
     * @param  string  $slug
     * @param  array  $permission
     * @return \Illuminate\Http\Response
     */
    public function attachPermission(Request $request, $slug)
    {
        //     $role = Role::where('slug', $form_data['role'])->firstOrFail();
        //     $role->permissions()->attach($permission);
        //     $message = 'Permission Updated, Role Attached.';
    }

    /**
     * Update permission to role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @param  array  $permission
     * @return \Illuminate\Http\Response
     */
    public function revokePermission(Request $request, $slug)
    {
        //
    }

    /**
     * Update role of user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function manageUserRole(Request $request, $slug)
    {
        // attach or detach or update role
    }
}
