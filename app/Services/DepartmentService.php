<?php
namespace App\Services;

use App\Events\DepartmentDeleted;
use App\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentService{
    public function createDepartment(array $data, $parentId){
        return DB::transaction(function () use ($data, $parentId) {
            $nextPath = $this->getNextPathNumber($parentId);
            
            // Convert parent_id properly
            $parentIdForDb = ($parentId === null || $parentId === 'null' || $parentId === 0) 
                ? null 
                : (int)$parentId;
            
            // Log::info('createDepartment', [
            //     'parent_id' => $parentId,
            //     'parent_id_for_db' => $parentIdForDb,
            //     'next_path' => $nextPath,
            //     'next_path_type' => gettype($nextPath),
            //     'data' => $data
            // ]);
            
            $department = Department::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'organization_id' => (int)($data['organization_id'] ?? auth()->user()->organization_id),
                'parent_id' => $parentIdForDb,
                'path' => $nextPath,  // Now an integer
            ]);
            $this->getDepartmentList();
            $this->getDepartmentMap();
            // Log::info('Department created', [
            //     'id' => $department->id,
            //     'path' => $department->path,
            //     'path_type' => gettype($department->path),
            //     'parent_id' => $department->parent_id
            // ]);
            
            return $department;
        });
    }
    public function getDepartmentTree(){
        $departments = Department::orderBy('path')->get();
        
        $tree = [];
        $map = [];
        
        foreach ($departments as $dept) {
            $map[$dept->id] = [
                'id' => $dept->id,
                'title' => $dept->title,
                'path' => $dept->path,
                'children' => []
            ];
        }
        
        foreach ($departments as $dept) {
            if ($dept->parent_id === null) {
                $tree[] = &$map[$dept->id];
            } else {
                $map[$dept->parent_id]['children'][] = &$map[$dept->id];
            }
        }
        
        return $tree;
    }
    public function updateDepartment(Department $department, array $data): Department{
        return DB::transaction(function () use ($department, $data) {

            $department->update($data);
            Log::info('(Service)Department is updated', [
                'department_id' => $department->id,
                'title' => $department->title,
                'description' => $department->description,
                'status' => $department->status
            ]);
            $this->getDepartmentList();
            $this->getDepartmentMap();
            return $department->fresh();
        });//deleteDepartment
    }
    public function deleteDepartment(Department $department): array{
        $blockers = $department->canBeDeleted();
        
        if (!empty($blockers)) {
            return [
                'success' => false,
                'blocked' => true,
                'message' => 'Cannot delete this department because it has related records.',
                'dependencies' => $blockers
            ];
        }

        return DB::transaction(function () use ($department) {
            $parentId = $department->parent_id;
            $deletedPath = $department->path;
            
            $department->delete($department->id);
            Log::info('Department deleted', [
                'department_id' => $department->id,
                'title' => $department->title
            ]);
                        
            event(new DepartmentDeleted($department));
            $this->getDepartmentList();
            $this->getDepartmentMap();
            
            // Department::where('parent_id','=', $parentId,true)
            //     ->where('path', '>', $deletedPath)
            //     ->decrement('path');
            
            return [
                'success' => true,
                'message' => 'Department deleted successfully'
            ];
        });
    }
    public function getAllDepartmentsWithPagination(array $data): LengthAwarePaginator{
        //logger('(DepartmentService)parentId: ',$data['parentId']);
        $perPage = $data['perPage'] ?? 10;
        $search = $data['search'] ?? '';
        $depId = $data['parentId'];
        
        Log::info('DepartmentService: Fetching departments', [
            'perPage' => $perPage,
            'search' => $search,
            'depId' => $depId
        ]);
        
        $query = Department::orderBy('created_at', 'desc');
        if (!$depId) {
            $query->whereNull('parent_id');
        } 
        else {
            $query->where('parent_id', (int)$depId);
        }
        
        // Add search functionality
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $departments = $query->paginate($perPage);
        
        Log::info('DepartmentService: Results', [
            'total' => $departments->total(),
            'current_page' => $departments->currentPage(),
            'last_page' => $departments->lastPage()
        ]);
        return $departments;
    }
    public function getLastSibling($parentId){
        $query = Department::query();
        
        if ($parentId === null || $parentId === 'null') {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', (int)$parentId);
        }
        
        // Now path is integer, so sorting works correctly!
        $result = $query->orderBy('path', 'desc')->first();
        
        // Log::info('getLastSibling', [
        //     'parent_id' => $parentId,
        //     'last_path' => $result?->path,
        //     'last_path_type' => gettype($result?->path),
        //     'found' => $result ? true : false
        // ]);
        
        return $result;
    }
     public function getNextPathNumber($parentId){
        $lastSibling = $this->getLastSibling($parentId);
        
        if ($lastSibling) {
            // Path is now integer, so addition works correctly
            $nextPath = (int)$lastSibling->path + 1;
            
            // Log::info('getNextPathNumber - incrementing', [
            //     'parent_id' => $parentId,
            //     'last_path' => $lastSibling->path,
            //     'last_path_type' => gettype($lastSibling->path),
            //     'next_path' => $nextPath
            // ]);
            
            return $nextPath;
        }
        
        // Log::info('getNextPathNumber - first sibling', [
        //     'parent_id' => $parentId,
        //     'next_path' => 1
        // ]);
        
        return 1;
    }

    public function moveDepartment(Department $department, int $newPath): bool{
        if ($department->path == $newPath) {
            return true;
        }
        
        return DB::transaction(function () use ($department, $newPath) {
            $oldPath = $department->path;
            $parentId = $department->parent_id;
            
            if ($newPath > $oldPath) {
                // Moving right: shift items in between left
                Department::where('parent_id','=', $parentId,true)
                    ->whereBetween('path', [$oldPath + 1, $newPath])
                    ->decrement('path');
            } else {
                // Moving left: shift items in between right
                Department::where('parent_id','=', $parentId,true)
                    ->whereBetween('path', [$newPath, $oldPath - 1])
                    ->increment('path');
            }
            
            $department->path = $newPath;
            $department->save();
            
            return true;
        });
    }
    private function buildTree($departments, $parentId = null): array{
        $tree = [];
        
        foreach ($departments as $department) {
            if ($department->parent_id == $parentId) {
                $children = $this->buildTree($departments, $department->id);
                if ($children) {
                    $department->children = $children;
                }
                $tree[] = $department;
            }
        }
        
        return $tree;
    }

    public function getTree(int $organizationId): array{
        $departments = Department::where('organization_id', $organizationId)
            ->with('children')
            ->orderBy('path')
            ->get();
        
        return $this->buildTree($departments);
    }

    public function getDeletionPreview(Department $department): array{
        return [
            'department' => [
                'id' => $department->id,
                'title' => $department->title
            ],
            'dependencies' => $department->canBeDeleted(),
            'is_deletable' => $department->isDeletable()
        ];
    }
    // In DepartmentService
    public function getDepartmentList(): array{
        $key = 'departments_list';
        
        return Cache::remember($key, now()->addMinutes(30), function () {
            return Department::orderBy('title')
                ->where('status', 'active')
                ->get(['id', 'title'])
                ->toArray();
        });
    }
    public function getDepartmentMap(): array{
        $key = 'departments_map';

        return Cache::remember($key, now()->addMinutes(30), function () {
            $result = Department::where('status', 'active')
                ->orderBy('title')
                ->get(['id', 'title as description']) // انتخاب مستقیم ستون‌ها
                ->toArray(); // تبدیل Collection به آرایه
                logger('getDepartmentMap: ',[$result]);
                return $result;
        });
    }
    
}