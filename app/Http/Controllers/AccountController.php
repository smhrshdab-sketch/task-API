<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\ViewAccountsRequest;
use App\Models\Account;
use App\Services\AccountService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountController extends Controller
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService){
        $this->accountService = $accountService;
    }
    /**
     * Display a listing of accounts (paginated)
     */
    public function indeex(ViewAccountsRequest $request){
        Log::info('AccountController@index reached', [
                'account' => current_membership()->account->name,
                'request_data' => $request->validated()
            ]);
        try {
            $accounts = $this->accountService->getAllAccountsWithoutPagination();
            
            return response()->json([
                'success' => true,
                'data' => $accounts->items(),
                'message' => 'Accounts retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Failed to retrieve accounts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display all accounts without pagination
     */
    public function index(ViewAccountsRequest $request){
        try {
            $accounts = $this->accountService->getAllAccountsWithoutPagination();
            
            return response()->json([
                'success' => true,
                'data' => $accounts,
                'total' => $accounts->count(),
                'message' => 'Accounts retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve accounts'
            ], 500);
        }
    }
    
    /**
     * Display a specific account
     */
    public function me(){
        logger('me function in AccountController');
        try {
            //$account = $this->accountService->getAccountById($id);
            $account = Response()->json(auth('api')->user());                        
            if (!$account) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account not found'
                ], 404);
            }
            logger('me me',[$account]);
            return response()->json([
                'success' => true,
                'data' => $account,
                'message' => 'Account retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve account'
            ], 500);
        }
    }
    public function show(Account $account){
        $this->authorize('view',$account);
        logger()->info('Before view in show controller :', ['account_id' => $account->id]);
        app(AuditLogService::class)->record($account, 'viewed', [
            'description' => 'Account viewed',
            'metadata' => ['table' => $account->getTable()],
        ]);
        return response()->json([
            'success' => true,
            'data' => $account,
            'message' => 'Account retrieved successfully'
        ], 201);
        
    }
    /**
     * Get account statistics
     */
    public function statistics(){
        try {
            $statistics = $this->accountService->getAccountStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Statistics retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }
    
    /**
     * Get trashed (soft-deleted) accounts
     */
    public function trashed(ViewAccountsRequest $request){
        try {
            $accounts = $this->accountService->getTrashedAccounts($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $accounts->items(),
                'meta' => [
                    'current_page' => $accounts->currentPage(),
                    'last_page' => $accounts->lastPage(),
                    'per_page' => $accounts->perPage(),
                    'total' => $accounts->total(),
                ],
                'message' => 'Trashed accounts retrieved successfully'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve trashed accounts'
            ], 500);
        }
    }
     //===========
    public function register(StoreAccountRequest $request){
        try {
            $result = $this->accountService->createAccountWithToken($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Registered & logged in successfully',
                'access_token' => $result['token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'user' => [
                    'id' => $result['account']->id,
                    'name' => $result['account']->name,
                    'email' => $result['account']->email,
                    'address' => $result['account']->address,
                    'phone' => $result['account']->phone,
                    'org_id' => $result['account']->organization_id,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
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
    public function store(StoreAccountRequest $request){
        $account = $this->accountService->createAccount(
            $request->validated()
        );
        return response()->json([
            'success' => true,
            'data' => $account,
            'message' => 'Account is created successfully'
        ], 201);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccountRequest $request, Account $account){
        try {
            // Get validated data
            $validatedData = $request->validated();
            
            // Get avatar file if present
            $avatarFile = $request->hasFile('avatar') ? $request->file('avatar') : null;
            
            // Update account
            $updatedAccount = $this->accountService->updateAccount(
                $account,
                $validatedData,
                $avatarFile
            );
            
            // Get avatar URL for response
            $avatarUrl = $this->accountService->getAvatarUrl($updatedAccount->avatar_path);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $updatedAccount->id,
                    'name' => $updatedAccount->name,
                    'email' => $updatedAccount->email,
                    'bio' => $updatedAccount->bio,
                    'phone' => $updatedAccount->phone,
                    'avatar_url' => $avatarUrl,
                    'updated_at' => $updatedAccount->updated_at
                ],
                'message' => 'Account updated successfully'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Account update failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account){
        $this->authorize('delete',$account);
        logger()->info('Before delete in destroy controller :', [
            'account_id' => $account->id,
            'deleted_at' => $account->deleted_at,
            'exists' => $account->exists
        ]);
        $this->accountService->deleteAccount($account);
        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ], 201);
        
    }
}
