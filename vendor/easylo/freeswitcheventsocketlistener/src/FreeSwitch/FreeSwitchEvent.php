<?php
namespace FreeSwitch;

/**
* 
*/
class FreeSwitchEvent 
{
    var $UniqueID = null ;
    var $EventName = null ;
    var $EventDateTimestamp = null ;

    var $ChannelState = null ;
    var $ChannelStateNumber = null ;
    var $ChannelName = null ;
    var $ChannelCallState = null ;

    var $AnswerState = null ;

    var $CallerCalleeIDName = null ;
    var $CallerCalleeIDNumber = null ;
    var $CallerDestinationNumber = null ;
    var $CallerUniqueID = null ;
    var $CallerContext = null ;
    var $CallerChannelName = null ;
    var $CallDirection = null ;


    /*function __construct(argument)
    {
        # code...

    }*/


    public function loadFromArray( $aData = array() ){

        if( is_array( $aData )){
            foreach ($aData as $key => $value) {
                if( $value != "NULL" ){
                    switch ( $key ) {

                        case 'Unique-ID':
                            $this->UniqueID = $value ;
                            break;

                        case 'Event-Name':
                            $this->EventName = $value ;
                            break;

                        case 'Event-Date-Timestamp':
                            $this->EventDateTimestamp = $value ;
                            break;

                        case 'Channel-State':
                            $this->ChannelState = $value ;
                            break;

                        case 'Channel-State-Number':
                            $this->ChannelStateNumber = $value ;
                            break;                    

                        case 'Channel-Name':
                            $this->ChannelName = $value ;
                            break;

                        case 'Channel-Call-State':
                            $this->ChannelCallState = $value ;
                            break; 

                        case 'Answer-State':
                            $this->AnswerState = $value ;
                            break;

                        case 'Caller-Callee-ID-Number':
                            $this->CallerCalleeIDNumber = $value ;
                            break;

                        case 'Caller-Callee-ID-Name':
                            $this->CallerCalleeIDName = $value ;
                            break;

                        case 'Caller-Destination-Number':
                            $this->CallerDestinationNumber = $value ;
                            break;

                        case 'Caller-Unique-ID':
                            $this->CallerUniqueID = $value ;
                            break;
                            
                        case 'Caller-Context':
                            $this->CallerContext = $value ;
                            break;

                        case 'Caller-Channel-Name':
                            $this->CallerChannelName = $value ;
                            break;

                        case 'Call-Direction':
                            $this->CallDirection = $value ;
                            break;

                        default:
                            # code...
                            break;
                    }
                }
            }
        }

        if( !is_null( $this->UniqueID ) ){
            return $this ;
        }
        return null ;

    }

}