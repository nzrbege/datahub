<?php

namespace App\Providers;

use App\Models\DataFile;
use App\Models\DataRequest;
use App\Policies\DataFilePolicy;
use App\Policies\DataRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        DataFile::class    => DataFilePolicy::class,
        DataRequest::class => DataRequestPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
