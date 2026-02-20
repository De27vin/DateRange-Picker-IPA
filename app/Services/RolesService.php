<?php
namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class RolesService
{
    private array $hierarchyRolesIds = [];

    public function getHierarchyRolesIds(): array
    {
        if (!empty($this->hierarchyRolesIds)) {
            return $this->hierarchyRolesIds;
        }

        $order = (new Role())->order;

        $roles = Role::all()->sortBy(function ($role) use ($order) {
            $index = array_search($role->role_type, $order);
            return ($index === false) ? PHP_INT_MAX : $index;
        });

        $this->hierarchyRolesIds = $roles->pluck('role_id')->toArray();

        return $this->hierarchyRolesIds;
    }

    public function getHighestRoleOfUser(User|Authenticatable $user, ?int $accountId = null)
    {
        $userRoleIds = $user->roles
            ->when($accountId, fn(Collection $roles) => $roles->filter(
                fn($role) => $role->getOriginal('pivot_ur_account_id') == $accountId)
            )
            ->pluck('role_id')->toArray();

        $highestRoleId = null;
        foreach ($this->getHierarchyRolesIds() as $roleId) {
            if (in_array($roleId, $userRoleIds)) {
                $highestRoleId = $roleId;
                break;
            }
        }

        return $highestRoleId;
    }

    public function doesUserHaveHigherOrEqualRole(User|Authenticatable $user, ?int $roleId, ?int $accountId = null): bool
    {
        if (is_null($roleId)) {
            return true;
        }

        $userRoleIds = $user->roles
            ->when($accountId, fn(Collection $roles) => $roles->filter(
                fn($role) => is_null($role->getOriginal('pivot_ur_account_id')) || $role->getOriginal('pivot_ur_account_id') == $accountId)
            )
            ->pluck('role_id')->toArray();
        $hierarchyRolesIds = $this->getHierarchyRolesIds();

        $roleIndex = array_search($roleId, $hierarchyRolesIds);
        if ($roleIndex === false) {
            return false;
        }

        foreach ($userRoleIds as $userRoleId) {
            $userRoleIndex = array_search($userRoleId, $hierarchyRolesIds);
            if ($userRoleIndex !== false && $userRoleIndex <= $roleIndex) {
                return true;
            }
        }

        return false;
    }

    public function getUserRoleStates(User $user)
    {
        $basicRoles = (new Role)->without('users_roles')->whereIn('role_type', ['site','admin','user'])->get()->pluck(0, 'role_type')->all();
        $optionalRoles = (new Role)->without('users_roles')->whereIn('role_type', ['agent','mobile'])->get()->pluck(0, 'role_type')->all();
        $roles = $user->roles()->get()->pluck('role_id','role_type')->toArray();

        foreach ($basicRoles as $type => $state) {
            $basicRoles[$type] = (Arr::exists($roles, $type) ? 1 : 0);
        }
        foreach ($optionalRoles as $type => $state) {
            $optionalRoles[$type] = (Arr::exists($roles, $type) ? 1 : 0);
        }

        return compact('basicRoles', 'optionalRoles');
    }

    public function getBasicRolesBinary(array $basicRoles)
    {
        $binaryRoles = intval($basicRoles['site']) * 100;
        $binaryRoles = (intval($basicRoles['admin']) * 10) + intval($binaryRoles);
        $binaryRoles = intval($basicRoles['user']) + intval($binaryRoles);

        return $binaryRoles;
    }

    public function normalizeBinaryRoles($binaryRoles)
    {
        return $binaryRoles >= 100 ? 100 : ($binaryRoles >= 10 ? 10 : 1);
    }

    public function compareBinaryRoles($binaryRoles1, $binaryRoles2)
    {
        return $this->normalizeBinaryRoles($binaryRoles1) <=> $this->normalizeBinaryRoles($binaryRoles2);
    }
}