<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Membership;
use App\Services\DepartmentContext;
use Symfony\Component\HttpFoundation\Response;

class SetDepartmentContext
{
    public function handle(Request $request, Closure $next): Response{
        //logger()->info('=== SetDepartmentContext STARTED ===');
        $departmentId = $request->header('X-Department-Id');

        if (!$departmentId) {
            logger()->warning('No department ID in header');
            return response()->json([
                'message' => 'Department context not provided'
            ], 400);
        }

        $membership = Membership::where('account_id', auth()->id())
            ->where('department_id', $departmentId)
            ->first();

        if (!$membership) {
            logger()->warning('No membership found', [
                'account_id' => auth()->id(),
                'department_id' => $departmentId
            ]);
            return response()->json([
                'message' => 'You are not a member of this department'
            ], 403);
        }
        //logger()->info('Membership found', ['membership_id' => $membership->id]);
        app(DepartmentContext::class)->setMembership($membership);
        //$request->attributes->set('membership', $membership);// تزریق membership به request
        //logger()->info('=== SetDepartmentContext FINISHED ===');

        return $next($request);
    }
}