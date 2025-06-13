<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        /**
         * Gate to check if a user can manage roles for a specific community.
         * Only users with the role 'Kurucu' (Founder) or 'Yönetici' (Admin) can pass.
         */
        Gate::define('manage-community-roles', function (User $user, Community $community) {
            // Get the user's membership details for this specific community
            $membership = $user->communityMemberships()
                ->where('community_id', $community->id)
                ->first();

            // If the user is not a member at all, deny access.
            if (!$membership) {
                return false;
            }

            // Check if the user's role ID corresponds to Kurucu (1) or Yönetici (2)
            return in_array($membership->role_id, [1, 2]);
        });
    }
}
