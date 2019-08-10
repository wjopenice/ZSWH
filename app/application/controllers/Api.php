<?php

class ApiController extends BaseController {
    const APP_KEY = "MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    //手机验证码登录
    public function phoneloginAction(){
        $reqdata =$this->phpinput();
        $arr['phone'] = $reqdata['phone'];
        $arr['password']=$reqdata['password'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("phone = {$arr['phone']}")->findobj();
        if(empty($resultdb)){
            $arr['dynamic_code'] = $reqdata['dynamic_code'];
            $arr['ts'] = $reqdata['ts'];
            $arr['uuid'] = $reqdata['uuid'];
            $sign = $reqdata['sign'];
            $result = $this->issign($arr,$sign);
            if($result){
                $locktime = time();
                $telsvcode = $this->db->field("*")->table("tab_short_message")->where(" phone = '{$arr['phone']}' and code = '{$arr['dynamic_code']}' ")->findobj();
                $datatime = $telsvcode->send_time;
                if( ($locktime - $datatime) < 60){
                    $datacode = $telsvcode->code;
                    $lockcode = $reqdata['dynamic_code'];
                    if($datacode == $lockcode){
                        $user['password']=$this->think_ucenter_md5($arr['password'], UC_AUTH_KEY);
                        $user['register_time']= $arr['ts'];
                        $user['account'] =  $arr['phone'];
                        $user['register_way'] = 2;
                        $user['is_online'] = '1';
                        $user['phone'] =  $arr['phone'];
                        $user['register_ip'] =  server("REMOTE_ADDR");
                        $outtime = $this->get7day($user['register_time']);
                        $rand1 = range("a","z");
                        $rand2 = range("0","9");
                        $rand3 = range("A","Z");
                        $randstr = $rand1[0].$rand2[0].$rand3[0];
                        $basestr = $outtime.$randstr;
                        $bool = $this->db->action($this->db->insertSql("user",$user));
                        $result = $this->db->field("*")->table("tab_user")->where("phone = {$arr['phone']}")->findobj();
                        $zs_user['login_ip'] = server("REMOTE_ADDR");
                        $zs_user['login_time'] = $arr['ts'];
                        $zs_user['token'] =generateToken($result->id, $result->account,$arr['password']);
                       $this->db->action($this->db->updateSql("user",$zs_user," phone = {$arr['phone']}"));
                        //积分系统
                        $this->user_bp($result->id,"+5积分",time(),"登录",5);
                        $arrx=$this->get_userinfo_data($result->id);
                        $result->follow_num=$arrx['follow_num'];
                        $result->fans_num=$arrx['fans_num'];
                        $result->integral=0;
                        $result->token=generateToken($result->id, $result->account,$arr['password']);
                        if($result->sex==0)
                        {
                            $result->sex='男';
                        }
                        else{
                            $result->sex='女';
                        }
                        $this->ajax_return(0,$result);
                    }else{
                        $this->ajax_return(1002);
                    }
                }else{
                    $this->ajax_return(1005);
                }
            }else{
                $this->ajax_return(100);
            }

        }else{
            $this->ajax_return(1003);

        }
    }
    //退出SDK接口
   public function logoutAction(){
        $reqdata = $this->phpinput();
        $arr['u_id'] = $reqdata['id'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("id = {$arr['u_id']}")->find();
        if(empty($resultdb)){
            $this->ajax_return(1010);
        }else{
            $data['is_online'] = '1';
            $this->db->action($this->db->updateSql("user",$data," id = {$arr['u_id']}"));
            $this->ajax_return(0);;
        }
    }
    //找回密码SDK接口
    public function forgetpasswordAction(){
        $reqdata =$this->phpinput();
        $arr['phone'] = $reqdata['phone'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("phone = '{$arr['phone']}'")->find();
        if(empty($resultdb)){
            $this->ajax_return(1004);
        }else{
            $arr['dynamic_code'] = $reqdata['dynamic_code'];
            $phonecode = $this->db->field("*")->table("tab_short_message")->where(" phone = '{$arr['phone']}' and code = '{$arr['dynamic_code']}' ")->find();
            if(!empty($phonecode)){
                $arr['password'] = $reqdata['password'];
                if(!empty($arr['password'])){
                    $data['password'] = $this->think_ucenter_md5($arr['password'], UC_AUTH_KEY);
                    $bool = $this->db->action($this->db->updateSql("user",$data," phone = '{$arr['phone']}'"));
                    if($bool){
                        $this->ajax_return(0);
                    }else{
                        $this->ajax_return(500);
                    }
                }else{
                    $this->ajax_return(1008);
                }
            }else{
                $this->ajax_return(1002);
            }
        }
    }
    //第三方登录SDK接口
    public function sdkloginAction(){
        $reqdata = $this->phpinput();
        $sdkid = $reqdata['sdkid'];
        $type = $reqdata['type'];
        $ts = $reqdata['ts'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("{$type} = '{$sdkid}'")->find();
        if(!empty($resultdb)){
            $zs_user['is_online'] = '0';
            $zs_user['login_ip'] = server("REMOTE_ADDR");
            $zs_user['login_time'] = $ts;
            $zs_user[$type] = $sdkid;
            $this->db->action($this->db->updateSql("user",$zs_user,"{$type} = '{$sdkid}'"));
            //积分系统
            $this->user_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
            $arrx=$this->get_userinfo_data($resultdb['id']);
            $resultdb['follow_num']=$arrx['follow_num'];
            $resultdb['fans_num']=$arrx['fans_num'];
            $resultdb['integral']=0;
            if($resultdb['sex']==0)
            {
                $resultdb['sex']='男';
            }
            else
            {
                $resultdb['sex']='女';
            }
            $this->ajax_return(0,$resultdb);
        }else{
            $this->ajax_return(0);
        }
    }
    //第三方登录手机号验证SDK接口
    public function sdkloginphoneAction(){
       $reqdata = $this->phpinput();
        $sdkid = $reqdata['sdkid'];
        $type = $reqdata['type'];
        $mobile_num = $reqdata['phone'];
        $dynamic_code = $reqdata['dynamic_code'];
        $password=$this->think_ucenter_md5($reqdata['password'], UC_AUTH_KEY);
        $ts = $reqdata['ts'];
        //验证码判断
        $locktime = time();
        $telsvcode = $this->db->field("*")->table("tab_short_message")->where(" phone = '{$mobile_num}' and code = '{$dynamic_code}' ")->find();
        $datatime = $telsvcode['send_time'];
        if( ($locktime - $datatime) < 600){
            $datacode = $telsvcode['code'];
            $lockcode = $reqdata['dynamic_code'];
            if($datacode == $lockcode){
                $resultdb = $this->db->field("*")->table("tab_user")->where(" phone = '{$mobile_num}' and password='{$password}'")->find();
                if(!empty($resultdb)){
                    if($resultdb['sex']==0){
                        $resultdb['sex']='男';
                    }else{
                        $resultdb['sex']='女';
                    }
                    //登录
                    $zs_user['is_online'] = '0';
                    $zs_user['login_ip'] = server("REMOTE_ADDR");
                    $zs_user['login_time'] = $ts;
                    $zs_user[$type] = $sdkid;
                    $bool = $this->db->action($this->db->updateSql("user",$zs_user," phone = '{$mobile_num}'"));
                    //积分系统
                    $this->user_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
                }else{
                    //注册
                    $user['account'] =  $mobile_num;
                    $user['password']=$this->think_ucenter_md5($reqdata['password'], UC_AUTH_KEY);
                    $user['phone'] =  $mobile_num;
                    $user['sex'] =  0;
                    $user['register_time'] =  time();
                    $user['register_ip'] =  server("REMOTE_ADDR");
                    $user['login_time'] =  time();
                    $user['login_ip'] =   server("REMOTE_ADDR");
                    $user['avatar'] = '';
                    $user['is_online'] = '1';
                    $user[$type] = $sdkid;
                    $bool = $this->db->action($this->db->insertSql("user",$user));
                }
                if($bool){
                    $resultdb = $this->db->field("*")->table("tab_user")->where(" phone = '{$mobile_num}'")->find();
                    $zs_user['token'] =generateToken($resultdb['id'], $resultdb['account'],$reqdata['password']);
                    $this->db->action($this->db->updateSql("user",$zs_user," phone = '{$mobile_num}'"));
                    $this->user_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
                    $arrx=$this->get_userinfo_data($resultdb['id']);
                    $resultdb['follow_num']=$arrx['follow_num'];
                    $resultdb['fans_num']=$arrx['fans_num'];
                    $resultdb['integral']=0;
                    $resultdb['token']=generateToken($resultdb['id'],$resultdb['account'],$reqdata['password']);
                    if($resultdb['sex']==0){
                        $resultdb['sex']='男';
                    }else{
                        $resultdb['sex']='女';
                    }
                    $this->ajax_return(0,$resultdb);
                }else{
                    $this->ajax_return(500);
                }
            }else{
                $this->ajax_return(1002);
            }
        }else{
            $this->ajax_return(1005);
        }
    }

    /**
     * 发送验证码判断
     */
    public function sendvcodeAction() {
        $reqdata = $this->phpinput();
        $phone = isset($reqdata['phone'])?$reqdata['phone']:"";
        $name = $phone;
        if (empty($phone) || empty($name)) {
            $this->ajax_return(100);
        }
        $this->telsvcodeAction($phone);
    }
    /**
     * 发送手机安全码
     */
    public function telsvcodeAction($phone=null,$delay=10,$flag=true) {
        include APP_PATH."/application/core/Xigu.php";
        if (empty($phone) || (strlen($phone) != 11)) {
            $this->ajax_return(1000);
        }
        // 产生手机安全码并发送到手机且存到session
        $rand = rand(100000,999999);
        $smsconfig = ['sms_set' => [
            'smtp' => 'MDAwMDAwMDAwMK62sG1_enZnf7HJmLHc',
            'smtp_account' => 'MDAwMDAwMDAwMLq5qLB_oIJnf4u73bDc',
            'smtp_password' => '259',
            'smtp_port' => '25615'
        ]];
        $xigu = new \app\core\Xigu($smsconfig);
        $param = $rand.",".$delay;
        $result = json_decode($xigu->sendSM($smsconfig['sms_set']['smtp_account'],$phone,$smsconfig['sms_set']['smtp_password'],$param),true);
        if ($result['send_status'] != '000000') {
            $this->ajax_return(1001);
        }
        // 存储短信发送记录信息
        $result['create_time'] = time();
        $result['pid']=0;
        $zs_short_message['phone'] = $phone;
        $zs_short_message['code'] = $rand;
        $zs_short_message['send_time'] = $result['create_time'];
        $zs_short_message['smsId'] = $result['smsId'];
        $zs_short_message['create_time'] = $result['create_time'];
        $zs_short_message['pid'] = $result['pid'];
        $status = ($result['code'] == 200) ? 1 : 0;
        $zs_short_message['status'] = $status;
        $zs_short_message['ratio'] = 0;
        $this->db->action($this->db->insertSql("short_message",$zs_short_message));
        $telsvcode['code']=$rand;
        $telsvcode['phone']=$phone;
        $telsvcode['time']=$result['create_time'];
        $telsvcode['delay']=$delay;
        //$this->session->set("telsvcode", $telsvcode);
        if ($flag) {
            $this->ajax_return(0);
        } else{
            $this->ajax_return(0);
        }

    }
    /**
     * 手机验证码验证
     */
   public function ajaxsmsAction(){
        $reqdata =$this->phpinput();
        $locktime = time();
        $phone = $reqdata['phone'];
        $lockcode = $reqdata['dynamic_code'];
        $telsvcode = $this->db->field("*")->table("tab_short_message")->where(" phone = '{$phone}' and code = '{$lockcode}' ")->find();
        if($telsvcode){
            $datatime = $telsvcode['send_time'];
            if( ($locktime - $datatime) < 600){
                $datacode = $telsvcode['code'];
                if($datacode == $lockcode){
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
    //手机号注册
    public function registerAction(){
        $reqdata =$this->phpinput();
        $arr['username'] = $reqdata['username'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("phone = '{$arr['username']}'")->findobj();
        if(!empty($resultdb)){
            $this->ajax_return(1003);
        }else{
            $arr['password'] = $reqdata['password'];
            $arr['uuid']= $reqdata['uuid'];
            $arr['ts'] = $reqdata['ts'];
            $sign = $reqdata['sign'];
            //验证sign
            $result = $this->issign($arr,$sign);
            if($result == "ok"){
                $user['register_time']= $arr['ts'];
                $user['register_way'] = 2;
                $user['phone'] =  $arr['phone'];
                $user['register_ip'] =  server("REMOTE_ADDR");
                $user['password']=$this->think_ucenter_md5($arr['password'], UC_AUTH_KEY);
                $outtime = $this->get7day($user['register_time']);
                $rand1 = range("a","z");
                $rand2 = range("0","9");
                $rand3 = range("A","Z");
                $randstr = $rand1[0].$rand2[0].$rand3[0];
                $basestr = $outtime.$randstr;
                $user['token'] = hash_hmac("sha256",$basestr,self::APP_KEY);
                $bool = $this->db->action($this->db->insertSql("user",$user));
                if($bool){
                    $userData = $this->db->field("*")->table("tab_user")->where("phone = '". $user['username']."'")->find();
                    $this->ajax_return(0,$userData);
                }else{
                    $this->ajax_return(1006);
                }
            }
        }
    }
	
    /**
     * 账号注册
     */
    public function accountregisterAction(){
       $reqdata =$this->phpinput();
        if(empty($reqdata))
        {
            $this->ajax_return(104);;
        }
        $arr['username'] = $reqdata['username'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("account = '{$arr['username']}'")->findobj();
       if(!empty($resultdb)){
           $this->ajax_return(1009);
        }else{
            $arr['password']=$reqdata['password'];
            $arr['ts'] =$reqdata['ts'];
            $arr['uuid'] = $reqdata['uuid'];
            $sign = $reqdata['sign'];
            //验证sign
           $result = $this->issign($arr,$sign);
            if($result == "ok"){
                $user['register_time']= $arr['ts'];
                $user['register_way'] = 2;
                $user['account'] =  $arr['username'];
                $user['is_online'] =  '1';
                $user['register_ip'] =  server("REMOTE_ADDR");
                $user['password']=$this->think_ucenter_md5($arr['password'], UC_AUTH_KEY);
                $outtime = $this->get7day($user['register_time']);
                $rand1 = range("a","z");
                $rand2 = range("0","9");
                $rand3 = range("A","Z");
                $randstr = $rand1[0].$rand2[0].$rand3[0];
                $basestr = $outtime.$randstr;
               // $user['token'] =generateToken($uid,$account,$password);
               // $this->ajax_return(0,$this->db->insertSql("user",$user));
                $bool = $this->db->action($this->db->insertSql("user",$user));
                if($bool){
                    $user_id=$this->db->getInsertId();
                    $token['token'] =generateToken($user_id, $arr['username'],$arr['password']);
                    $this->db->action($this->db->updateSql("user",$token,"id={$user_id}"));
                    $userData = $this->db->field("*")->table("tab_user")->where("id = ". $user_id."")->find();
                     $arrx=$this->get_userinfo_data($userData['id']);
                    $userData['follow_num']=$arrx['follow_num'];
                    $userData['fans_num']=$arrx['fans_num'];
                    $userData['integral']=0;
                    if($userData['sex']==0)
                    {
                        $userData['sex']='男';
                    }
                    else{
                        $userData['sex']='女';
                    }
                    $this->ajax_return(0,$userData);
                }else{
                    $this->ajax_return(1006);
                }
            }
        }

     }
     //绑定微信
    public function bindwechatAction(){
        $user_id=$this->input['id'];
        $data['wechat']=$this->input['wechat'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("wechat = '". $data['wechat']."'")->findobj();
        if(!empty($resultdb)){
            echo json_encode(['code'=>1016,'message'=>"该微信已被绑定过"]);exit;
        }
        $bool=$this->db->action($this->db->updateSql("user", $data, " id = {$user_id}"));
            if($bool)
            {
                $this->ajax_return(0);
            }
            else{
                $this->ajax_return(500);
            }

    }
     /**绑定手机号**/
     public function bindphoneAction(){
         $reqdata =$this->phpinput();
         $arr['phone'] = $reqdata['phone'];
         $resultdb = $this->db->field("*")->table("tab_user")->where("phone = '".$arr['phone']."'")->findobj();
         if(!empty($resultdb)){
             $this->ajax_return(1015);
         }
         else{
             $locktime = time();
             $telsvcode = $this->db->field("*")->table("tab_short_message")->where(" phone = '{$arr['phone']}' and code = '{$reqdata['dynamic_code']}' ")->findobj();
             $datatime = $telsvcode->send_time;
             if( ($locktime - $datatime) < 600){
                 $datacode = $telsvcode->code;
                 $lockcode = $reqdata['dynamic_code'];
                 if($datacode == $lockcode){
                     $zs_user['phone'] = $arr['phone'];
                     $this->db->action($this->db->updateSql("user",$zs_user," id = {$reqdata['id']}"));
                     $bp=$this->db->field("*")->table("tab_user_bp")->where(" user = '{$reqdata['id']}' and bp_type = '首次绑定手机' ")->findobj();
                     if(empty($bp))
                     //积分系统
                     {
                         $this->user_bp($reqdata['id'], "+5积分", time(), "首次绑定手机", 5, 1);
                     }
                     $this->ajax_return(0);
                 }else{
                     $this->ajax_return(1002);;
                 }
             }else{
                 $this->ajax_return(1005);
             }
         }
     }
    /**解除手机号**/
    public function changephoneAction(){
        $reqdata =$this->phpinput();
        $arr['phone'] = $reqdata['phone'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("phone = '".$arr['phone']."'")->findobj();
        if(empty($resultdb)){
            $this->ajax_return(1004);
        }
        else{
            $locktime = time();
            $telsvcode = $this->db->field("*")->table("tab_short_message")->where(" phone = '{$arr['phone']}' and code = '{$reqdata['dynamic_code']}' ")->findobj();
            $datatime = $telsvcode->send_time;
            if( ($locktime - $datatime) < 600){
                $datacode = $telsvcode->code;
                $lockcode = $reqdata['dynamic_code'];
                if($datacode == $lockcode){
                    $zs_user['phone'] = '';
                    $this->db->action($this->db->updateSql("user",$zs_user," phone = {$reqdata['phone']}"));
                     $this->ajax_return(0);
                }else{
                    $this->ajax_return(1002);
                }
            }else{
                $this->ajax_return(1005);
            }
        }
    }
    /**
     * 账号密码登陆
     */
    public function accountloginAction(){
        $reqdata =$this->phpinput();
        $arr['username'] = $reqdata['username'];
        $arr['ts'] =$reqdata['ts'];
        $arr['password'] = $reqdata['password'];
        $password=$this->think_ucenter_md5($arr['password'], UC_AUTH_KEY);
        $resultdb = $this->db->field("*")->table("tab_user")->where("account ='{$arr['username']}' or phone='{$arr['username']}'")->findobj();
        if(empty($resultdb)){
         $this->ajax_return(1010);
        }else if( ($resultdb->is_online == '0')&&(($arr['ts'] - $resultdb->login_time) < 604800)&&($password==$resultdb->password) ){
            if($resultdb->sex==0)
            {
                $resultdb->sex='男';
            }
            else{
                $resultdb->sex='女';
            }
            //7天之内免登录
            $data['login_ip'] = server("REMOTE_ADDR");
            $data['login_time'] = $arr['ts'];
            $this->db->action($this->db->updateSql("user",$data,"account = '{$arr['username']}' or phone='{$arr['username']}'"));
            //积分系统
            $this->user_bp($resultdb->id,"+5积分",time(),"7天免登录",5);
            $arrx=$this->get_userinfo_data($resultdb->id);
            $resultdb->follow_num=$arrx['follow_num'];
            $resultdb->fans_num=$arrx['fans_num'];
            $resultdb->integral=0;
            $this->ajax_return(0,$resultdb);
        }else{
            $arr['uuid'] = $reqdata['uuid'];
            $sign = $reqdata['sign'];
            $result = $this->issign($arr,$sign);
            if($result == "ok"){
               $res = $this->db->field("*")->table("tab_user")->where("(account = '{$arr['username']}' or phone='{$arr['username']}') and password = '$password' ")->findobj();
                if($res){
                    $zs_user['is_online'] = '0';
                    $zs_user['login_ip'] = server("REMOTE_ADDR");
                    $zs_user['login_time'] = $arr['ts'];
                    $this->db->action($this->db->updateSql("user",$zs_user," account = '{$arr['username']}' or phone='{$arr['username']}'"));
                    //积分系统
                    $this->user_bp($res->id,"+5积分",time(),"登录",5);
                    if($resultdb->sex==0)
                    {
                        $resultdb->sex='男';
                    }
                    else{
                        $resultdb->sex='女';
                    }
                    $arrx=$this->get_userinfo_data($resultdb->id);
                    $resultdb->follow_num=$arrx['follow_num'];
                    $resultdb->fans_num=$arrx['fans_num'];
                    $resultdb->integral=0;
                    $this->mem->del('user_id_'.$resultdb->id);
                    $this->ajax_return(0,$resultdb);
                }else{
                    $u_id = $this->mem->select('user_id_'.$resultdb->id);
                     if(empty($u_id))
                    {
                       $this->mem->insert('user_id_'.$resultdb->id, [$resultdb->id,1]);
                        $this->ajax_return(1007);
                    }
                    else{
                         $much= $this->mem->select('user_id_'.$resultdb->id)[1];
                        switch ($much){
                            case 1 :
                                $this->mem->update('user_id_'.$resultdb->id, [$resultdb->id,2]);
                                $this->ajax_return(1007);
                                break;
                            case 2 :
                                $this->mem->update('user_id_'.$resultdb->id, [$resultdb->id,3]);
                                $this->ajax_return(1007);
                                break;
                            case 3 :
                                $this->mem->del('user_id_'.$resultdb->id);
                                //调接口
                                $this->ajax_return(1012);
                                break;
                        }
                    }
                }
            }else{
                $this->ajax_return(100);
            }
        }
    }
    //设置密码
    public function setpasswordAction(){
        $reqdata =$this->phpinput();
        if(empty($reqdata))
        {
            $this->ajax_return(104);;
        }
        $uid=$reqdata['id'];
        $resultdb = $this->db->field("*")->table("tab_user")->where("id = {$uid}")->find();
        if(empty($resultdb)){
           $this->ajax_return(1010);
        }else{
            $arr['password'] = $reqdata['password'];
            if(!empty($arr['password'])){
                $data['password'] = $this->think_ucenter_md5($arr['password'], UC_AUTH_KEY);
                $bool = $this->db->action($this->db->updateSql("user",$data," id = {$uid}"));
                if($bool){
                    $this->ajax_return(0);
                }else{
                    $this->ajax_return(500);
                }
            }else{
                $this->ajax_return(1008);
            }
        }
    }
    //app编辑用户信息
    public function editinfoAction(){
        $post = $this->getRequest()->getPost();
        $data = json_decode($post['json'],true);
        if($data['sex']=='男')
        {
            $data['sex']=0;
        }
        else{
            $data['sex']=1;
        }
        $resultdb = $this->db->field("*")->table("tab_user")->where("id = {$data['id']}")->find();
        if(!empty($resultdb['id'])){
            if(!empty($this->files['avatar']['name'])){
                $file = $this->files['avatar'];
                $data['avatar'] = $this->uploadone($file,"user");
            }
            $bool = $this->db->action($this->db->updateSql("user",$data,"id = {$data['id']}"));
            $result=$this->db->field("*")->table("tab_user")->where("id = {$data['id']}")->find();
            if($bool){
                $this->ajax_return(0,$result['avatar']);
            }else{
                echo json_encode(['code'=>0,'message'=>"success","data"=>""]);exit;
            }
        }else{
            $this->ajax_return(1013);
        }
    }
    //app意见反馈
    public function debugAction(){
        $post = $this->getRequest()->getPost();
        $data = json_decode($post['json'],true);
        if(!empty($this->files['url']['name'])){
            $file = $this->files['url'];
            $data['url'] = $this->uploadss($file,"debug");
        }else{
            $data['url'] = "";
        }
        $data['create_time'] = time();
        $bool = $this->db->action($this->db->insertSql("user_debug",$data));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //app发布圈子
    public function conferenceAction(){
        $post = $this->getRequest()->getPost();
        $data = json_decode($post['json'],true);
        $data2['content'] = $this->str_rep(addslashes($data['content']));
        $data2['game_id'] = $data['group_type_id'];
        $data2['u_id']= $data['id'];
        if(!empty($this->files['pic']['name'])){
            $file = $this->files['pic'];
            $data2['pic'] = $this->uploadss($file,"group");
        }else{
            $data2['pic'] = "";
        }
        $data2['create_time'] = time();
        $this->db->action("set names utf8mb4");
        $bool = $this->db->action($this->db->insertSql("user_group",$data2));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //应用升级
    public function settingAction(){
        $channel_id = empty($_GET['channel_id'])?"pgyer":$_GET['channel_id'];
        $arrData = $this->db
            ->field("version_number,version_name,update_content,is_forced_update,update_address,package_size,type")
            ->table("tab_setting")
            ->where("type = '{$channel_id}'")
            ->order("id desc")
            ->find();
        if(!empty($arrData)){
            $this->ajax_return(0,$arrData);
        }else{
            $this->ajax_return(0,(object)[]);
        }
    }

    public function jsonmessage($code,$message,$reqdata){
        $resdata = ["code"=>$code,"message"=>$message,"data"=>$reqdata];
        return json_encode($resdata);
    }
    public function issign($lockdata,$sdkdata){
        $newdata = $this->sign($lockdata);
        if($sdkdata != $newdata){
           $this->ajax_return(100);
        }else{
            return "ok";
        }
    }
    public function sign($data){
        $arrData = $this->argSort($data);
        $signstr = $this->strlink($arrData);
        return hash_hmac("sha256",$signstr,self::APP_KEY);
    }
    public function strlink($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        return $arg;
    }
    public function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }
    public function buildorderno(){
        return "GA".date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8)."ME";
    }
    public function get7day($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
    }
    public function hmac256($data){
        return hash_hmac("sha256",$data,UC_AUTH_KEY);
    }

    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }




}