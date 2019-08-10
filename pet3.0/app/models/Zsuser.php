<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;
class Zsuser extends Model{
    public $id;
    public $uid;
	public $mobile_num;
	public $nick_name;
	public $password;
	public $type;
	public $avatar;
	public $sex;
	public $user_age;
    public $u_sign;
    public $location_province;
    public $location_city;
    public $user_level;
    public $access_token;
    public $competence;
    public $lock_status;
    public $user_exp;
    public $membership_points;
    public $register_time;
    public $register_ip;
    public $last_activity_date;
    public $is_online;
    public $sina;
    public $wechat;
    public $qq;
    public $longitude;
    public $latitude;
    public $db;
    public function initialize(){
        $this->pdb = new Pdb();
        $this->pdb->action("set names utf8mb4");
    }

    //验证码登录
    public function phonelogin($phone,$dynamic_code){
        $zs_user=new Zsuser();
        $resultdb = $zs_user::findFirst("mobile_num = '{$phone}'");
         if(empty($resultdb)){
           return 1004;
        }
        else{
            $zs_short_message = new Zsshortmessage();
            $locktime = time();
            $telsvcode = $zs_short_message::findFirst(" phone = '{$phone}' and code = '{$dynamic_code}' ");
            if($telsvcode) {
                $datatime = $telsvcode->send_time;
                if (($locktime - $datatime) < 600) {
                    $datacode = $telsvcode->code;
                    if ($datacode == $dynamic_code) {
                        $resultdb->is_online = '0';
                        $resultdb->register_ip = $_SERVER['REMOTE_ADDR'];
                        $resultdb->last_activity_date = time();
                        $resultdb->save();
                        //积分系统
                        $this->user_bp($resultdb->id, "+5积分", time(), "登录", 5);
                        $arrx=get_userinfo_data($resultdb->id);
                        $result = $zs_user::findFirst("mobile_num = '{$phone}'")->toArray();
                        $result['totalposts'] = $arrx['totalposts'] ;
                        $result['commonweal_price'] =  $arrx['commonweal_price'] ;
                        return $result;
                    } else {
                        return 1002;
                    }
                } else {
                    return 1005;
                }
            }
            else{
                return 1002;
            }
        }
    }

    //密码登录
    public function pwdlogin($phone,$password){
        $password=$this->hmac256($password);
        $zsuser=new Zsuser();
        $resultdb = $zsuser::findFirst(" mobile_num = '{$phone}'");
        $time=time();

        if(empty($resultdb)){
            return 1004;
        }
        else if ($password!=$resultdb->password)
        {
            return 1007;
        }
        else if( ($resultdb->is_online == '0')&&(($time- $resultdb->last_activity_date) < 604800)){
            //7天之内免登录
            $resultdb->register_ip =$_SERVER['REMOTE_ADDR'];
            $resultdb->last_activity_date= $time;
            $resultdb->save();
            //积分系统
            $this->user_bp($resultdb->id,"+5积分",time(),"7天免登录",5);
            $arrx=get_userinfo_data($resultdb->id);
            $result = $zsuser::findFirst("mobile_num = '{$phone}'")->toArray();
            $result['totalposts'] = $arrx['totalposts'] ;
             $result['commonweal_price'] =  $arrx['commonweal_price'] ;
            return $result;
        }else{
          $res = $zsuser::findFirst(" mobile_num = '{$phone}' and password='{$password}'");
                if($res){
                    $res->is_online = '0';
                    $res->register_ip =$_SERVER['REMOTE_ADDR'];
                    $res->last_activity_date= $time;
                    $res->save();
                    //积分系统
                    $this->user_bp($res->id,"+5积分",time(),"登录",5);
                    $arrx=get_userinfo_data($resultdb->id);
                    $result = $zsuser::findFirst("mobile_num = '{$phone}'")->toArray();
                    $result['totalposts'] = $arrx['totalposts'] ;
                    $result['commonweal_price'] =  $arrx['commonweal_price'] ;
                    return $result;
                }else{
                    return 1007;
                }

        }
    }
    //用户积分
    public  function user_bp($user,$bp,$optime,$bp_type,$jf){
        $zs_user_bp = new Zsuserbp();
        $zs_user=new Zsuser();
        $ptime = date("Y-m-d",$optime); //时间戳转为日期
        $result = $zs_user_bp::findFirst(" user = {$user} and optime = '{$ptime}' ");
        $user_result = $zs_user::findFirst(" id = {$user} ");
        if(!empty($user_result)){
            if(empty($result)){
                $user_result->user_exp= (int)$user_result->user_exp + $jf;
                $user_result->user_level= $this->switch_user_level($user_result->user_exp);
                $user_result->save();
                //用户积分日志
                $zs_user_bp->user= $user;
                $zs_user_bp->bp = $bp;
                $zs_user_bp->optime = $ptime;
                $zs_user_bp->bp_type = $bp_type;
                $zs_user_bp->save();
            }
        }
    }
    //用户等级
   public function switch_user_level($level){
        $user_level = 1;
        switch(true){
            case $level>=0 && $level<50: $user_level = 1; break;
            case $level>=50 && $level<130: $user_level = 2; break;
            case $level>=130 && $level<290: $user_level = 3; break;
            case $level>=290 && $level<610: $user_level = 4; break;
            case $level>=610 && $level<1250: $user_level = 5; break;
            case $level>=1250 && $level<2530: $user_level = 6; break;
            case $level>=2530 && $level<5090: $user_level = 7; break;
            case $level>=5090 && $level<10210: $user_level = 8; break;
            case $level>=10210 && $level<20450: $user_level = 9; break;
            case $level>=20450 : $user_level = 10; break;
            default:$user_level = 1; break;
        }
        return $user_level;
    }
    //注册接口
    public function register($phone,$password='',$type,$tabname,$logintype='',$sdkid=''){
        $user=new Zsuser();
        $user->uid = "C".substr(time(),1);
        $user->mobile_num= $phone;
        $user->password = $this->hmac256($password);
        $user->avatar = "";
        $user->nick_name = "";
        $user->type=$type;
        $user->sex = "保密";
        $user->user_age= 0;
        $user->u_sign="";
        $user->birthday="";
        $user->location_province="";
        $user->location_city="";
        $user->user_level='0';
        $user->competence="宠爱用户";
        $user->lock_status='0';
        $user->user_exp='0';
        $user->membership_points='0';
        $user->is_online='0';
        if(empty($logintype)) {
            $user->sina = "";
            $user->wechat = "";
            $user->qq = "";
        }
        else{
            $user->password=$this->hmac256('123456');
            $user->{$logintype}=$sdkid;
        }
        $outtime = $this->get7day(time());
        $rand1 = range("a","z");
        $rand2 = range("0","9");
        $rand3 = range("A","Z");
        $randstr = $rand1[0].$rand2[0].$rand3[0];
        $basestr = $user->uid.$outtime.$randstr;
        $user->access_token = $this->hmac256($basestr);
        $user->register_time = time();
        $user->register_ip = $_SERVER['REMOTE_ADDR'];
        $user->last_activity_date = time();
        $bool =$user->save();
        return $bool;
    }

