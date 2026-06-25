<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipRequest;
use App\Http\Requests\UpdateMembershipRequest;
use App\Http\Requests\ViewMembershipsRequest;
use App\Models\Membership;
use App\Services\AuditLogService;
use App\Services\DepartmentService;
use App\Services\MembershipService;
use App\Services\RoleService;
use Illuminate\Support\Facades\Log;

class MembershipController extends Controller
{
    protected MembershipService $membershipService;

    public function __construct(MembershipService $membershipService){
        $this->membershipService = $membershipService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(ViewMembershipsRequest $request){
        $perPage = $request->input('limit', 10);
        $search = $request->input('search', '');
        logger('index(MembershipController):,search,perPage',[$search,$perPage]);
        try {
            $memberships = $this->membershipService->getAllMembershipsWithPagination([$perPage,$search]);
            
            return response()->json([
            'success' => true,
            'data' => [
                'items' => $memberships->items(),
                'pagination' => [
                    'current_page' => $memberships->currentPage(),
                    'last_page' => $memberships->lastPage(),
                    'per_page' => $memberships->perPage(),
                    'total' => $memberships->total(),
                ]
            ],
            'message' => 'Memberships retrieved successfully'
        ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve memberships'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMembershipRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Membership $membership){
        $this->authorize('view',$membership);
        logger()->info('Before view in show controller :', ['membership_id' => $membership->id]);
        try{
            $membershipData = Membership::with(['account', 'department', 'role'])->findOrFail($membership->id);

            app(AuditLogService::class)->record($membership, 'viewed', [
                'description' => 'Membership viewed',
                'metadata' => ['table' => $membership->getTable()],
            ]);
            logger('(show-membership-controller)membershipData: ',[$membershipData]);
            $departmentMap = app(DepartmentService::class)->getDepartmentMap(); 
            logger('(show-membership-controller)departmentMap: ',[$departmentMap]);
            $roleMap = app(RoleService::class)->getRoleMap();            
            logger('(show-membership-controller)roleMap: ',[$roleMap]);
            return response()->json([
                'success' => true,
                'data' => $membershipData,
                'department_list' => $departmentMap,
                'role_list' => $roleMap,
                'message' => 'Membership retrieved successfully'
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Membership not found'
            ], 404);
        }        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function Status(Membership $membership){
        $this->authorize('update',$membership);
        //switchStatus
        try {
                $updatedStatus = $this->membershipService->switchStatus(
                $membership
            );

            return response()->json([
                'success' => true,
                'data' => $updatedStatus,
                'message' => 'Membership status updated successfully'
            ], 200); // Use 200 for update, not 201
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to status update membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(UpdateMembershipRequest $request, Membership $membership){
        try {
                $updatedMembership = $this->membershipService->updateMembership(
                $request->validated(),
                $membership
            );

            return response()->json([
                'success' => true,
                'data' => $updatedMembership,
                'message' => 'Membership updated successfully'
            ], 200); // Use 200 for update, not 201
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Membership $membership){
        $this->authorize('delete',$membership);
        logger()->info('Before delete in destroy controller :', [
            'membership_id' => $membership->id,
            'deleted_at' => $membership->deleted_at,
            'exists' => $membership->exists
        ]);
        $this->membershipService->deleteMembership($membership);
        return response()->json([
            'success' => true,
            'message' => 'Membership deleted successfully'
        ], 201);
    }
}
