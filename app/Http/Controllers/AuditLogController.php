<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuditLogController extends Controller{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService){
        $this->auditLogService = $auditLogService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){
        $headers = ['subject_type','subject_id','causer_id','event','description','batch_id','ip_address','user_agent','url','method','metadata','created_at','updated_at'];
        $title = 'LOGS';
        //--------------
        $perPage = $request->input('limit', 10);
        $search = $request->input('search', '');
        Log::info('AuditLogController@index called', [
            'per_page' => $perPage,
            'search' => $search,
        ]);
        logger('index(controller_auditLog): search,perPage,parentId',[$search,$perPage]);
        try {
            $auditLogs = $this->auditLogService->getAllAuditLogsWithPagination([
                'perPage' => $perPage,
                'search' => $search,
            ]);
            
            return response()->json([
            'success' => true,
            'data' => $auditLogs->items(),
            'headers' => $headers,
            'title' => $title,
            'current_page' => $auditLogs->currentPage(),
            'last_page' => $auditLogs->lastPage(),
            'per_page' => $auditLogs->perPage(),
            'total' => $auditLogs->total(),
            'from' => $auditLogs->firstItem(),
            'to' => $auditLogs->lastItem()
        ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve auditLoga'
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AuditLog $auditLog)
    {
        app(AuditLogService::class)->record($auditLog, 'viewed', [
            'description' => 'AuditLog viewed',
            'metadata' => ['table' => $auditLog->getTable()],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AuditLog $auditLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuditLog $auditLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditLog $auditLog)
    {
        //
    }
}