    //填写资料
    public function fillinfo($phone,$birthday,$sex,$nick_name,$file=""){
        $user=new Zsuser();
        $phone = $phone;
        $nowyear=date("Y");
        $useryear=substr($birthday,0,4);
        $result = $user::findFirst("mobile_num = '".$phone."'");
        if($result){
            $this->pdb->action("set names utf8mb4");
            $data['sex']=$sex;
            $data['birthday']=$birthday;
            $data['nick_name']=$this->parseEmojiTounicode($nick_name);;
            $data['user_age']=$nowyear-$useryear;;
            $data['avatar']=$file;
            $bool = $this->pdb->action($this->pdb->updateSql("zsuser",$data," id = {$result->id}"));
            if($bool)
            {
                $arrx=get_userinfo_data($result->id);
                $result = $user::findFirst("mobile_num = '{$phone}'")->toArray();
                $result['totalposts'] = $arrx['totalposts'] ;
                $result['commonweal_price'] =  $arrx['commonweal_price'] ;
                return $result;
           }
            else{
                return 500;
            }

        } else {
            return 1004;
        }
    }

    //第三方登录
    public function buildlogin($sdkid,$logintype){
        $user=new Zsuser();
        $resultdb = $user::findFirst("{$logintype} = '{$sdkid}'");
        if(!empty($resultdb)){
            $resultdb->is_online = '0';
            $resultdb->register_ip = $_SERVER['REMOTE_ADDR'];
            $resultdb->last_activity_date = time();
            $resultdb->{$logintype}=$sdkid;
            $resultdb->save();
            //积分系统
            //积分系统
            $this->user_bp($resultdb->id, "+5积分", time(), "第三方登录", 5);
            $arrx = get_userinfo_data($resultdb->id);
            $result = $user::findFirst("id = '{$resultdb->id}'")->toArray();
            $result['totalposts'] = $arrx['totalposts'] ;
            $result['commonweal_price'] =  $arrx['commonweal_price'] ;
            return $result;
        }else{
            return 0;
        }
    }
    //SDK手机号验证（自带注册）
    public function sdkloginphone($sdkid,$type,$phone,$dynamic_code,$logintype){
        //验证码判断
        $zs_short_message = new Zsshortmessage();
        $locktime = time();
        $telsvcode = $zs_short_message::findFirst(" phone = '{$phone}' and code = '{$dynamic_code}' ");
        if($telsvcode) {
            $datatime = $telsvcode->send_time;
            if (($locktime - $datatime) < 600) {
                $datacode = $telsvcode->code;
                $lockcode = $dynamic_code;
                if ($datacode == $lockcode) {
                    $zs_user=new Zsuser();
                    $resultdb =  $zs_user::findFirst(" mobile_num = '{$phone}'  ");
                    if (!empty($resultdb)) {
                        //登录
                        $resultdb->is_online = '0';
                        $resultdb->register_ip = $_SERVER['REMOTE_ADDR'];
                        $resultdb->last_activity_date = time();
                        $resultdb->{$logintype}=$sdkid;
                        $bool=$resultdb->save();
                        //积分系统
                        $this->user_bp($resultdb->id, "+5积分", time(), "第三方登录", 5);
                    } else {
                        //注册
                        $bool = $this->register($phone,'',$type,'zsuser',$logintype,$sdkid);
                    }
                    if ($bool) {
                        $resultdb =  $zs_user::findFirst(" mobile_num = '{$phone}'  ");
                        $this->user_bp($resultdb->id, "+5积分", time(), "第三方登录", 5);
                        $arrx = get_userinfo_data($resultdb->id);
                        $result = $zs_user::findFirst("id = '{$resultdb->id}'")->toArray();
                        $result['totalposts'] = $arrx['totalposts'] ;
                        $result['commonweal_price'] =  $arrx['commonweal_price'] ;
                       // $result['nick_name']=$this->parseHtmlemoji($result['nick_name']);
                        return $result;
                    } else {
                        return 500;
                    }
                } else {
                    return 1002;
                }
            } else {
                return 1005;
            }
        }
        else{
            return 1002;
        }
    }

