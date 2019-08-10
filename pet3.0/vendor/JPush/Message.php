<?php
use \JPush\PushPayload;
use \JPush\Client;
use \JPush\Exceptions\JPushException;
class Message{
    const APP_KEY = "9d379faa31de116fe128f1d7";
    const SECRET = "6e597b9d95ce3d6c52c5daca";
    public $client;
    //平台
    public $platform = [1=>"all",2=>['android','ios'],3=>'android',4=>'ios'];
    //推送类型(1所有，2别名，3标签)
    public $send_type = [1=>'all',2=>'alias',3=>'tag'];

    public function __construct(){
        include BASE_PATH.'/vendor/JPush/Client.php';
        include BASE_PATH.'/vendor/JPush/Exceptions/JPushException.php';
        include BASE_PATH.'/vendor/JPush/Exceptions/APIConnectionException.php';
        include BASE_PATH.'/vendor/JPush/Exceptions/APIRequestException.php';
        include BASE_PATH.'/vendor/JPush/Exceptions/ServiceNotAvaliable.php';
        include BASE_PATH.'/vendor/JPush/Config.php';
        include BASE_PATH.'/vendor/JPush/PushPayload.php';
        include BASE_PATH.'/vendor/JPush/ReportPayload.php';
        include BASE_PATH.'/vendor/JPush/SchedulePayload.php';
        include BASE_PATH.'/vendor/JPush/Http.php';
        include BASE_PATH.'/vendor/JPush/DevicePayload.php';
        include BASE_PATH.'/vendor/JPush/AdminClient.php';
        try{
            $this->client = new Client(self::APP_KEY, self::SECRET);
        }catch (\Exception $e){
            echo "The exception code is: " . $e->getCode();
        }
    }

    public function indexAction(){
        $pusher = $this->client->push();
        $pusher->setPlatform($this->platform[2]);
        $pusher->addAllAudience();
        $pusher->setNotificationAlert('Hello, JPush');
        try {
            $pusher->send();
            echo "推送成功";
        } catch (JPushException $e) {
            // try something else here
            echo $e;
        }
    }

