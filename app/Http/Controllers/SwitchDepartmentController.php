<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Services\MembershipContext;
use Illuminate\Http\Request;

class SwitchDepartmentController extends Controller
{
    public function __invoke(Request $request){
        $request->validate([
            'department_id' => 'required|exists:departments,id'
        ]);

        $membership = Membership::where('account_id', auth()->id())
            ->where('department_id', $request->department_id)
            ->firstOrFail();

        app(MembershipContext::class)->setMembership($membership);

        return response()->json([
            'message' => 'Department context switched'
        ]);
    }
}
