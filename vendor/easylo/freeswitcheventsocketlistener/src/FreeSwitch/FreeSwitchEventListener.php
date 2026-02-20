<?php
namespace FreeSwitch;

/**
* 
*/
class FreeSwitchEventListener 
{
    
    var $password = "ClueCon";
    var $port = "8021";    
    var $host = "127.0.0.1";

    var $fp = null ;

    var $iRetryCurrentNumber = 0 ;
    var $iRetryMaxNumber = 100 ;

    function __construct($host = null, $port = null , $password = null)
    {
       if( !is_null( $host) ) {
        $this->setHost( $host) ;
       }

       if( !is_null( $port) ) {
        $this->setPort( $port) ;
       }

       if( !is_null( $password) ) {
        $this->setPassword( $password) ;
       }

       return $this ;
    }


    public function setHost( $host ){
        $this->host = $host ;
        return $this;
    }

    public function setPort( $port ){
        $this->port = $port ;
        return $this;
    }

    public function setPassword( $password ){
        $this->password = $password ;
        return $this;
    }

    private function event_socket_create() {
        try {
            $this->fp = fsockopen($this->host, $this->port, $errno, $errdesc)  ;
        } catch (Exception $e) {
            
        }
        

        if (!$this->fp) {
            if( $this->iRetryCurrentNumber < $this->iRetryMaxNumber ){
                $this->iRetryCurrentNumber++ ;
                sleep(1);
                return $this->event_socket_create() ;
            }else{
                die("Connection to $this->host failed");  
            }            
        }
            
        $this->iRetryCurrentNumber = 0 ;
        socket_set_blocking($this->fp,false);

        if ($this->fp) {
            while (!feof($this->fp)) {
                $buffer = fgets($this->fp, 1024);
                usleep(100); //allow time for reponse
                if (trim($buffer) == "Content-Type: auth/request") {
                    fputs($this->fp, "auth $this->password\n\n");
                    break;
                }
            }
            return $this->fp;

        }else {
            return false;
        }           
    }

    public function event_socket_close() {
        $this->fp->close();
    }


    public function event_socket_request( $cmd ) {

        if( is_null( $this->fp )  ){
            $this->event_socket_create();
        }
    
        if ($this->fp) {   

            fputs($this->fp, $cmd."\n\n");    
            usleep(100); //allow time for response

            $response = '';
            $length = 0;
            $x = 0;
            while (!feof($this->fp) )
            {
                $x++;
                usleep(100);
                $theNewData = stream_get_line($this->fp, 4096, "\n");

                if ($length > 0) {
                    $response .= $theNewData . "\n";
                }

                if ($length == 0 && strpos($theNewData, 'Content-Length:') !== false) {
                    $length = (int)str_replace('Content-Length: ', '', $theNewData);
                }

                if ($length > 0 && strlen($response) >= $length) {
                    break;
                }

                if ($x > 10000) break;
            }
            $this->fp = null;

            // If a JSON response, process the event, otherwise return the raw result
            if (strpos($response, '{') === 0) {
                $response = $this->getJsonReponseClean( $response ) ;
                if( is_array( $response )){
                    $myFSEvent = new FreeSwitchEvent();
                    $myFSEvent = $myFSEvent->loadFromArray( $response ) ;

                    $this->SendFreeSwitchEvent( $myFSEvent );
                }
            }else{
                return $response;
            }

        }else {
            echo "no handle";
        }
    }

    /*
    * Send FreeSwitchEvent to ...
    */
    private function SendFreeSwitchEvent( $_FreeSwitchEvent  ){

        if( !is_null( $_FreeSwitchEvent ) ){

            var_dump( $_FreeSwitchEvent ) ;

        }
       
       return true ;
    }


    private function getJsonReponseClean( $response ){

        $pattern = '/(.*){(.*)}(.*)/m';
        
        $replacement = '{$2}';
        $response = preg_replace($pattern, $replacement, $response ); 

        try {
            $response = json_decode($response, true ) ;
        } catch (Exception $e) {
            return false ;
        }

        if( is_array( $response ) ){
            return $response ;
        }

        return false ;

    }


}