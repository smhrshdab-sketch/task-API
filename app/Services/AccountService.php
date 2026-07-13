<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class AccountService
{
    public function createAccount(array $data): Account{
        return DB::transaction(function () use ($data) {
            // Prepare the account data
            $accountData = [
                'organization_id' => $data['organization_id'] ?? 1,
                'name' => $data['name'],
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password']),
                'bio' => $data['bio'] ?? null,
                'permissions_version' => 1,
            ];
            
            // Create the account
            $account = Account::create($accountData);
            //event(new UserRegistered($account));
            
            // Create membership - FIXED: Remove quotes around numbers
            $member = Membership::create([
                'account_id' => $account->id,  // This is correct
                'organization_id' => $account->organization_id ?? 1,
                'department_id' => 1,  // Remove quotes - should be integer
                'role_id' => 1,        // Remove quotes - should be integer
                'status' => 'active',
                'permissions_override' => null,  // Add this if needed
            ]);
            
            Log::info('Account created successfully', [
                'account_id' => $account->id,
                'email' => $account->email,
                'membership_id' => $member->id,
                'status' => $member->status
            ]);
            
            return $account;
        });
    }
    
    /**
     * Create account and return with authentication token (returns array)
     */
    public function createAccountWithToken(array $data): array{
        return DB::transaction(function () use ($data) {
            // Create the account (this returns Account model)
            $account = $this->createAccount($data);
            
            // Generate JWT token
            $token = JWTAuth::fromUser($account);
            
            // Get token TTL
            $ttl = JWTAuth::factory()->getTTL();
            
            // Return array with token and account data
            return [
                'account' => $account,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl * 60
            ];
        });
    }

    // ------------------------
    public function getAllAccounts(array $filters = []): LengthAwarePaginator{
        $query = Account::query();
        
        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);
        
        // Get paginated results
        $perPage = $filters['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }
    /**
     * Get all accounts without pagination (for exports, etc.)
     */
    public function getAllAccountsWithoutPagination(): Collection{
        return Account::orderBy('created_at', 'desc')->get();
    }
     /* Get soft-deleted (trashed) accounts
     */
    public function getTrashedAccounts(array $filters = []): LengthAwarePaginator{
        $query = Account::onlyTrashed();
        
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        
        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }
    /**
     * Get accounts by organization
     */
    public function getAccountsByOrganization(int $organizationId, array $filters = []): LengthAwarePaginator{
        $query = Account::where('organization_id', $organizationId);
        
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }
        
        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }
    
    /**
     * Get account statistics
     */
    public function getAccountStatistics(): array{
        return [
            'total' => Account::count(),
            'active' => Account::whereNull('deleted_at')->count(),
            'trashed' => Account::onlyTrashed()->count(),
            'new_today' => Account::whereDate('created_at', today())->count(),
            'new_this_week' => Account::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }
    //-------------------------

    public function updateAccount(Account $account, array $data, $avatarFile = null): Account{
        return DB::transaction(function () use ($account, $data, $avatarFile) {
            
            // Prepare update data
            $updateData = [];
            
            // Only update fields that are present
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }
            
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            
            if (isset($data['bio'])) {
                $updateData['bio'] = $data['bio'];
            }
            
            if (isset($data['phone'])) {
                $updateData['phone'] = $data['phone'];
            }
            
            // Handle password separately (only if provided)
            if (isset($data['password']) && !empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }
            
            // Handle avatar upload
            if ($avatarFile) {
                // Delete old avatar if exists
                if ($account->avatar_path && Storage::disk('public')->exists($account->avatar_path)) {
                    Storage::disk('public')->delete($account->avatar_path);
                    Log::info('Old avatar deleted', ['account_id' => $account->id]);
                }
                
                // Store new avatar
                $avatarPath = $this->storeAvatar($avatarFile, $account->id);
                $updateData['avatar_path'] = $avatarPath;
            }
            
            // Update the account
            if (!empty($updateData)) {
                $account->update($updateData);
                Log::info('Account updated', [
                    'account_id' => $account->id,
                    'fields' => array_keys($updateData)
                ]);
            }
            
            return $account->fresh();
        });
    }
    
    /**
     * Store avatar for account
     * Path: storage/app/public/avatars/{account_id}.jpg
     */
    public function storeAvatar(UploadedFile $avatarFile, int $accountId): string{
        // Generate filename: account_{id}.extension
        $extension = $avatarFile->getClientOriginalExtension();
        $filename = "account_{$accountId}.{$extension}";
        
        // Store in avatars directory
        $path = $avatarFile->storeAs('avatars', $filename, 'public');
        
        if (!$path) {
            throw new \Exception('Failed to store avatar');
        }
        
        Log::info('Avatar stored', [
            'account_id' => $accountId,
            'path' => $path
        ]);
        
        return $path;
    }
    
    /**
     * Get avatar URL for account
     */
    public function getAvatarUrl(?string $avatarPath): ?string{
        if (!$avatarPath) {
            return null;
        }
        
        return Storage::disk('public')->url($avatarPath);
    }

    // ------------------------

    public function deleteAccount(Account $account): void{
        // if ($account->attachments()->exists()) {
        //     throw new \Exception('Account has attachments and cannot be deleted.');
        // }

        logger()->info('Before delete', [
            'account_id' => $account->id,
            'deleted_at' => $account->deleted_at,
            'exists' => $account->exists
        ]);
        
        $result = $account->delete();
        
        logger()->info('After delete', [
            'account_id' => $account->id,
            'deleted_at' => $account->deleted_at,
            'result' => $result,
            'was_soft_deleted' => $account->trashed()
        ]);
    }
    //================
    public function getAttachments(Account $account){
        $cacheKey = "attachments_{$account->getMorphClass()}_{$account->id}";

        return cache()->remember(
            $cacheKey,
            now()->addMinutes(30),
            fn() => $account->attachments()->latest()->get()
        );
    }
}

