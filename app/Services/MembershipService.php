<?php

namespace App\Services;

use App\Models\Membership;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MembershipService
{
    public function createMembership(array $data): Membership{
        return DB::transaction(function () use ($data) {
            // Handle permissions_override JSON
            if (isset($data['permissions_override'])) {
                $data['permissions_override'] = json_encode($data['permissions_override']);
            }
            
            $membership = Membership::create($data);
            
            Log::info('Membership created', [
                'membership_id' => $membership->id,
                'account_id' => $membership->account_id
            ]);
            
            return $membership;
        });
    }
    ///===========
    public function updateMembership(array $data, Membership $membership): Membership{
        return DB::transaction(function () use ($data, $membership) {
            // Update the membership with the provided data
            $membership->update($data);
            
            Log::info('Membership updated successfully', [
                'membership_id' => $membership->id,
                'account_id' => $membership->account_id,
                'organization_id' => $membership->organization_id,
                'department_id' => $membership->department_id,
                'role_id' => $membership->role_id,
                'status' => $membership->status
            ]);
            
            return $membership->fresh(); // Return fresh instance with updated data
        });
    }
    public function deleteMembership(Membership $membership): void{

            logger()->info('Before delete', [
                'membership_id' => $membership->id,
                'deleted_at' => $membership->deleted_at,
                'exists' => $membership->exists
            ]);
            
            $result = $membership->delete();
            Log::info('Membership is deleted successfully');
        }
    public function switchStatus(Membership $membership): Membership{
        return DB::transaction(function () use ( $membership) {
            if($membership->status == 'active'){
                $membership->status == 'suspended';
            }
            else{
                $membership->status == 'active';
            }
            Log::info('Membership status is updated successfully', [
                'status' => $membership->status
            ]);
            
            return $membership->fresh(); // Return fresh instance with updated data
        });
    }
    //============
    public function getAllMembershipsWithoutPagination(): Collection{
        return Membership::orderBy('created_at', 'desc')->get();
    }
    public function getAllMembershipsWithPagination(array $data): LengthAwarePaginator{
        $perPage = $data['perPage'] ?? 10;
        $search = $data['search'] ?? '';

        Log::info('class MembershipService: Fetching memberships', [
            'perPage' => $perPage,
            'search' => $search
        ]);

        $query = Membership::with([
            'account',
            'department',
            'role'
        ])->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('account', function ($accountQuery) use ($search) {
                    $accountQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('role', function ($roleQuery) use ($search) {
                    $roleQuery->where('title', 'like', "%{$search}%");
                })
                ->orWhereHas('department', function ($departmentQuery) use ($search) {
                    $departmentQuery->where('title', 'like', "%{$search}%");
                });
            });
        }

        $memberships = $query->paginate($perPage);

        logger('MembershipService: ', ['memberships' => $memberships]);

        return $memberships;
    }

}