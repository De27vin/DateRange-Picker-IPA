<?php

namespace App\Exceptions;

use Exception;

class UcpException extends Exception
{
    public function __toString()
    {
        return '<ul class="errormessage"><li><span class="title">' . __('Error') . '</span><span>' . $this->getMessage() .
               '</span></li><li><span class="title">' . __('File') . '</span><span>' . $this->getFile() .
               '</span></li><li><span class="title">' . __('Line') . '</span><span>' . $this->getLine() . '</span></li></ul>';
    }
}