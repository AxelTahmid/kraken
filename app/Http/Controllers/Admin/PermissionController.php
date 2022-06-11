<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
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
            'slug' => ['required', 'unique:permissions,slug', 'max:191'],
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
            Permission::with('roles')->where('slug', $slug)->firstOrFail(),
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
}
