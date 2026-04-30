<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

if (!function_exists('isNumberInBetween')) {
    function isNumberInBetween($number, $lowerBound, $upperBound): bool
    {
        return ($number >= $lowerBound) && ($number <= $upperBound);
    }
}

if (!function_exists('isNumberBetween')) {
    function isNumberBetween($number, $lowerBound, $upperBound): bool
    {
        return ($number > $lowerBound) && ($number < $upperBound);
    }
}

if (!function_exists('logoutUser')) {
    function logoutUser() {
        Cookie::queue(Cookie::forget('ucp_account'));
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    }
}

if (!function_exists('toBoolean')) {
    function toBoolean(String $string) {
        return filter_var($string,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE);
    }
}

if (!function_exists('countryDisplayName')) {
    function countryDisplayName(?string $countryIso, string $locale = 'en'): string
    {
        $countryIso = strtoupper((string) $countryIso);
        if ($countryIso === '') {
            return '';
        }

        if (function_exists('locale_get_display_region')) {
            return locale_get_display_region('-'.$countryIso, $locale) ?: $countryIso;
        }

        return $countryIso;
    }
}

// ------------------
// PORTING TO TEST BELOW
// ------------------

if(!function_exists('toUserTimezone')){
    function toUserTimezone($str){
        if(empty($str)){
            return '';
        }

        if($userTimezone = (Auth::user()->user_timezone != null ? Auth::user()->user_timezone : 'Europe/Zurich')){
            $format       = 'd.m.Y H:i:s';
            if(!is_object($str)){
                $new_str = new \DateTime($str, new \DateTimeZone('UTC') );
            } else {
                $new_str = $str;
            }
            $new_str->setTimeZone(new \DateTimeZone( $userTimezone ));

            return $new_str->format($format);
        } else {
            Auth::logout();
        }
    }
}

if(!function_exists('startBenchmark')){
    function startBenchmark() {
        global $benchmarkData;
        $benchmarkData['start_time'] = microtime(true);
        $benchmarkData['start_memory'] = memory_get_usage();
    }
}

if(!function_exists('stopBenchmark')){
    function stopBenchmark(bool $return = false) {
        global $benchmarkData;
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $benchmarkData['execution_time'] = $endTime - $benchmarkData['start_time'];
        $benchmarkData['memory_usage'] = $endMemory - $benchmarkData['start_memory'];

        $result = 'Time: '.number_format($benchmarkData['execution_time'], 4).' seconds';
        $result = $result . '<br>';
        $result = $result . 'Memory: '.number_format($benchmarkData['memory_usage'] / (1024 * 1024), 4).' mb';

        if ($return) {
            return $result;
        }

        die($result);
    }
}

if(!function_exists('old_each')){
    function old_each(&$array) {
        $key = key($array);
        $value = current($array);
        $each = is_null($key) ? false : [
            0        => $key,
            'key'    => $key,
            1        => $value,
            'value'  => $value,
        ];
        next($array);
        return $each;
    }
}

if(!function_exists('trimAndNullEmptyString')){
    function trimAndNullEmptyString($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}

if(!function_exists('trimAndNullEmptyStringsArray')){
    function trimAndNullEmptyStringsArray($input)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = trimAndNullEmptyStringsArray($value);
            }
            elseif (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = null;
                }
            }
        }
        return $input;
    }
}

if (!function_exists('get_roles_array')) {
    function get_roles_array(User|int|string $user = null)
    {
        if ((!$user instanceof \App\Models\User) && !empty($user)) {
            $user = User::find($user);
        }
        $user = $user ?? Auth::user();

        $rolesService = new \App\Services\RolesService();
        return $rolesService->getUserRoleStates($user);
    }
}

if (!function_exists('get_roles_binary')) {
    function get_roles_binary(User|int|string $user = null)
    {
        if ((!$user instanceof \App\Models\User) && !empty($user)) {
            $user = User::find($user);
        }
        $user = $user ?? Auth::user();

        $rolesService = new \App\Services\RolesService();
        $rolesArray = $rolesService->getUserRoleStates($user);
        return $rolesService->getBasicRolesBinary($rolesArray['basicRoles']);
    }
}

if (!function_exists('compare_binary_roles')) {
    function compare_binary_roles($binaryRoles1, $binaryRoles2)
    {
        $rolesService = new \App\Services\RolesService();
        return $rolesService->compareBinaryRoles($binaryRoles1, $binaryRoles2);
    }
}
