<?php
namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Language;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Ucp
{

    /**
     * @var mixed
     */
    private $object = null;
    /**
     * @param stdClass $object
     */
    public function __construct(stdClass $object)
    {
        $this->object = $object;
    }

    /**
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this->object, $property)) {
            return $this->object->$property;
        }
    }

    public static function isStaging()
    {
        return (url('/') == 'https://ucp-staging.serv24.com' ? true : false);
    }

    public static function isLocal()
    {
        return (url('/') == 'http://svn-ucp.local' ? true : false);
    }


    /**
     * Describe this function
     *
     * @param
     * @return
     */
    public static function getUserLocale()
    {
        if(Auth::user() != null){
            return Auth::user()->locale?->language?->language_code ?? 'en';
        } else {
            return 'en';
        }
    }

    /**
     * Describe this function
     *
     * @param
     * @return
     */
    public static function setUserLocale($lang)
    {
        $user = Auth::user();
        try {
            $language = Language::where('language_code', $lang)->first();
            $affected = DB::table('users')
              ->where('id', $user->id)
              ->update(['user_language_id' => $language->language_id]);
            Session::put('locale', $lang);
        } catch(\Exception $e){
            //
        }
        // dd( Auth::user() );
        return Auth::user()?->locale?->language?->language_code ?? 'en';
    }

    /**
     * @param $property
     * @param $value
     * @return mixed
     */
    public function __set($property, $value)
    {
        if (property_exists($this->object, $property)) {
            $this->object->$property = $value;
        }
        return $this;
    }

    /**
     * @param stdClass $object
     */
    public static function make(stdClass $object)
    {
        return new self($object);
    }

    /**
     * @param array $collection
     * @return mixed
     */
    public static function makeCollection(array $collection)
    {
        foreach ($collection as $key => $Item) {
            $collection[$key] = self::make($Item);
        }
        return $collection;
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function tabbedListItem($string = null)
    {
        // dd($string);
        if ($string == null || !strpos($string, '_')) {
            return $string;
        } else {
            $strAsArray = explode('_', $string);
            return $strAsArray[0] . '<span>' . $strAsArray[1] . '</span>';
        }
    }

    public static function stringDateToUTC($dateString, $startEnd='end')
    {
        $userTimezone = (Auth::user()->timezone != null ? Auth::user()->timezone : 'Europe/Zurich');
        if($startEnd == 'start'){
            $utcDate = Carbon::createFromFormat('d.m.Y',$dateString, $userTimezone)->startOfDay();
        } else {
            $utcDate = Carbon::createFromFormat('d.m.Y',$dateString, $userTimezone)->endOfDay();
        }
        return $utcDate->setTimezone('UTC');
    }

    //this function converts string from UTC time zone to current user timezone
    public static function toUserTimezone($str, bool $formatted = false, string $format = 'Y-m-d H:i:s') {
        if(empty($str)){
            return '';
        }

        if(Auth::user() == null){
            Auth::logout();
        }
        $userTimezone = (Auth::user()->timezone != null ? Auth::user()->timezone : 'Europe/Zurich');
        if(!is_object($str)){
            try{
                if(is_array($str)){
                    $str = $str['date'];
                    $new_str = new \DateTime($str);
                } else {
                    $new_str = new \DateTime($str, new \DateTimeZone('UTC') );
                    $new_str->setTimeZone(new \DateTimeZone( $userTimezone ));
                }
            } catch(\Throwable $e) {
                $new_str = $str;
            }
        } else {
            $new_str = $str;
        }

        return $formatted ? $new_str->format($format) : $new_str;
    }


    public static function getTimezone(Request $request)
    {
        if ($timezone = $request->get('timezone')) {   
            return $timezone;
        }
    }

    /**
     * Returns a GUIDv4 string
     *
     * Uses the best cryptographically secure method
     * for all supported pltforms with fallback to an older,
     * less secure version.
     *
     * @param bool $trim
     * @return string
     */
    public static function getUuid($trim = true)
    {

// OSX/Linux
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $data    = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            return strtoupper(vsprintf('%s%s%s%s', str_split(bin2hex($data), 8)));
        }

// Windows
        if (function_exists('com_create_guid') === true) {
            if ($trim === true) {
                return trim(com_create_guid(), '{}');
            } else {
                return com_create_guid();
            }

        }

    }

    /**
     * A PHP function that will generate a secure random password.
     * 
     * @param int $length The length that you want your random password to be.
     * @return string The random password.
     */
    public static function generatePassword($length, $passwordSettings = null)
    {
        //A list of characters that can be used in our
        //random password.
        $placeholders = [
            'lower' => 'abcdefghijklmnopqrstuvwxyz',
            'upper' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'numeric' => '0123456789',
            'special' => '!@#$%^&*()_+\-=\[\]{}|'
        ];

        if($passwordSettings == null){
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $length = 12;
        } else {
            $characters = '';
            $characers = ($passwordSettings['lower'] ? $characters . $passwordSettings['lower'] : $characters);
            $characters = ($passwordSettings['upper'] ? $characters . $passwordSettings['upper'] : $characters);
            $characters = ($passwordSettings['numeric'] ? $characters . $passwordSettings['numeric'] : $characters);
            $characters = ($passwordSettings['special'] ? $characters . $passwordSettings['special'] : $characters);
            $length = $passwordSettings['length'];
        }
        // $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';

        //Create a blank string.
        $password = '';
        //Get the index of the last character in our $characters string.
        $characterListLength = mb_strlen($characters, '8bit') - 1;
        //Loop from 1 to the $length that was specified.
        foreach(range(1, $length) as $i){
            $password .= $characters[random_int(0, $characterListLength)];
        }
        return $password;
        
    }

    // public function isPartUppercase($string) {
    //     return (bool) preg_match(‘/[A-Z]/’, $string);
    // }
    // public function isPartLowercase($string) {
    //     return (bool) preg_match(‘/[a-z]/’, $string);
    // }
    // public function isPartNumeric($string) {
    //     return (bool) preg_match(‘/[0-9]/’, $string);
    // }
    // public function isPartSpecial($string) {
    //     return (bool) preg_match("/[".  ."]/", $string);
    // }

}
