<?php
namespace App\Traits;

use FreeSwitch\FreeSwitchEventListener;
use App\Models\Host;

trait FreeswitchApiTrait
{
    protected $fs_host;
    protected $fs_port;
    protected $fs_password;
    protected $fs_command;
    protected $fs_format;
    public $response;
    public $socket;
    public $errors = [];

    public function fsMake($cmd = '', $format = false, $all = false, $host = null)
    {
        $this->fs_port     = env('FS_SOCKET_PORT','8021');
        $this->fs_password = env('FS_SOCKET_PASSWORD');
        $this->fs_command  = $cmd;
        $this->fs_format   = $format;
        $this->response    = null;
        $hosts             = Host::whereNotNull('host_fqdn')->where('host_active','=',1)->get();

        try{
            if($all) {
                foreach ($hosts as $host) {
                    $this->fs_host = $host->host_fqdn;
                    if(false == ($this->response = $this->fsRun())){
                        return false;
                    }
                }
            } elseif($host != null) {
                $this->fs_host = $host . '.serv24.com';
                if(false == ($this->response = $this->fsRun())){
                    return false;
                }
            } else {
                $choice = (rand(0,999) % $hosts->count());
                if(false == ($host = $hosts->all()[$choice])){
                    return false;
                }
                $this->fs_host = $host->host_fqdn;
                if(false == ($this->response = $this->fsRun())){
                    return false;
                }
            }
        } catch(\Throwable $e){
            \Log::error($e, ['Caught']);
            return false;
        }
        return $this->response;
    }

    public function fsRun()
    {
        if($this->fs_command == ''){
            \Log::error('$this->fs_command is empty');
            return [];
        }

        if(!$this->checkSocketConnection()){
            $this->errors[] = 'Freeswitch API socket connection failed';
            \Log::error('checkSocketConnection() failed');
            return false;
        }

        try {
            $fsListener = new FreeSwitchEventListener($this->fs_host, $this->fs_port, $this->fs_password);

            $request = 'api ' . $this->fs_command;
            \Log::info('Request to freeswitch', ['request' => $request]);

            if($this->fs_format){
                $responseAsString = str_replace('|', ',', $fsListener->event_socket_request($request));
                \Log::info('ResponseAsString from freeswitch', ['responseAsString' => $responseAsString]);

                $response = array_map("str_getcsv", explode("\n", $responseAsString));
                \Log::info('Response from freeswitch', ['response' => $response]);

                $this->stripArray($response);
                \Log::info('Response from freeswitch', ['response' => $response]);

                return $this->format($response);
            }

            $response = $fsListener->event_socket_request($request);
            \Log::info('Response from freeswitch', ['response' => $response]);

            return $response;

        } catch (\Throwable $e) {
            \Log::error('Caught Exception from freeswitch call', ['Exception' => $e]);
            $this->errors[] = $e->getMessage();
            return false;
        }
    }


    /**
     * Describe this function
     *
     * @param
     * @return
     */
    public function checkSocketConnection()
    {
        try {
            if(false == (@fsockopen($this->fs_host, $this->fs_port, $errno, $errdesc, 10))){
                $this->errors[] = "Freeswitch API socket connection failed ".$errdesc." (".$errno.")";
                \Log::error("Freeswitch API socket connection failed ".$errdesc." (".$errno.")", ['Caught']);
                return false;
            }

            $result = fsockopen($this->fs_host, $this->fs_port, $errno, $errdesc, 10);
        } catch (\Throwable $e) {
            \Log::error($e, ['Caught']);
            $this->errors[] = "Freeswitch API socket connection failed ".$errdesc." (".$errno.")";
            return false;  
        }
        return true;
    }

    private function format($current_array)
    {
        $array_keys = head($current_array);
        array_shift($current_array);
        $array_formatted = [];
        foreach ($current_array as $value) {
            $array_item_formatted = array_combine($array_keys, $value);
            array_push($array_formatted, $array_item_formatted);
        }
        return $array_formatted;
    }



    private function stripArray(&$array) {
        $local_array = $array;
        foreach ($array as $key => $value) {
            if(count($value) == 1){
                unset($local_array[$key]);
            }
        }
        $array = $local_array;
    }


}