    /**
     * $type int 推送类型 （0点赞 1评论 2回复）
     * $uid string 接收别名（接收用户ID）
     * $pid string 发送别名（发送用户ID）
     * $pnickname string 发送用户昵称
     * $pavatar string 发送用户头像 （http://www.zhishengwh.com/xxx.png）
     * $data array 推送数据
     *
     * $platform_id int 请参考 $platform
     * $send_type_id int 请参考 $send_type
     *
     * return "success"|"error"
     */
    public function jpapi($type,$uid,$pid,$pnickname,$pavatar,$data,$cate,$feedbackuer="",$new=array(),$platform_id = 2,$send_type_id = 2,$tag=""){
        $str = "";
        $messagestr = "";
        $title = "";
        $pusher = $this->client->push();
        //设置发送平台
        $pusher = $pusher->setPlatform($this->platform[$platform_id]);
        //选择发送类型
        $send_id = $this->send_type[$send_type_id];
        switch ($send_id){
            case 'all':$pusher = $pusher->addAllAudience();break;
            case 'alias' :$pusher = $pusher->addAlias($uid);break;
            case 'tag' :$pusher = $pusher->addTag($tag);break;
        }
        if($pavatar == ""){
            $avatar = "http://test.pettap.cn/dx.png";
        }else{
            $avatar = $pavatar;
        }
        switch ($type){
            case 0:$str = "赞了你的帖子：";break;
            case 1:$str = "评论了你";break;
            case 2:
                if(!empty($feedbackuer)) {
                    $str = "回复了" . $feedbackuer;
                }
                else{
                    $str="回复了你";
                }
                break;
            case 4:$str = "在您附近发布了";break;
            case 5:$str = "您的订单已发货，请点击查看";break;
        }
        switch ($cate){
            case 1:
                if(is_array($uid))
                {
                    foreach ($uid as $value)
                    {
                        $url = "pettap://rescue_action_notifications?id={$value}";
                    }
                }
                else{
                    $url = "pettap://rescue_action_notifications?id={$uid}";
                }
                break;
            case 2:
                if(is_array($uid))
                {
                    foreach ($uid as $value)
                    {
                        $url = "pettap://moment_action_notifications?id={$value}";
                    }
                }
                else{
                    $url = "pettap://moment_action_notifications?id={$uid}";
                }
                break;
            case 3:
                if(is_array($uid))
                {
                    foreach ($uid as $value)
                    {
                        $url = "pettap://order_notifications?id={$value}";
                    }
                }
                else{
                    $url = "pettap://order_notifications?id={$uid}";
                }
                break;
            case 4:
                $url = "pettap://rescue_notification?id={$pid}&uid={$new['puid']}&avatar={$avatar}&longitude={$new['longitude']}&latitude={$new['latitude']}&m_id={$new['m_id']}&viewstatus={$new['viewstatus']}";
                break;
        }
        if($pnickname == ""){
            $messagestr = $pid.$str;
            $title = $pid;
        }else{
            $messagestr = $pnickname.$str;
            $title = $pnickname;
        }

        //$alert = $messagestr.json_encode($data);
        $alert=$messagestr.":".$data;
        $datamessage = [
            "title"=>$title,
            "large_icon"=>$avatar,
            "intent"=>[
                "url"=>$url,
            ],
            "extras"=>[
                "sendtime"=>time(),
                "url"=>$url,
                "large_icon"=>$avatar,
                "msg_content"=>$str,
                "title"=>$title
            ]
        ];

        $iosmessage = [
            "alert"=>$title,
            "sound"=>"default.caf",
            "badge"=>1,
            "extras"=>[
                "title"=>$title,
                "url"=>$url,
                "large_icon"=>$pavatar,
                "msg_content"=>$data
            ]
        ];

        switch ($platform_id){
            case 1:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 2:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 3:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 4:
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
        }
        try {
            $pusher->send();
            return 0;
        } catch (JPushException $e) {
            return 0;
        }
    }

    public  function jpapiall($title,$data,$pavatar,$url,$time,$platform_id = 2,$send_type_id = 1,$tag=""){
        $pusher = $this->client->push();
        //设置发送平台
        $pusher = $pusher->setPlatform($this->platform[$platform_id]);
        //选择发送类型
        $pusher = $pusher->addAllAudience();
        $alert=$data;
        $datamessage = [
            "alert"=>$title,
            "title"=>$title,
            "large_icon"=>$pavatar,
            "intent"=>[
                "url"=>"pettap://notifications",
            ],
            "extras"=>[
                "title"=>$title,
                "url"=>"pettap://notifications",
                "sendtime"=>$time,
                "large_icon"=>$pavatar,
                "msg_content"=>$data
            ]
        ];

        $iosmessage = [
            "alert"=>$title,
            "sound"=>"default.caf",
            "badge"=>1,
            "extras"=>[
                "title"=>$title,
                "url"=>"pettap://notifications",
                "sendtime"=>$time,
                "large_icon"=>$pavatar,
                "msg_content"=>$data
            ]
        ];
        switch ($platform_id){
            case 1:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 2:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 3:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 4:
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
        }
        try {
            $pusher->send();
            return 0;
        } catch (JPushException $e) {
            return 0;
        }
    }

    public function send($arr=array()){
        $this->jpapi($arr['type'],$arr['uid'],$arr['pid'],$arr['pnickname'],$arr['pavatar'],$arr['data'],$arr['cate'],$arr['feedbackuser']);
    }

    public function sendall($arr=array()){
        $this->jpapiall($arr['title'],$arr['content'],$arr['icon'],$arr['url'],$arr['sendtime'],1);
    }
    public function sendmap($arr=array(),$new=array()){
        $this->jpapi($arr['type'],$arr['uid'],$arr['pid'],$arr['pnickname'],$arr['pavatar'],$arr['data'],$arr['cate'],$arr['feedbackuser'],$new);
    }

}