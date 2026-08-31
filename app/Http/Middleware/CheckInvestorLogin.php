<?php

namespace App\Http\Middleware;

use Closure;

class CheckInvestorLogin
{
    /**
     * Allows only investor-portal accounts through.
     *
     * An investor account must be of type 'investor', have login enabled and be
     * linked to an investor record. Staff accounts are rejected here so that the
     * portal never renders with an admin's privileges.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (empty($user)
            || $user->user_type != 'investor'
            || $user->allow_login != 1
            || empty($user->investor_id)
            || $user->status != 'active'
        ) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
