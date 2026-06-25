<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\ViewRoleRequest;
use App\Models\Role;
use App\Services\AuditLogService;
use App\Services\RoleService;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService){
        $this->roleService = $roleService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(ViewRoleRequest $request){
        //logger(' ===== RoleController ======== index ===== ViewRoleRequest : ',[$request]);
        try {
            $roles = $this->roleService->getAllRolesWithoutPagination();
            
            return response()->json([
                'success' => true,
                'data' => $roles,
                'total' => $roles->count(),
                'message' => 'Roles retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Roles'
            ], 500);
        }
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request){
        Log::info('🔵🔵🔵 CREATE Role CONTROLLER REACHED 🔵🔵🔵');
        Log::info('Request data: ', $request->validated());
        $role = $this->roleService->createRole(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $role,
            'message' => 'Role is created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role){
         //logger(' ===== RoleController ======== show ===== role : : ',[$role]);
        $this->authorize('view', $role);
        app(AuditLogService::class)->record($role, 'viewed', [
            'description' => 'Role viewed',
            'metadata' => ['table' => $role->getTable()],
        ]);
        return response()->json([
            'success' => true,
            'data' => $role,
            'message' => 'Role is retrieved successfully'
        ], 201);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role){
        Log::info('🔵🔵🔵 UPDATE Role CONTROLLER REACHED 🔵🔵🔵');
        Log::info('Role ID: ' . $role->id);
        Log::info('Request data: ', $request->validated());
        try {
                $updatedRole = $this->roleService->updateRole(
                $request->validated(),
                $role
            );

            return response()->json([
                'success' => true,
                'data' => $updatedRole,
                'message' => 'Role updated successfully'
            ], 200); // Use 200 for update, not 201
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role){
        $this->authorize('delete',$role);
        logger()->info('Before delete in destroy controller :', [
            'role_id' => $role->id,
            'deleted_at' => $role->deleted_at,
            'exists' => $role->exists
        ]);
        $this->roleService->deleteRole($role);
        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ], 201);
    }
}
