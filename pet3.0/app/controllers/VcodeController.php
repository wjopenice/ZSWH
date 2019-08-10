<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
class VcodeController extends Controller
{
	
    const APP_KEY = "MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
	
	public $error;
	
	public function initialize(){
		include APP_PATH."/core/Errorcode.php";
        $this->error = \app\core\Errorcode::getCodeConfig();
    }
	
	public function testAction(){
	
		$this->data = file_get_contents("php://input");
		file_put_contents(dirname(__FILE__).'/code.txt', "android".$this->data.PHP_EOL."\r\n",FILE_APPEND);
		$this->request = new Request();
		$this->data =  $this->request;
		file_put_contents(dirname(__FILE__).'/code.txt', "IOS".json_encode($this->data).PHP_EOL."\r\n",FILE_APPEND);
		$this->data =  $_POST;
		file_put_contents(dirname(__FILE__).'/code.txt', "POST".json_encode($this->data).PHP_EOL."\r\n",FILE_APPEND);
		$this->data =  $_GET;
		file_put_contents(dirname(__FILE__).'/code.txt', "GET".json_encode($this->data).PHP_EOL."\r\n",FILE_APPEND);
		
		$this->ajax_return(0); 
	}
	
	public function ajax_return($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]],320);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data],320);exit;
        }
    }

	  
    /**
      * 发送验证码判断
      */
    public function sendvcodeAction() {
        $reqdata = $this->request->getPost("phone");
        $phone = isset($reqdata)?$reqdata:"";
        $name = $phone;
        if (empty($phone) || empty($name)) {
			$this->ajax_return(1000); 
        }
        $this->telsvcodeAction($phone);
    }
    /**
      * 发送手机安全码
      */
    public function telsvcodeAction($phone=null,$delay=10,$flag=true) {
        include APP_PATH."/core/Xigu.php";
        $zs_short_message = new Zsshortmessage();
        if (empty($phone)) {
          	$this->ajax_return(1000); 
        }
        //产生手机安全码并发送到手机且存到session
        $rand = rand(100000,999999);
        $smsconfig = ['sms_set' => [
            'smtp' => 'MDAwMDAwMDAwMK62sG1_enZnf7HJmLHc',
            'smtp_account' => 'MDAwMDAwMDAwMLq5qLB_oIJnf4u73bDc',
            'smtp_password' => '273',
            'smtp_port' => '25615'
        ]];
        $xigu = new \app\core\Xigu($smsconfig);
        $param = $rand.",".$delay;
        $result = json_decode($xigu->sendSM($smsconfig['sms_set']['smtp_account'],$phone,$smsconfig['sms_set']['smtp_password'],$param),true);
        if($result['send_status'] != '000000') {
          	$this->ajax_return(1001); 
        }
        // 存储短信发送记录信息
        $result['create_time'] = time();
        $result['pid']=0;

		file_put_contents(dirname(__FILE__).'/code.txt', "SMS".json_encode($result)."=====".$rand.PHP_EOL."\r\n",FILE_APPEND);

        $zs_short_message->phone = $phone;
        $zs_short_message->code = $rand;
        $zs_short_message->send_time = $result['create_time'];
        $zs_short_message->smsId = $result['smsId'];
        $zs_short_message->create_time = $result['create_time'];
        $zs_short_message->pid = $result['pid'];
        $status = ($result['code'] == 200) ? 1 : 0;
        $zs_short_message->status = $status;
        $zs_short_message->ratio = 0;

		file_put_contents(dirname(__FILE__).'/code.txt', "SMS".json_encode($result)."=====".$rand.PHP_EOL."\r\n",FILE_APPEND);
        $bool = $zs_short_message->save();
        //$telsvcode['code']=$rand;
        //$telsvcode['phone']=$phone;
        //$telsvcode['time']=$result['create_time'];
        //$telsvcode['delay']=$delay;
        //$this->session->set("telsvcode", $telsvcode);
        if($flag){
            $this->ajax_return(0); 
        }else{
            $this->ajax_return(0); 
        }
    }
    /**
      * 手机验证码验证
      */
    public function ajaxsmsAction(){
        $reqdata = $this->request->getPost();
        $locktime = time();
        $zs_short_message = new Zsshortmessage();
        $phone = $reqdata['phone'];
        $lockcode = $reqdata['dynamic_code'];
        $telsvcode = $zs_short_message::findFirst(" phone = '{$phone}' and code = '{$lockcode}' ");
        if($telsvcode){
            $datatime = $telsvcode->send_time;
            if( ($locktime - $datatime) < 600){
                $datacode = $telsvcode->code;
                if($datacode == $lockcode){
                    setcookie('mobile_num',$phone,time()+60*60);
					$this->ajax_return(0); 
                }else{
                  	$this->ajax_return(1002); 
                }
            }else{
              	$this->ajax_return(1005); 
            }
        }else{
          	$this->ajax_return(1002); 
        }
    }
    //手机号验证
    public function phoneAction(){
        include APP_PATH."/core/Pdb.php";
        $pdb = new \app\core\Pdb();
        $reqdata = $this->request->getPost();
        $phone=$reqdata['phone'];
        if(strlen($phone) == "11")
        {
            $check = '/^(1(([35789][0-9])|(47)))\d{8}$/';
            if (preg_match($check, $phone)) {
                 $user =$pdb->action("select * from zsuser where mobile_num='{$phone}'");
                if($user)
                {
                    $this->ajax_return(1003);
                }
                else{
                    $this->ajax_return(0);
                }
            } else {
                $this->ajax_return(1000);
            }
        }else
        {
            $this->ajax_return(1000);
        }
    }
    public function buildordernoAction(){
        return "pet".date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8)."tap";
    }
    public function get7dayAction($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
    }
    public function hmac256($data){
        return hash_hmac("sha256",$data,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
    }

}
