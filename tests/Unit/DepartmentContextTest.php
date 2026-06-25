<?php

use App\Services\DepartmentContext;
use App\Models\Membership;
use Tests\TestCase;

class DepartmentContextTest extends TestCase
{
    //php artisan test --filter=
    public function test_it_stores_and_returns_membership()
    {
        $context = new DepartmentContext();//چون هدف ما تست دیتابیس  نیس بلکه عملکرد  کلاسه پس  برای رهایی از خطا های دیتابیس از
        $membership = new Membership();   //new 
        //                                  استفاده کردیم
        $context->setMembership($membership);

        $this->assertSame($membership, $context->getMembership());
        $this->assertTrue($context->hasContext());
    }
}
