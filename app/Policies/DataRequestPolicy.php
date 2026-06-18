<?php

namespace App\Policies;

use App\Models\DataRequest;
use App\Models\User;

class DataRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Super admin lihat semua, admin lihat miliknya (difilter di controller)
    }

    public function view(User $user, DataRequest $dataRequest): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $dataRequest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function approve(User $user, DataRequest $dataRequest): bool
    {
        return $user->isSuperAdmin() && $dataRequest->isPending();
    }

    public function reject(User $user, DataRequest $dataRequest): bool
    {
        return $user->isSuperAdmin() && $dataRequest->isPending();
    }

    public function revoke(User $user, DataRequest $dataRequest): bool
    {
        return $user->isSuperAdmin() && $dataRequest->isApproved();
    }

    public function download(User $user, DataRequest $dataRequest): bool
    {
        return $dataRequest->user_id === $user->id && $dataRequest->canDownload();
    }
}
