<?php

namespace App\Policies;

use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function canManage(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
