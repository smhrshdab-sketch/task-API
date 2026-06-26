<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Requests\ViewDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class DepartmentController extends Controller{
    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService){
        $this->departmentService = $departmentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(ViewDepartmentRequest $request){
        $perPage = $request->input('limit', 30);
        $search = $request->input('search', '');
        $parentId = $request->input('parentId', null);
        Log::info('DepartmentController@index called', [
            'per_page' => $perPage,
            'search' => $search,
            'parent_id' => $parentId
        ]);
        logger('index(controller_department): search,perPage,parentId',[$search,$perPage,$parentId]);
        try {
            $departments = $this->departmentService->getAllDepartmentsWithPagination([
                'perPage' => $perPage,
                'search' => $search,
                'parentId' => $parentId
            ]);
            
            return response()->json([
            'success' => true,
            'data' => $departments->items(),        // Current page items
            'current_page' => $departments->currentPage(),
            'last_page' => $departments->lastPage(),
            'per_page' => $departments->perPage(),
            'total' => $departments->total(),
            'from' => $departments->firstItem(),
            'to' => $departments->lastItem()
        ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departmenta'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){
        $auth = $this->authorize('create', Department::class);
        // $membership = current_membership();
        // if (!$membership) {
        //     return false;
        // }
        // $membership->account->can('create', new Department());
        logger('CREATE department controller authorize is : ',[$auth]);
        // User sees form only if authorized
        return response()->json(['message' => 'Ready to create']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request){
        try {
            // Get parent_id from request, convert empty string or 0 to null
            $parentId = $request->input('parent_id');
            
            // Convert falsy values to null for root departments
            if (empty($parentId) || $parentId === 0 || $parentId === '0') {
                $parentId = null;
            }
            
            $department = $this->departmentService->createDepartment(
                $request->validated(),
                $parentId
            );
            
            return response()->json([
                'success' => true,
                'data' => $department,
                'message' => 'Department created successfully'
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Failed to create department: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ViewDepartmentRequest $request,Department $department){
        $perPage = $request->input('limit', 10);
        $search = $request->input('search', '');
        $parentId = $department->id;
        logger('show(DepartmentController):,search,perPage',[$search,$perPage,$parentId,$department]);
        try {
            // Authorize - Check if user can view this department
            
            $departments = $this->departmentService->getAllDepartmentsWithPagination([
                'perPage' => $perPage,
                'search' => $search,
                'parentId' => $parentId
            ]);
            
            return response()->json([
                'success' => true,
                'department' => $department,
                'message' => 'Department retrieved successfully',
                'sub_departments' => $departments->items(),
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
                'from' => $departments->firstItem(),
                'to' => $departments->lastItem()
            ], 200);
            
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this department',
                'error' => $e->getMessage()
            ], 403);
            
        } 
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department){
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department){
        $department = $this->departmentService->updateDepartment(
            $department,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => $department,
            'message' => 'Department is updated successfully'
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department){
        $this->authorize('delete',$department);
        logger()->info('Before delete in destroy controller :', [
            'department_id' => $department->id
        ]);
        $result = $this->departmentService->deleteDepartment($department);
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'dependencies' => $result['dependencies'] ?? []
            ], 409);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully'
        ]);
    }

    public function deletePreview(Department $department){
        $preview = $this->departmentService->getDeletionPreview($department);
        
        if ($preview['is_deletable']) {
            return response()->json([
                'success' => true,
                'deletable' => true,
                'message' => 'This department can be deleted. No related records found.'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'deletable' => false,
            'message' => 'This department cannot be deleted because it has related records.',
            'dependencies' => $preview['dependencies']
        ], 409);
    }
    // app/Http/Controllers/DepartmentController.php

    public function colleague(Request $request){
         logger()->info('👓 You are in colleague controller : 💥'); 
        try {
            $dep = $request::header('X-Department-Id');
            
            if (!$dep) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department ID not provided'
                ], 400);
            }
            logger('Request: ',[$request]);
            ///logger('header: ',[$dep ]);
            logger('dep: ',[$dep]);
            // Find the department
            $department = Department::find($dep);            
            
            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department not found'
                ], 404);
            }
            logger('departments: ',[$department]);
            // Get memberships with their accounts
            $memberships = $department->memberships()
                ->with('account')  // Eager load account relationship
                ->where('status', 'active')
                ->get();
            logger('memberships: ',[$memberships]);
            $result = [];
            foreach ($memberships as $membership) {
                $result[] = [
                    'id' => $membership->id,
                    //'account_id' => $membership->account_id,
                    'title' => $membership->account->name ?? 'Unknown',
                    //'email' => $membership->account->email ?? '',
                    //'avatar_path' => $membership->account->avatar_path ?? null,
                    //'role' => $membership->role->title ?? 'No Role'
                ];
            }
            
            logger('(department Controller) colleague: ', [$result]);
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'count' => count($result)
            ]);
            
        } catch (\Exception $e) {
            logger()->error('Error in colleague method: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch colleagues: ' . $e->getMessage()
            ], 500);
        }
    }
    public function subDepartments(Request $request){
        logger()->info('👓 You are in subDepartments controller : 💥'); 
        try{
            $dep = $request::header('X-Department-Id');
            
            if (!$dep) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department ID not provided'
                ], 400);
            }
            //logger('Request: ',[$request]);
            ///logger('header: ',[$dep ]);
            //logger('dep: ',[$dep]);
            // Find the department
            $departments = Department::find($dep);            
            
            if (!$departments) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department not found'
                ], 404);
            }
            //logger('departments: ',[$departments]);
            $children = $departments->children()->get();
            $result = [];
            foreach ($children as $child) {
                $result[] = [
                    'id' => $child->id,
                    'title' => $child->title ?? 'Unknown'
                ];
            }
            logger('sub departments: ',[$result]);
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        }
        catch(\Exception $e){
            //
        }
    }
}
