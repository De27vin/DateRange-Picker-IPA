<?php

namespace App\Overrides\FreeSwitch;

use FreeSwitch\FreeSwitchEventListener as BaseFreeSwitchEventListener;

/**
 * FreeSwitchEventListener with configurable timeouts
 *
 * This class extends the vendor FreeSwitchEventListener to provide
 * longer timeout values without modifying vendor files directly.
 */
class FreeSwitchEventListener extends BaseFreeSwitchEventListener
{
    /**
     * Maximum number of iterations to wait for response
     * Default 65000 gives approximately 9.75 seconds (65000 * 150 microseconds)
     */
    protected $maxIterations = 65000;

    /**
     * Connection timeout in seconds
     */
    protected $connectionTimeout = 10;

    /**
     * Set the maximum iterations for response waiting
     *
     * @param int $iterations
     * @return self
     */
    public function setMaxIterations($iterations)
    {
        $this->maxIterations = $iterations;
        return $this;
    }

    /**
     * Set connection timeout in seconds
     *
     * @param int $timeout
     * @return self
     */
    public function setConnectionTimeout($timeout)
    {
        $this->connectionTimeout = $timeout;
        return $this;
    }

    /**
     * Override event_socket_create to add connection timeout
     */
    private function event_socket_create() {
        try {
            $this->fp = fsockopen($this->host, $this->port, $errno, $errdesc, $this->connectionTimeout);
        } catch (\Exception $e) {

        }


        if (!$this->fp) {
            if( $this->iRetryCurrentNumber < $this->iRetryMaxNumber ){
                $this->iRetryCurrentNumber++;
                sleep(1);
                return $this->event_socket_create();
            }else{
                die("Connection to $this->host failed");
            }
        }

        $this->iRetryCurrentNumber = 0;
        socket_set_blocking($this->fp, false);

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

        } else {
            return false;
        }
    }

    /**
     * Override event_socket_request to use configurable iteration limit
     */
    public function event_socket_request($cmd) {

        if (is_null($this->fp)) {
            $this->event_socket_create();
        }

        if ($this->fp) {

            fputs($this->fp, $cmd."\n\n");
            usleep(150); //allow time for response

            $response = '';
            $length = 0;
            $x = 0;
            while (!feof($this->fp))
            {
                $x++;
                usleep(150);
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

                // Use configurable max iterations instead of hardcoded 10000
                if ($x > $this->maxIterations) break;
            }
            $this->fp = null;

            // If a JSON response, process the event, otherwise return the raw result
            if (strpos($response, '{') === 0) {
                $response = $this->getJsonReponseClean($response);
                if (is_array($response)) {
                    $myFSEvent = new \FreeSwitch\FreeSwitchEvent();
                    $myFSEvent = $myFSEvent->loadFromArray($response);

                    $this->SendFreeSwitchEvent($myFSEvent);
                }
            } else {
                return $response;
            }

        } else {
            echo "no handle";
        }
    }
}
