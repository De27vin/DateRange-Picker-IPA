<?php

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if(!function_exists('getTranslationFromSetting')){
    function getTranslationFromSetting($setting_id): string
    {
        $locale = session('locale','en');
        $translations = session('translations','');
        $setting = Setting::find($setting_id);
        $arrPath = Str::replaceFirst('device.', $locale.'.device.setting.',$setting->setting_key);
        return Arr::get($translations,$arrPath,'unknown');
    }
}

if(!function_exists('getTranslationFromPath')){
    function getTranslationFromPath($path): string
    {
        $locale = session('locale','en');
        $translations = session('translations','');
        $arrPath = $locale.'.'.$path;
        return Arr::get($translations,$arrPath,'unknown');
    }
}


if (!function_exists('arraySortUTF')) {
    function arraySortUTF($tArray) {
        $aOriginal = $tArray;
        if (count($aOriginal) == 0) { return $aOriginal; }
        $aModified = array();
        $aReturn   = array();
        $aSearch   = array("Ä","Å","ä","Ö","ö","Ü","ü","ß","-");
        $aReplace  = array("Ae","Aa","ae","Oe","oe","Ue","ue","ss"," ");
        foreach($aOriginal as $key => $val) {
            $aModified[$key] = str_replace($aSearch, $aReplace, $val);
        }
        natcasesort($aModified);
        foreach($aModified as $key => $val) {
            $aReturn[$key] = $aOriginal[$key];
        }
        return $aReturn;
    }
}