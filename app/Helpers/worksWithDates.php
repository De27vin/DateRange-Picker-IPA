<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

if (!function_exists('toUserDate')) {
    function toUserDate($date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($date == null) {
            return '-- . -- . ----';
        }

        if ($user) {
            $timezone = $user->user_timezone;
        } else {
            $timezone = Auth::user()?->user_timezone
                ?? throw new \RuntimeException('toUserDate() requires an authenticated user or an explicit $user argument.');
        }
 
        if (is_string($date)) {
            return Carbon::parse($date, 'UTC')->setTimezone($timezone)->isoFormat('L');
        }
 
        return $date->setTimezone($timezone)->isoFormat('L');
    }
}
 
if (!function_exists('toUserTime')) {
    function toUserTime($date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($date == null) {
            return '-- : --';
        }

        if ($user) {
            $timezone = $user->user_timezone;
        } else {
            $timezone = Auth::user()?->user_timezone
                ?? throw new \RuntimeException('toUserTime() requires an authenticated user or an explicit $user argument.');
        }
 
        if (is_string($date)) {
            return Carbon::parse($date, 'UTC')->setTimezone($timezone)->isoFormat('LT');
        }
 
        return $date->setTimezone($timezone)->isoFormat('LT');
    }
}
 
if (!function_exists('toUserDateTime')) {
    function toUserDateTime($date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($date == null) {
            return '-- . -- . ----  --:--';
        }

        if ($user) {
            $timezone = $user->user_timezone;
        } else {
            $timezone = Auth::user()?->user_timezone
                ?? throw new \RuntimeException('toUserDateTime() requires an authenticated user or an explicit $user argument.');
        }
 
        if (is_string($date)) {
            return Carbon::parse($date, 'UTC')->setTimezone($timezone)->isoFormat('L LT');
        }
 
        return $date->setTimezone($timezone)->isoFormat('L LT');
    }
}
 
if (!function_exists('toUserDateTimeObject')) {
    function toUserDateTimeObject($date, ?User $user = null, bool $userTimezone = false)
    {
        if ($date == null) {
            return '-- . -- . ----  --:--';
        }

        if ($userTimezone) {

            if ($user) {
                $timezone = $user->user_timezone;
            } else {
                $timezone = Auth::user()->user_timezone;
            }

            if (is_string($date)) {
                return Carbon::parse($date, 'UTC')->setTimezone($timezone);
            }

            return $date->setTimezone($timezone);

        } else {

            if (is_string($date)) {
                return Carbon::parse($date, 'UTC');
            }

            return $date;
        }
    }
}

if (!function_exists('fromUserDate')) {
    function fromUserDate($date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($date == null) {
            return '-- . -- . ----';
        }

        if ($user) {
            $timezone = $user->timezone;
        } else {
            $timezone = Auth::user()->timezone;
        }
 
        if (is_string($date)) {
            return Carbon::parse($date, $timezone)->setTimezone('UTC')->toDateString();
        }
 
        return $date->setTimezone('UTC')->toDateTimeString();
    }
}
 
if (!function_exists('fromUserTime')) {
    function fromUserTime($date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($date == null) {
            return '-- : --';
        }

        if ($user) {
            $timezone = $user->user_timezone;
        } else {
            $timezone = Auth::user()->user_timezone;
        }
 
        if (is_string($date)) {
            return Carbon::parse($date, $timezone)->setTimezone('UTC')->toTimeString();
        }
 
        return $date->setTimezone('UTC')->toDateTimeString();
    }
}
 
if (!function_exists('fromUserDateTime')) {
    function fromUserDateTime($date, ?User $user = null, string $timezone = 'UTC'): string
    {
        if ($date == null) {
            return '-- . -- . ----  --:--';
        }

        if ($user) {
            $timezone = $user->user_timezone;
        } else {
            $timezone = Auth::user()->user_timezone;
        }
 
        if (is_string($date)) {
            return Carbon::parse($date, $timezone)->setTimezone('UTC')->toDateTimeString();
        }
 
        return $date->setTimezone('UTC')->toDateTimeString();
    }
}

if (!function_exists('sortOverdueAsc')){
    function sortOverdueAsc($a, $b)
    {
        return $a['overdue_seconds'] - $b['overdue_seconds'];
    }
}


if (!function_exists('sortOverdueDesc')){
    function sortOverdueDesc($a, $b)
    {
        return $b['overdue_seconds'] - $a['overdue_seconds'];
    }
}
