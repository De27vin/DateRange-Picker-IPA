FreeSWITCH Event Socket Listener
================================

A PHP 5.3 module to interact with [mod_event_socket](wiki.freeswitch.org/wiki/Mod_event_socket) on FreeSWITCH. This is a work in progress, with the event handling not fully implemented. API calls will return their raw response however. Until event handling is implemented, extend `FreeSwitchEventListener` and override the `SendFreeSwitchEvent` method.

Installation
============

Install the module using [Composer](http://getcomposer.org). Inside of the `composer.json` file, add the following:

``` javascript
{ 
  "require": {
    "easylo/freeswitcheventsocketlistener": "dev-master"
  }
}
```

Then run the following command:

``` bash
php composer.phar require easylo/freeswitcheventsocketlistener 
```

Or

``` bash
php composer.phar install
```

Example Usage
=============

``` php
<?php
require_once 'vendor/autoload.php';

use FreeSwitch\FreeSwitchEventListener;

// The command to send to FreeSWITCH
$cmd = "event json ALL";

$myFSEventListener = new FreeSwitchEventListener() ;
$myFSEventListener->setHost("192.168.1.104") ;

$myFSEventListener->event_socket_request($cmd);
?>
```
