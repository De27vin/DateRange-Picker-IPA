<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Setting;
use Illuminate\Support\Arr;
use App\Traits\PasswordPolicyTrait;

class PasswordStrengthRule implements Rule
{
    use PasswordPolicyTrait;

    public $length;
    public $lengthCheck           = false;
    public $uppercaseCheck        = false;
    public $numericCheck          = false;
    public $specialCharacterCheck = false;
    public $settingsCurrent;
    public $messages              = [];

    public function __construct(int $accountId)
    {
        $this->messages = [];
        $this->settingsCurrent = $this->getActivePasswordSettings($accountId);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(Str::length($value) < $this->settingsCurrent['length']){
            $this->fail(__('at least :length characters', ['length' => $this->settingsCurrent['length']]));
        }
        if($this->settingsCurrent['uppercase'] && !$this->isPartUppercase($value)){
            $this->fail(__('at least one uppercase letter'));
        }
        if($this->settingsCurrent['lowercase'] && !$this->isPartLowercase($value)){
            $this->fail(__('at least one lowercase letter'));
        }
        if($this->settingsCurrent['numbers'] && !$this->isPartNumeric($value)){
            $this->fail(__('at least one number'));
        }

        if($this->settingsCurrent['symbols'] && !$this->isPartSpecial($value)){
            $this->fail(__('at least one symbol'));
        }

        if (! empty($this->messages)) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The «:attribute» must contain') . '<br/>' . implode('<br/>',$this->messages);
    }

    public function isPartUppercase($string) {
        return (bool) preg_match("/[A-Z]/", $string);
    }
    public function isPartLowercase($string) {
        return (bool) preg_match("/[a-z]/", $string);
    }
    public function isPartNumeric($string) {
        return (bool) preg_match("/[0-9]/", $string);
    }
    // public function isPartSpecial($string) {
    //     return (bool) preg_match("/\p{Z}|\p{S}|\p{P}/u", $string);
    // }

    public function isPartSpecial($string) {
        return (bool) preg_match("/[#@!()&^%$~\/\]*\[{}?><;]/u", $string);
    }

    /**
     * Adds the given failures, and return false.
     *
     * @param  array|string  $messages
     * @return bool
     */
    protected function fail($message)
    {
        $this->messages = Arr::prepend($this->messages,$message);
    }

}
