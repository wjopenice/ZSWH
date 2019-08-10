<?php
use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;
use app\core\Pdb;
class LoginController extends Controller
{
	public $error;
	public $pdb;
	public $session;
	
	public function initialize(){
		include APP_PATH."/core/Errorcode.php";
        $this->error = \app\core\Errorcode::getCodeConfig();
	        include APP_PATH."/core/Pdb.php";
    	$this->pdb = new \app\core\Pdb();
		include APP_PATH."/core/Session.php";
		$this->session = new \app\core\Session();
        $this->pdb->action("set names utf8mb4");
    }
	
	public function ajax_return($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]],320);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data],320);exit;
        }
    }
	
	//API注册接口
	public function registerAction(){	
		$reqdata = $this->request->getPost();
		$phone = $reqdata['phone'];
		$dynamic_code = $reqdata['dynamic_code'];
		$password = $reqdata['password'];
		$type=$reqdata['type'];//设备类型
		//检测验证码
		$this->ajaxsms($phone, $dynamic_code);
		//获得数据库参数
		$tabname = "zsuser";
		$zs_user=new Zsuser();
        $user = $zs_user::findFirst(" mobile_num = '{$phone}' ");
        if(!empty($user))
        {
            $this->ajax_return(1003);
        }
        //注册
        $bool = $zs_user->register($phone,$password,$type,$tabname);
        if($bool){
            $result = $zs_user::findFirst("mobile_num = '{$phone}'")->toArray();
            $arrx=get_userinfo_data($result['id']);
            $result['totalposts'] = $arrx['totalposts'] ;
            // $result['collection_num'] = 0;
            $result['commonweal_price'] =$arrx['commonweal_price'] ;
            $this->ajax_return(0,$result);
        }else{
            $this->ajax_return(500);
        }
	}
    //检测验证码
    public function ajaxsms($phone,$lockcode){
        $locktime = time();
        $zs_short_message = new Zsshortmessage();
        $telsvcode = $zs_short_message::findFirst(" phone = '{$phone}' and code = '{$lockcode}' ");
        if($telsvcode){
            $datatime = $telsvcode->send_time;
            if( ($locktime - $datatime) < 600){
                $datacode = $telsvcode->code;
                if($datacode == $lockcode){
                    return true;
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


    //填写资料
    public function fillinfoAction(){
        $reqdata = $this->request->getPost();
        $user=new Zsuser();
        if ($this->request->hasFiles() == true) {
            $file = $this->uploadone("avatar", "user");
        }
        else{
            $file="";
        }
        $code = $user->fillinfo($reqdata['phone'],$reqdata['birthday'],$reqdata['sex'],$reqdata['nick_name'],$file);
        if(is_numeric($code)) {
            $this->ajax_return($code);
        }
        else{
            $this->ajax_return(0,$code);
        }
    }
    //API修改用户信息
    public function editinfoAction(){
        $reqdata = $this->request->getPost();
        $zsuer=new Zsuser();
        $arrdata =$zsuer::findFirst("id = '{$reqdata['id']}'");
        if ($this->request->hasFiles() == true) {
            $file = $this->uploadone("avatar", "user");
        }
        else{
            $file=$arrdata->avatar;
        }
        $code=$zsuer->useredit($reqdata,$file);
        if (is_numeric($code))
        {
            $this->ajax_return($code);
        }
        else{
            $this->ajax_return(0,$code);
        }

    }
    //微信授权
    public function  authorizeAction(){
        $reqdata = $this->request->getPost();
        $wechat=$this->pdb->field("*")->table("zsuser")->where("wechat = '{$reqdata['sdkid']}'")->find();
            if($wechat)
            {
                $this->ajax_return(1014);
            }
            $data['wechat']=$reqdata['sdkid'];

        $bool = $this->pdb->action($this->pdb->updateSql("zsuser",$data," id = {$reqdata['id']}"));
        if($bool)
        {
            $this->ajax_return(0);
        }
        else{
            $this->ajax_return(500);
        }
    }
    //API验证码登录接口
    public function phoneloginAction(){
        $reqdata = $this->request->getPost();
        $phone = $reqdata['phone'];
        $dynamic_code=$reqdata['dynamic_code'];
        $zsuser=new Zsuser();
       $code= $zsuser->phonelogin($phone,$dynamic_code);
       if(is_numeric($code))
       {
           $this->ajax_return($code);
       }
       else{
           $this->ajax_return(0,$code);
       }
    }

    //API密码登录接口
    public function pwdloginAction(){
        $reqdata = $this->request->getPost();
        $phone = $reqdata['phone'];
        $password=$reqdata['password'];
        $zsuser=new Zsuser();
        $code= $zsuser->pwdlogin($phone,$password);
        if(is_numeric($code))
        {
            $this->ajax_return($code);
        }
        else{
            $this->ajax_return(0,$code);
        }
    }

    //API退出接口
    public function logoutAction(){
        $reqdata = $this->request->getPost();
        $zs_user=new Zsuser();
        $resultdb = $zs_user::findFirst("id = '{$reqdata['id']}'");
        if(empty($resultdb)){
            $this->ajax_return(1013);
        }else{
            $resultdb->is_online = '1';
            $resultdb->save();
            $this->ajax_return(0);;
        }
    }
    //第三方登录
    public function buildloginAction(){
        $reqdata = $this->request->getPost();
        $zs_user=new Zsuser();
        $sdkid = $reqdata['sdkid'];
        $logintype=$reqdata['logintype'];
        $code= $zs_user->buildlogin($sdkid,$logintype);
        if(is_numeric($code))
        {
            $this->ajax_return($code);
        }
        else{
            $this->ajax_return(0,$code);
        }
    }
    ////SDK手机号验证（自带注册）
    public function sdkloginphoneAction(){
        $reqdata = $this->request->getPost();
        $zs_user=new Zsuser();
        $sdkid = $reqdata['sdkid'];
        $logintype=$reqdata['logintype'];
        $mobile_num = $reqdata['phone'];
        $dynamic_code = $reqdata['dynamic_code'];
        $type=$reqdata['type'];
        $code= $zs_user->sdkloginphone($sdkid,$type,$mobile_num,$dynamic_code,$logintype);
        if(is_numeric($code))
        {
            $this->ajax_return($code);
        }
        else{
            $this->ajax_return(0,$code);
        }
    }

    //用户协议
    public function useragAction(){
        $arrData=$this->pdb->field("*")->table('zsuserag')->order("id desc")->find();
        $arrData['content'] = htmlspecialchars_decode($arrData['content']);
        $this->ajax_return(0,$arrData);
    }

	//API用户信息接口
	public function infoAction(){
		$reqdata = $this->request->getPost();
        $userid = $reqdata['id'];
       $zsuser=new Zsuser();;
        $code=$zsuser->getinfo($userid);
        if(is_numeric($code)) {
            $this->ajax_return($code);
        }
        else{
            $this->ajax_return(0,$code);
        }

	}

	public function debugAction(){
	  	$reqdata = $this->request->getPost();
        $debug = $reqdata['debug'];
		$phone = $reqdata['phone'];
		$debug=new Zsdebug();
        $debug->debug = $debug;
        $debug->phone= $phone;
        $debug->create_time = time();
		$bool = $debug->save();
		if($bool){
			$this->ajax_return(0); 
		}else{
			$this->ajax_return(500); 
		}	
	}

	public function aloginAction(){
		if($this->request->isPost()){
			$user = $this->request->getPost("username");
			$pass = $this->request->getPost("password");
			if($user == 'admin' && $pass == 'wifi'){
				$this->session->set("username",$user);
				success("欢迎进入宠爱之家后台","/admin/index");
				exit;
			}else{
				error("账号密码错误");
			}
		}
	}
	
	public function alogoutAction(){
		$this->session->remove("username");
		success("退出成功","/login/alogin");
	}
	
	//文件上传
	public function uploadone($filename,$path){
		$time = time();
        $dir = BASE_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
		$fileArr = "";
        foreach ( $this->request->getUploadedFiles($filename) as $file){
            $fileArr = "/".$path."/".$time."/".$file->getName();
            $file->moveTo($dir."/".$file->getName());
        }
		return $fileArr;
    }
	
    public function codeAction(){
        include APP_PATH."/core/Image.php";
        header("content-type:image/png");
        \app\core\Image::code(160,56,25,15,35,35,"/fonts/MSYHBD.TTC");
        $this->view->disable();
    }

}