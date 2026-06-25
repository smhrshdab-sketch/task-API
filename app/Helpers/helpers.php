<?php

use App\Models\Membership;
use App\Services\DepartmentContext;

function current_membership(): ?Membership{
    return app(DepartmentContext::class)->getMembership();
}

function current_department(){
    return app(DepartmentContext::class)->getDepartment();
}