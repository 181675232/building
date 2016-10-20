<?php
namespace Org\Util;

//极光推送
use JPush\Model as M;
use JPush\JPushClient;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

require_once './vendor/autoload.php';
//极光推送end

/**
 * 极光push实现类
 */
class Jpush
{
    private $appkey = '';
    private $secret = '';

    public function __construct( $appkey , $secret )
    {
        $this->appkey = $appkey;
        $this->secret = $secret;
    }

    public function push( $jpushid , $title , $content , $array = array() )
    {
        $client = new JPushClient( $this->appkey , $this->secret );
        if( $jpushid == 'all' ){
            $result1 = $client->push()
                ->setPlatform( M\Platform( 'android' , 'ios' ) )
                ->setAudience( M\all )
                ->setNotification( M\notification( $content ,
                    M\android( $content , $title , 1 , $array ) ,
                    M\ios( $content , "happy" , "+1" , true , $array , "Ios8 Category" )
                ) )
                ->send();
            return $result1;
        }else{
            $result1 = $client->push()
                ->setPlatform( M\Platform( 'android' , 'ios' ) )
                ->setAudience( M\Audience( M\registration_id( $jpushid ) ) )
                ->setNotification( M\notification( $content ,
                    M\android( $content , $title , 1 , $array ) ,
                    M\ios( $content , "happy" , "+1" , true , $array , "Ios8 Category" )
                ) )
                ->send();
            return $result1;
        }

    }

    public function pushMessage( $jpushid , $title = '' , $content , $content_type = '' , $array = array() )
    {
        $client = new JPushClient( $this->appkey , $this->secret );
        if( $jpushid == 'all' ){
            $result1 = $client->push()
                ->setPlatform( M\Platform( 'android' , 'ios' ) )
                ->setAudience( M\all )
                ->setMessage( M\message( $content , $title , $content_type , $array ) )
                ->send();
            return $result1;
        }else{
            $result1 = $client->push()
                ->setPlatform( M\Platform( 'android' , 'ios' ) )
                ->setAudience( M\Audience( M\registration_id( $jpushid ) ) )
                ->setMessage( M\message( $content , $title , $content_type , $array ) )
                ->send();
            return $result1;
        }

    }


}