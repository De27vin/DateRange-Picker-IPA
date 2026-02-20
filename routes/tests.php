<?php

use Illuminate\Support\Facades\Route;


Route::get('/api_test/local/mobile', function () {
    $client = new GuzzleHttp\Client();
    $res = $client->post('http://localhost:8000/v1/mobile', [
        'http_errors' => false,
        'auth' =>  ['jacek.dziurdzikowski3@serv24.com', '123456'],
        GuzzleHttp\RequestOptions::JSON => ['action' => 'login'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'accounts'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'reserve', 'account' => '2', 'device' => 'LT-80024'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'unreserve', 'account' => '2', 'device' => 'LT-80024'],
//        GuzzleHttp\RequestOptions::JSON => [
//            'action' => 'classify',
//            'account' => '2',
//            'device' => 'V058 - Last1',
//            'mic' => 'ok',
//            'speaker' => 'ok',
//            'alarmbtn' => 'ok',
//            'location' => 'ok',
//            'comment' => 'device is completely ok',
//        ],
    ]);
    echo $res->getStatusCode();
    echo "<pre>";
    print_r($res->getHeaders());
    echo "<pre>";
    echo $res->getBody();
});

Route::get('/api_test/dev/mobile', function () {
    $client = new GuzzleHttp\Client();
    $res = $client->post('https://ucp-api-dev.serv24.com/v1/mobile', [
        'http_errors' => false,
        'auth' =>  ['jacek.dziurdzikowski@serv24.com', '123456'],
        GuzzleHttp\RequestOptions::JSON => ['action' => 'login'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'accounts'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'reserve', 'account' => '3', 'device' => 'LT-80024'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'unreserve', 'account' => '3', 'device' => 'LT-80024'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'reserve', 'account' => '3', 'device' => 'SF-74023'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'unreserve', 'account' => '3', 'device' => 'SF-74023'],
//        GuzzleHttp\RequestOptions::JSON => [
//            'action' => 'classify',
//            'account' => '2',
//            'device' => 'V058 - Last1',
//            'mic' => 'ok',
//            'speaker' => 'ok',
//            'alarmbtn' => 'ok',
//            'location' => 'ok',
//            'comment' => 'device is completely ok',
//        ],
    ]);
    echo $res->getStatusCode();
    echo "<pre>";
    print_r($res->getHeaders());
    echo "<pre>";
    echo $res->getBody();
});

Route::get('/api_test/local/amwin_connect', function () {
    $client = new GuzzleHttp\Client();
    $res = $client->post('http://localhost:8000/v1/connect', [
        'http_errors' => false,
//        'auth' =>  ['AmWinTest', 'AmWinPass1'],
        GuzzleHttp\RequestOptions::JSON => ['EQID' => 'V058 - Last1', 'Target' => 'jacek.dziurdzikowski@serv24.com', 'UUID' => 'afdsfa', 'ClassType' => 'sdas'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'login'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'accounts'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'reserve', 'account' => '2', 'device' => '7326'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'unreserve', 'account' => '2', 'device' => 'V058 - Last1'],
//        GuzzleHttp\RequestOptions::JSON => [
//            'action' => 'classify',
//            'account' => '2',
//            'device' => 'V058 - Last1',
//            'mic' => 'ok',
//            'speaker' => 'ok',
//            'alarmbtn' => 'ok',
//            'location' => 'ok',
//            'comment' => 'device is completely ok',
//        ],
    ]);
    echo $res->getStatusCode();
    echo "<pre>";
    print_r($res->getHeaders());
    echo "<pre>";
    echo $res->getBody();
});


Route::get('/api_test/out/amwin_alarm', function () {
    $client = new GuzzleHttp\Client();
    $res = $client->post('https://ucptest.amwin.de:9876/app-api/v1/alarm', [
        'http_errors' => true,
        'auth' =>  ['UCPTest', 'UCPPass'],
        GuzzleHttp\RequestOptions::JSON => ['EQID' => '2000', 'UUID' => 'df644ac4-a524-42ae-be08-ea32393d7f94'],
    ]);
    echo $res->getStatusCode();
    echo "<pre>";
    print_r($res->getHeaders());
    echo "<pre>";
    echo $res->getBody();
});

