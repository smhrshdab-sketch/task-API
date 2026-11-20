<?php

use App\Models\Membership;
use App\Services\MembershipContext;

function current_membership(): ?Membership{
    return app(MembershipContext::class)->getMembership();
}

function current_department(){
    return app(MembershipContext::class)->getDepartment();
}