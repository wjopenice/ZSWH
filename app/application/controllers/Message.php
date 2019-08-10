<?php
use Yaf\Application;
use Yaf\Dispatcher;
use \JPush\PushPayload;
use \JPush\Client;
use \JPush\Exceptions\JPushException;

class MessageController extends Yaf\Controller_Abstract{
    const APP_KEY = "07af9a703cb1999c0f4a5404";
    const SECRET = "b62e73366f5034a028143a18";
    public $db;
    public $client;
    //平台
    public $platform = [1=>"all",2=>['android','ios'],3=>'android',4=>'ios'];
    //推送类型(1所有，2别名，3标签)
    public $send_type = [1=>'all',2=>'alias',3=>'tag'];

    public function init(){
        Dispatcher::getInstance()->autoRender(false);
        $this->db = new dbModel();
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
        } catch (\JPush\Exceptions\JPushException $e) {
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
    public function jpapi($title,$data,$pavatar,$url,$time,$platform_id = 3,$send_type_id = 1,$tag=""){
        $pusher = $this->client->push();
        //设置发送平台
        $pusher = $pusher->setPlatform($this->platform[$platform_id]);
        //选择发送类型
        $send_id = $this->send_type[$send_type_id];
        $pusher = $pusher->addAllAudience();
        $alert=$data;
        $datamessage = [
            "title"=>$title,
            "large_icon"=>$pavatar,
            "intent"=>[
                "url"=>$url,
            ],
            "extras"=>[
                "title"=>$title,
                "url"=>$url,
                "sendtime"=>$time,
                "large_icon"=>$pavatar,
                "msg_content"=>$data,
            ]
        ];
        $iosmessage = [
            "alert"=>$title,
            "sound"=>"",
            "badge"=>1,
            "extras"=>[
                "title"=>$title,
                "url"=>"http://www.baidu.com",
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
                $pusher->androidNotification($alert,$datamessage);
                //设置自定义推送
                $pusher->message($alert,$datamessage);
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
            echo json_encode(['code'=>0,'message'=>"success"]);exit;
        } catch (\JPush\Exceptions\JPushException $e) {
            echo json_encode(['code'=>0,'message'=>"error"]);exit;
        }
    }
    public function sendAction($arr=array()){
         $this->jpapi($arr['title'],$arr['content'],$arr['icon'],$arr['url'],$arr['sendtime']);
    }
    public function test2Action($id=null){
        echo $id;
        exit;
    }}