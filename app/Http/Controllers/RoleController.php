<?php

namespace App\Http\Controllers;

use App\DataTables\RoleDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Repositories\RoleRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\RoleHasPermissionRepository;

class RoleController extends AppBaseController
{
    /** @var RoleRepository $roleRepository*/
    private $roleRepository;
    private $roleHasPermissionRepository;


    public function __construct(RoleRepository $roleRepo, RoleHasPermissionRepository $roleHasPermissionRepo)
    {
        $this->roleRepository = $roleRepo;
        $this->roleHasPermissionRepository = $roleHasPermissionRepo;
    }

    /**
     * Display a listing of the Role.
     *
     * @param RoleDataTable $roleDataTable
     *
     * @return Response
     */
    public function index(RoleDataTable $roleDataTable)
    {
        return $roleDataTable->render('roles.index');
    }

    /**
     * Show the form for creating a new Role.
     *
     * @return Response
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created Role in storage.
     *
     * @param CreateRoleRequest $request
     *
     * @return Response
     */
    public function store(CreateRoleRequest $request)
    {
        $input = $request->all();

        $permission_id = $input["permission_id"] ?? "";
        
        unset($input["permission_id"]);

        $role = $this->roleRepository->create($input);

        $rolePermission = [
            "role_id" => $role["id"],
            "permission_id" => $permission_id
        ];
        
        $roleHasPermission = $this->roleHasPermissionRepository->create($rolePermission);

        Flash::success('Role saved successfully.');

        return redirect(route('roles.index'));
    }

    /**
     * Display the specified Role.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        $roleHasPermission = $this->roleHasPermissionRepository->where('role_id', $id)->first();  // Use Eloquent 'where' and 'first'

        $role->permission_name = $roleHasPermission->permission->name ?? "";


        return view('roles.show')->with('role', $role);
    }

    /**
     * Show the form for editing the specified Role.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }
        
        $roleHasPermission = $this->roleHasPermissionRepository->where('role_id', $id)->first();  // Use Eloquent 'where' and 'first'

        $role->permission_id = $roleHasPermission->permission_id ?? "";

        return view('roles.edit')->with('role', $role);
    }

    /**
     * Update the specified Role in storage.
     *
     * @param int $id
     * @param UpdateRoleRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateRoleRequest $request)
    {
        $id = Crypt::decrypt($id);
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        $role = $this->roleRepository->update($request->all(), $id);

        $roleHasPermission = $this->roleHasPermissionRepository->where('role_id', $id)->first();  // Use Eloquent 'where' and 'first'

        if($roleHasPermission)
        {
            $rolePermission = [
                "permission_id" => $request["permission_id"]
            ];

            $this->roleHasPermissionRepository->update($rolePermission, $roleHasPermission->id);
        }
        else
        {
            $rolePermission = [
                "model_id" => $user["id"],
                "role_id" => $request["role_id"]
            ];
    
            $roleHasPermission = $this->roleHasPermissionRepository->create($rolePermission);
        }

        Flash::success('Role updated successfully.');

        return redirect(route('roles.index'));
    }

    /**
     * Remove the specified Role from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $role = $this->roleRepository->find($id);

        if (empty($role)) {
            Flash::error('Role not found');

            return redirect(route('roles.index'));
        }

        $this->roleRepository->delete($id);

        Flash::success('Role deleted successfully.');

        return redirect(route('roles.index'));
    }
}
