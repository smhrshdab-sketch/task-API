<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Membership;
use App\Services\DepartmentContext;
use Symfony\Component\HttpFoundation\Response;

class SetDepartmentContext{
    public function handle(Request $request, Closure $next): Response{
        $membershipId = $request->header('X-Membership-Id');

        if (!$membershipId) {
            logger()->warning('No membership ID in header');

            return response()->json([
                'message' => 'Membership context not provided'
            ], 400);
        }

        $membership = Membership::where('account_id', auth()->id())
            ->whereKey($membershipId)
            ->first();

        if (!$membership) {
            logger()->warning('No membership found', [
                'account_id' => auth()->id(),
                'membership_id' => $membershipId,
            ]);

            return response()->json([
                'message' => 'You are not a member of this membership'
            ], 403);
        }

        app(DepartmentContext::class)->setMembership($membership);
    }
}