Route::get('/api_test/out/amwin_alert', function () {
    $client = new GuzzleHttp\Client();
    $res = $client->post('https://ucptest.amwin.de:9876/app-api/v1/alert', [
        'http_errors' => true,
        'auth' =>  ['UCPTest', 'UCPPass'],
        GuzzleHttp\RequestOptions::JSON => ['EQID' => '2000', "AlertType" => "PERIODICAL", "State" => "true", "OptValue" => "optional value"],
    ]);
    echo $res->getStatusCode();
    echo "<pre>";
    print_r($res->getHeaders());
    echo "<pre>";
    echo $res->getBody();
});


Route::get('/api_test/dev/amwin_connect', function () {
    $client = new GuzzleHttp\Client();
    $res = $client->post('https://ucp-api-dev.serv24.com/v1/connect', [
        'http_errors' => false,
//        'auth' =>  ['jacek.dziurdzikowski@serv24.com', '123456'],
    GuzzleHttp\RequestOptions::JSON => ['EQID' => 'LC-20001', 'Target' => 'joachim.zender@insocam.de', 'UUID' => '', 'Timestamp' => '2025-03-04T14:12:10.726+01:00'],

//        GuzzleHttp\RequestOptions::JSON => ['action' => 'login'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'accounts'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'reserve', 'account' => '3', 'device' => 'TL-200005'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'unreserve', 'account' => '3', 'device' => 'Serv24-600001'],
//        GuzzleHttp\RequestOptions::JSON => [
//            'action' => 'classify',
//            'account' => '3',
//            'device' => 'TL-200005',
//            'mic' => 'ok',
//            'speaker' => 'ok',
//            'alarmbtn' => 'ok',
//            'location' => 'ok',
//            'comment' => 'device is completely ok - 22.08.24',
//        ],
    ]);
    echo $res->getStatusCode();
    echo "<pre>";
    print_r($res->getHeaders());
    echo "<pre>";
    echo $res->getBody();
});

Route::get('/api_test/test2/mobile', function () {
    $client = new GuzzleHttp\Client();
    $res = $client->post('https://ucp-api-test2.serv24.com/v1/mobile', [
        'http_errors' => false,
        'auth' =>  ['jacek.dziurdzikowski@serv24.com', '123456'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'login'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'accounts'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'reserve', 'account' => '2', 'device' => 'SL-40002'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'unreserve', 'account' => '2', 'device' => 'SL-40002'],
        GuzzleHttp\RequestOptions::JSON => [
            'action' => 'classify',
            'account' => '2',
            'device' => 'SL-40002',
            'mic' => 'ok',
            'speaker' => 'ok',
            'alarmbtn' => 'ok',
            'location' => 'ok',
            'comment' => 'device is completely ok',
        ],
    ]);
    echo $res->getStatusCode();
    echo "<pre>";
    print_r($res->getHeaders());
    echo "<pre>";
    echo $res->getBody();
});

Route::get('/api_test/prod/mobile', function () {
    $client = new GuzzleHttp\Client();
    $res = $client->post('https://api.serv24.com/v1/mobile', [
        'http_errors' => false,
        'auth' =>  ['jacek.dziurdzikowski@serv24.com', '123456'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'login'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'accounts'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'reserve', 'account' => '2', 'device' => 'Serv24-60001'],
//        GuzzleHttp\RequestOptions::JSON => ['action' => 'unreserve', 'account' => '3', 'device' => '604'],
        GuzzleHttp\RequestOptions::JSON => [
            'action' => 'classify',
            'account' => '3',
            'device' => '604',
            'mic' => 'ok',
            'speaker' => 'ok',
            'alarmbtn' => 'ok',
            'location' => 'ok',
            'comment' => 'device is completely ok',
        ],
    ]);
    echo $res->getStatusCode();
//    echo "<pre>";
//    print_r($res->getHeaders());
    echo "<pre>";
    echo $res->getBody();
});