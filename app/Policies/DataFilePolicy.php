<?php

namespace App\Policies;

use App\Models\DataFile;
use App\Models\User;

class DataFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, DataFile $dataFile): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->canAccessFile($dataFile);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, DataFile $dataFile): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, DataFile $dataFile): bool
    {
        return $user->isSuperAdmin();
    }

    public function download(User $user, DataFile $dataFile): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->canAccessFile($dataFile);
    }
}
