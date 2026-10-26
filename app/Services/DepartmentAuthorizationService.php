<?php
    namespace App\Services;//خیلی ضروری

use App\Models\Department;
use Illuminate\Support\Facades\Log;

class DepartmentAuthorizationService
{
    protected function hasPermission(string $permission): bool
    {
        $membership = current_membership();

        if (!$membership) {
            return false;
        }

        return $membership->hasPermission($permission);
    }

    public function canCreate(Department $parent): bool
    {
        if (!$this->isWithinScope($parent)) {
            return false;
        }

        return $this->hasPermission('department_create');
    }

    public function canUpdate(Department $target): bool
    {
        if (!$this->isWithinScope($target)) {
            return false;
        }

        return $this->hasPermission('department_update');
    }

    public function canDelete(Department $target): bool
    {
        if (!$this->isWithinScope($target)) {
            return false;
        }

        return $this->hasPermission('department_delete');
    }

    public function canView(Department $target): bool
    {
        if ($this->canViewAll()) {
            return true;
        }

        if (!$this->hasPermission('department_view')) {
            return false;
        }

        return $this->isWithinScope($target);
    }

    public function canViewAll(): bool
    {
        return $this->hasPermission('department_view_all');
    }

    public function canViewAny(): bool
    {
        return $this->hasPermission('department_view')
            || $this->canViewAll();
    }

    public function isWithinScope(Department $target): bool
    {
        $currentDepartment = current_department();

        if (!$currentDepartment) {
            return false;
        }

        if ($currentDepartment->id === $target->id) {
            return true;
        }

        $parent = $target->parent;

        while ($parent) {
            if ($parent->id === $currentDepartment->id) {
                return true;
            }

            $parent = $parent->parent;
        }

        return false;
    }
}