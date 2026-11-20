<?php
namespace App\Services;

use App\Models\Membership;
use App\Models\Department;

class MembershipContext
{
    protected ?Membership $membership = null;

    public function setMembership(Membership $membership): void{      
        $this->membership = $membership;
    }

    public function getMembership(): ?Membership{
        //logger()->error('Membership in getMembership : ', [$this->membership]);
        return $this->membership;
    }

    public function getDepartment(): ?Department{
        return $this->membership?->department;
    }

    public function hasContext(): bool{
        return $this->membership !== null;
    }
}