    //用户实时跟新定位
    public function lbs($longitude,$latitude,$id){
        $zs_user=new Zsuser();
        $userData = $zs_user::findFirst("id = '$id'");
        if(empty($userData)){
            return 1013;
        }
        $userData->longitude = $longitude;
        $userData->latitude= $latitude;
        $bool=$userData->save();
        if($bool){
            return 0;
        }else {
            return 500;
        }
    }

    //获取用户信息
    public function getinfo($id)
    {
        $this->pdb->action("set names utf8mb4");
        $data=$this->pdb->action("select * from zsuser where id={$id}");
        if(empty($data))
        {
            return 1013;
        }
        $arrData=$data[0];
        $arrx = get_userinfo_data($id);
        $arrData['totalposts'] = $arrx['totalposts'] ;
        $arrData['commonweal_price'] =  $arrx['commonweal_price'] ;
       $switch=Zsswitch::findFirst("id=1");
        $arrData['expire']=$switch->expire;
        $arrData['u_sign']=$this->parseHtmlemoji($data[0]['u_sign']);
        if(!empty($data[0]['nick_name'])) {
            $arrData['nick_name'] = $this->parseHtmlemoji($data[0]['nick_name']);
        }
        else{
            $arrData['nick_name']=$data[0]['uid'];
        }
        return $arrData;

    }

    //用户基本信息修改SDK接口
    public function useredit($reqdata,$file){
        if($reqdata){
            $data['avatar']=$file;
            if($reqdata['nick_name']) {
                $data['nick_name'] = $this->parseEmojiTounicode($reqdata['nick_name']);
            }
            if($reqdata['sex']) {
                $data['sex'] = $reqdata['sex'];
            }
            if($reqdata['birthday']) {
                $nowyear=date("Y");
                $useryear=substr($reqdata['birthday'],0,4);
                $data['user_age']= $nowyear - $useryear;
                $data['birthday'] = $reqdata['birthday'];
            }
            if($reqdata['u_sign']) {
                $data['u_sign'] = $this->parseEmojiTounicode($reqdata['u_sign']);
            }
            if($reqdata['sdkid'])
            {
                $wechat=$this->pdb->field("*")->table("zsuser")->where("wechat = '{$reqdata['sdkid']}'")->find();
                if($wechat)
                {
                    if($reqdata['id']!=$wechat['id'])
                    {
                        return 1014;
                    }
                }
                $data['wechat']=$reqdata['sdkid'];
            }
            if($reqdata['location_province']) {
                $data['location_province'] = $reqdata['location_province'];
            }
            if($reqdata['location_province']) {
                $data['location_city'] = $reqdata['location_city'];
            }
            $this->pdb->action("set names utf8mb4");
           // echo $this->pdb->updateSql("zsuser",$data," id = {$reqdata['id']}");exit;
            $bool = $this->pdb->action($this->pdb->updateSql("zsuser",$data," id = {$reqdata['id']}"));
            if($bool)
            {
                return $file;
            }
            else{
             return $file;
            }
        }else{
            return 104;
        }

    }
    public function parseEmojiTounicode($stremoji)
    {
        $text = preg_replace_callback('/[\xf0-\xf7].{3}/', function ($r) {
            return '@E' . base64_encode($r[0]);
        }, $stremoji);
        return $text;
    }
        public function parseHtmlemoji ($text)
        {
            $text_r = preg_replace_callback('/@E(.{6}==)/', function ($r) {
                return base64_decode($r[1]);
            }, $text);
            return $text_r;
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

    public function hmac256($data){
        return hash_hmac("sha256",$data,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
    }
    public function get7day($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
    }
}