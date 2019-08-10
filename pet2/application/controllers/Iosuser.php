<?php
class IosuserController extends IosbaseController{
    //注册接口
    public function register($mobile_num){
        $user['uid'] =  "C".substr(time(),1);
        $user['account'] =  $mobile_num;
        $user['password'] =  $this->hmac256("123456");
        $user['mobile_num'] =  $mobile_num;
        $user['sex'] =  "保密";
        $user['nick_name'] =  "";
        $user['real_name'] =  "";
        $user['id_card'] =  "";
        $user['user_level'] = 0;
        $user['user_exp'] =  0;
        $user['lock_status'] =  '0';
        $user['register_time'] =  time();
        $user['register_ip'] =  server("REMOTE_ADDR");
        $user['last_activity_date'] =  time();
        $user['is_online'] =  '1';
        $user['avatar'] = '';
        $user['pet_cur'] = 0;
        $user['user_age'] = 0;
        $user['pet_age'] = 0;
        $outtime = $this->get7day(time());
        $rand1 = range("a","z");
        $rand2 = range("0","9");
        $rand3 = range("A","Z");
        $randstr = $rand1[0].$rand2[0].$rand3[0];
        $basestr = $user['uid'].$outtime.$randstr;
        $user['access_token'] = $this->hmac256($basestr);
        $user['competence'] = '宠爱用户';
        $bool = $this->db->action($this->db->insertSql("ios_user",$user));
        return $bool;
    }
    //安卓数据接口
    public function android($mobile_num){
        $user['uid'] =  "C".substr(time(),1);
        $user['account'] =  $mobile_num;
        $user['password'] =  $this->hmac256("123456");
        $user['mobile_num'] =  $mobile_num;
        $user['sex'] =  "保密";
        $user['nick_name'] =  "";
        $user['location_province'] =  "";
        $user['location_city'] =  "";
        $user['location_area'] =  "";
        $user['real_name'] =  "";
        $user['id_card'] =  "";
        $user['user_level'] = 0;
        $user['user_exp'] =  0;
        $user['lock_status'] =  '0';
        $user['register_time'] =  time();
        $user['register_ip'] =  server("REMOTE_ADDR");
        $user['last_activity_date'] =  time();
        $user['is_online'] =  '1';
        $user['avatar'] = '';
        $outtime = $this->get7day(time());
        $rand1 = range("a","z");
        $rand2 = range("0","9");
        $rand3 = range("A","Z");
        $randstr = $rand1[0].$rand2[0].$rand3[0];
        $basestr = $user['uid'].$outtime.$randstr;
        $user['access_token'] = $this->hmac256($basestr);
        $user['competence'] = '普通用户';
        $this->db->action($this->db->insertSql("user",$user));
    }
    //手机号登录（自带注册）
    public function phoneloginAction(){
        $reqdata = $_POST;
        $arr['username'] = $reqdata['phone'];
        $exp = '/\d{11}/';
        if(!preg_match($exp,$arr['username'])){
            $this->ajax_return(105);
        }
        $resultdb = $this->db->field("*")->table("zs_ios_user")->where("mobile_num = {$arr['username']}")->findobj();
        if(empty($resultdb)){
            //注册
            $bool = $this->register($arr['username']);
            if($bool){
                $resultdb = $this->db->field("*")->table("zs_ios_user")->where("mobile_num = {$arr['username']}")->findobj();
                $arrx = get_userinfo_data($resultdb->id);
                $resultdb->follow_num = $arrx['follow_num'];
                //$resultdb->collection_num = $arrx['collection_num'];
                $resultdb->fans_num=0;
                $resultdb->commonweal_price = $arrx['commonweal_price'];
                $this->android($arr['username']); //同步安卓数据
                $this->ajax_return(0,$resultdb);
            }else{
                $this->ajax_return(500);
            }
        }else{
            unset($arr['username']);
            $arr['phone'] = $_POST['phone'];
            $arr['dynamic_code'] = $_POST['dynamic_code'];
            $arr['uuid'] = $_POST['uuid'];
            $arr['ts'] = $_POST['ts'];
            $sign = $_POST['sign'];
            $result = $this->issign($arr,$sign);
            if($result){
                $locktime = time();
                $telsvcode = $this->db->field("*")->table("zs_short_message")->where(" phone = '{$arr['phone']}' and code = '{$arr['dynamic_code']}' ")->findobj();
                $datatime = $telsvcode->send_time;
                if( ($locktime - $datatime) < 600){
                    $datacode = $telsvcode->code;
                    $lockcode = $reqdata['dynamic_code'];
                    if($datacode == $lockcode){
                        $zs_user['is_online'] = '0';
                        $zs_user['register_ip'] = server("REMOTE_ADDR");
                        $zs_user['last_activity_date'] = $arr['ts'];
                        $this->db->action($this->db->updateSql("ios_user",$zs_user," mobile_num = {$arr['phone']}"));
                        //积分系统
                        user_ios_bp($resultdb->id,"+5积分",time(),"7天免登录",5);
                        $arrx = get_userinfo_data($resultdb->id);
                        $resultdb->follow_num = $arrx['follow_num'];
                        $resultdb->collection_num = $arrx['collection_num'];
                        $resultdb->commonweal_price = $arrx['commonweal_price'];
                        $this->ajax_return(0,$resultdb);
                    }else{
                        $this->ajax_return(1002);
                    }
                }else{
                    $this->ajax_return(1005);
                }
            }else{
                $this->ajax_return(100);
            }
        }
    }
    //用户密码登录
    public function userloginAction(){
        $reqdata = $_POST;
        $arr['phone'] = $reqdata['phone'];
        $arr['ts'] = $reqdata['ts'];
        $resultdb = $this->db->field("*")->table("zs_ios_user")->where("mobile_num = {$arr['phone']}")->findobj();
        if(empty($resultdb)){
            $this->ajax_return(1004);
        }else if( ($resultdb->is_online == '0') && (($arr['ts'] - $resultdb->last_activity_date) < 604800 ) ){
            //7天之内免登录
            $data['register_ip'] = server("REMOTE_ADDR");
            $data['last_activity_date'] = $arr['ts'];
            $this->db->action($this->db->updateSql("ios_user",$data,"mobile_num = {$arr['phone']}"));
            //积分系统
            user_ios_bp($resultdb->id,"+5积分",time(),"7天免登录",5);
            $arrx = get_userinfo_data($resultdb->id);
            $resultdb->follow_num = $arrx['follow_num'];
            $resultdb->collection_num = $arrx['collection_num'];
            $resultdb->commonweal_price = $arrx['commonweal_price'];
            $this->ajax_return(0,$resultdb);
        }else{
            $arr['password'] = $reqdata['password'];
            $arr['uuid'] = $reqdata['uuid'];
            $sign = $reqdata['sign'];
            $result = $this->issign($arr,$sign);
            $password=$this->hmac256("123456");
            if($result == "ok"){
                $res = $this->db->field("*")->table("zs_ios_user")->where(" mobile_num = {$arr['phone']} and password = '{$password}' ")->find();
                if($res){
                    $zs_user['is_online'] = '0';
                    $zs_user['register_ip'] = server("REMOTE_ADDR");
                    $zs_user['last_activity_date'] = $arr['ts'];
                    $this->db->action($this->db->updateSql("ios_user",$zs_user," mobile_num = {$arr['phone']}"));
                    //积分系统
                    user_ios_bp($res['id'],"+5积分",time(),"登录",5);
                    $arrx = get_userinfo_data($res['id']);
                    $res['follow_num'] = $arrx['follow_num'];
                    $res['collection_num'] = $arrx['collection_num'];
                    $res['commonweal_price'] = $arrx['commonweal_price'];
                    $this->ajax_return(0,$res);
                }else{
                    $this->ajax_return(1007);
                }
            }else{
                $this->ajax_return(100);
            }
        }
    }
    //加密规则
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
    //第三方登录
    public function buildloginAction(){
        $reqdata = $_POST;
        $sdkid = $reqdata['sdkid'];
        $type = $reqdata['type'];
        $ts = $reqdata['ts'];
        $resultdb = $this->db->field("*")->table("zs_ios_user")->where("{$type} = '{$sdkid}'")->find();
        if(!empty($resultdb)){
            $zs_user['is_online'] = '0';
            $zs_user['register_ip'] = server("REMOTE_ADDR");
            $zs_user['last_activity_date'] = $ts;
            $zs_user[$type] = $sdkid;
            $this->db->action($this->db->updateSql("ios_user",$zs_user,"{$type} = '{$sdkid}'"));
            //积分系统
            user_ios_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
            $arrx = get_userinfo_data($resultdb['id']);
            $resultdb['follow_num'] = $arrx['follow_num'];
            $resultdb['collection_num'] = $arrx['collection_num'];
            $resultdb['commonweal_price'] = $arrx['commonweal_price'];
            $this->ajax_return(0,$resultdb);
        }else{
            $this->ajax_return(0);
        }
    }
    //SDK手机号验证（自带注册）
    public function sdkloginphoneAction(){
        $reqdata = $_POST;
        $sdkid = $reqdata['sdkid'];
        $type = $reqdata['type'];
        $mobile_num = $reqdata['phone'];
        $ts = $reqdata['ts'];
        $dynamic_code = $reqdata['dynamic_code'];
        //验证码判断
        $locktime = time();
        $telsvcode = $this->db->field("*")->table("zs_short_message")->where(" phone = '{$mobile_num}' and code = '{$dynamic_code}' ")->findobj();
        $datatime = $telsvcode->send_time;
        if( ($locktime - $datatime) < 600){
            $datacode = $telsvcode->code;
            $lockcode = $reqdata['dynamic_code'];
            if($datacode == $lockcode){
                $resultdb = $this->db->field("*")->table("zs_ios_user")->where(" mobile_num = {$mobile_num}")->find();
                if(!empty($resultdb)){
                    //登录
                    $zs_user['is_online'] = '0';
                    $zs_user['register_ip'] = server("REMOTE_ADDR");
                    $zs_user['last_activity_date'] = $ts;
                    $zs_user[$type] = $sdkid;
                    $bool = $this->db->action($this->db->updateSql("ios_user",$zs_user," mobile_num = {$mobile_num}"));
                    //积分系统
                    user_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
                }else{
                    //注册
                    $bool = $this->register($mobile_num);
                }
                if($bool){
                    $resultdb = $this->db->field("*")->table("zs_user")->where(" mobile_num = {$mobile_num}")->find();
                    user_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
                    $arrx = get_userinfo_data($resultdb['id']);
                    $resultdb['follow_num'] = $arrx['follow_num'];
                    $resultdb['collection_num'] = $arrx['collection_num'];
                    $resultdb['commonweal_price'] = $arrx['commonweal_price'];
                    $this->android($mobile_num); //同步安卓数据
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
    //用户退出
    public function logoutAction(){
        $uid = $_POST['id'];
        $resultdb = $this->db->field("*")->table("zs_ios_user")->where("uid = '{$uid}'")->find();
        if(empty($resultdb)){
            $this->ajax_return(1000);
        }else{
            $data['is_online'] = '1';
            $this->db->action($this->db->updateSql("ios_user",$data," uid = '{$uid}'"));
            $this->ajax_return(0);
        }
    }
    //找回密码
    public function forgetpasswordAction(){
        $reqdata = $_POST;
        $arr['username'] = $reqdata['phone'];
        $resultdb = $this->db->field("*")->table("zs_ios_user")->where("mobile_num = {$arr['username']}")->find();
        if(empty($resultdb)){
            $this->ajax_return(1004);
        }else{
            $arr['dynamic_code'] = $reqdata['dynamic_code'];
            $phonecode = $this->db->field("*")->table("zs_short_message")->where(" phone = '{$arr['username']}' and code = '{$arr['dynamic_code']}' ")->find();
            if(!empty($phonecode)){
                $arr['password'] = $reqdata['password'];
                if(!empty($arr['password'])){
                    $data['password'] = $arr['password'];
                    $bool = $this->db->action($this->db->updateSql("ios_user",$data," mobile_num = {$arr['username']}"));
                    $this->db->action($this->db->updateSql("user",$data," mobile_num = {$arr['username']}"));//同步安卓数据
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
    //修改密码
    public function repasswordAction(){

    }
    //更换手机号
    public function editphoneAction(){

    }
    //发送验证码判断
    public function sendvcodeAction() {
        $phone = isset($_POST['phone'])?$_POST['phone']:"";
        $name = $phone;
        if (empty($phone) || empty($name)) {
            $this->ajax_return(100);
        }
        $this->telsvcodeAction($phone);
    }
    //发送手机安全码
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
            'smtp_password' => '273',
            'smtp_port' => '25615'
        ]];
        $xigu = new \app\core\Xigu($smsconfig);
        $param = $rand.",".$delay;
        $result = json_decode($xigu->sendSM($smsconfig['sms_set']['smtp_account'],$phone,$smsconfig['sms_set']['smtp_password'],$param),true);
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

        if ($result['send_status'] != '000000') {
            $this->ajax_return(1001);
        }
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
    //手机验证码验证
    public function ajaxsmsAction(){
        $locktime = time();
        $phone = $_POST['phone'];
        $lockcode = $_POST['dynamic_code'];
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
    //社区推荐话题
    public function cardtypeAction(){
        $data = $this->db->action("SELECT id as t_id,title,pic,info FROM zs_card_type");
        foreach ($data as $k=>$v){
            $data[$k]['pic'] = "/public".$v['pic'];
        }
        $this->ajax_return(0,$data);
    }
    //社区发布内容
    public function addcardAction(){
        $savedata['u_id'] = $_POST['u_id'];
        $savedata['title'] = $_POST['title'];
        $res = $this->db->field("*")->table("zs_card_type")->where("title = '{$_POST['title']}'")->find();
        if(!empty($res)){
            $savedata['t_id'] = $res['id'];
        }else{
            $datas['title'] = $_POST['title'];
            $boolstatus = $this->db->action($this->db->insertSql("card_type",$datas));
            if(!$boolstatus){
                $this->ajax_return(500);
            }
            $tdata= $this->db->field("*")->table("zs_card_type")->where("title = '{$_POST['title']}'")->find();
            $savedata['t_id'] = $tdata['id'];
        }
        $savedata['content'] = str_rep(addslashes($_POST['content']));
        $savedata['click'] = 0;
        $savedata['create_time'] = time();
        $savedata['lbs'] = $_POST['lbs'];
        if (!empty($_FILES['image']['name'])) {
            $time = time();
            $fileicon = files("image");
            $dir = APP_PATH."/public/card/".$time;
            if(!file_exists($dir)){
                mkdir($dir,0777,true);
            }
            $fileArr = [];
            for($i=0;$i<count($fileicon['name']);$i++){
                $pathicon = $dir."/".$fileicon['name'][$i];
                move_uploaded_file( $fileicon['tmp_name'][$i],$pathicon);
                $fileArr[] = $time."/".$fileicon['name'][$i];
            }
            $savedata['card_pic'] = json_encode($fileArr,320);
        }else{
            $savedata['card_pic'] = json_encode([]);
        }
        $this->db->action("set names utf8mb4");
        unset($savedata['title']);
        $bool = $this->db->action($this->db->insertSql("card",$savedata));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //用户基本信息修改SDK接口
    public function usereditAction(){
        if($this->getRequest()->isPost()){
            $reqdata = $_POST;
            $id = $reqdata['id'];
            $resultdb = $this->db->field("*")->table("zs_ios_user")->where("id = {$id}")->find();
            if(!empty($resultdb)){
                $data['nick_name'] = $reqdata['nick_name'];
                $data['sex'] = $reqdata['sex'];
                $data['user_age'] = $reqdata['user_age'];
                $data['u_sign'] = $reqdata['u_sign'];
                $data['pet_age'] = $reqdata['pet_age'];
                $data['pet_breed'] = $reqdata['pet_breed'];
                $data['pet_sex'] = $reqdata['pet_sex'];
                $data['pet_sterilization'] = $reqdata['pet_sterilization'];
                if (!empty($_FILES['image']['name'])) {
                    $time = time();
                    $fileicon = files("image");
                    $dir = APP_PATH.'/public/user/'.$time;
                    if(!file_exists($dir)){
                        mkdir($dir,0777,true);
                    }
                    $pathicon = $dir.'/'.$fileicon['name'];
                    move_uploaded_file($fileicon['tmp_name'],$pathicon);
                    $fileArr = $time."/".$fileicon['name'];
                    $data['avatar'] = $fileArr;
                    $user['avatar'] = $fileArr;
                    $this->db->action($this->db->updateSql("user",['avatar'=>$fileArr],"id = {$id}"));
                }
                $user['nick_name'] = $reqdata['nick_name'];
                $user['sex'] = $reqdata['sex'];
                $user['location_province'] = $reqdata['location_province'];
                $user['location_city'] = $reqdata['location_city'];
                $user['u_sign'] = $reqdata['u_sign'];
                $user['pet_breed'] = $reqdata['pet_breed'];
                $point['lbs'] = $reqdata['location_province'].$reqdata['location_city'];
                $point['u_id']=$id;
                $bool1 = $this->db->action($this->db->updateSql("ios_user",$data,"id = {$id}"));
                $result=$this->db->field("*")->table("zs_user_point")->where("u_id={$id}")->find();
                if($result) {
                    $bool2 = $this->db->action($this->db->updateSql("user_point", $point, "u_id = {$id}"));
                }
                else{
                    $bool2 = $this->db->action($this->db->insertSql("user_point",$point));
                }
                $bool3 = $this->db->action($this->db->updateSql("user",$user,"id = {$id}"));
                $resultdb2 = $this->db->field("*")->table("zs_ios_user")->where("id = {$id}")->find();
                $this->ajax_return(0,$resultdb2['avatar']);
            }else{
                $this->ajax_return(1004);
            }
        }else{
            $this->ajax_return(104);
        }

    }
    //社区展示(推荐/最新)
    public function cardlistAction(){
        $user_id = $_POST['id'];
        $type = $_POST['type'];
        $currentPage = empty($_POST["page"])?"1":$_POST["page"];
        $showPage = empty($_POST["showpage"])?"9":$_POST["showpage"];
        $start =  ($currentPage-1)*$showPage;
        if($type == 'click'){
            $ordersql = " ORDER BY c.click DESC ";
        }else if($type == 'news'){
            $ordersql = " ORDER BY c.create_time DESC ";
        }else{
            $ordersql = " ORDER BY c.click DESC ";
        }
        $intdata = $this->screencard($user_id);
        if($intdata){
            $sql = "
         SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,u.competence,t.title,c.id as c_id,c.content,c.create_time,c.lbs,c.card_pic,c.click
         FROM zs_card as c
         INNER JOIN zs_ios_user as u ON u.id = c.u_id
         INNER JOIN zs_card_type as t ON t.id = c.t_id
         WHERE c.id NOT IN ($intdata)
         {$ordersql} LIMIT {$start},{$showPage}";
        }else{
            $sql = "
         SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,u.competence,t.title,c.id as c_id,c.content,c.create_time,c.lbs,c.card_pic,c.click
         FROM zs_card as c
         INNER JOIN zs_ios_user as u ON u.id = c.u_id
         INNER JOIN zs_card_type as t ON t.id = c.t_id
         {$ordersql} LIMIT {$start},{$showPage}";
        }
        $result = $this->db->action($sql);
        foreach ($result as $k=>$v){
                $result[$k]['c_id'] = $v['c_id'];
                $arr_card = json_decode($v['card_pic'],true);
                $arr = [];
                for($i=0;$i<count($arr_card);$i++){
                    $arr[$i] = "/public/card/".$arr_card[$i];
                }
                $result[$k]['card_pic'] = json_encode($arr,320);
                $result[$k]['feedback_num'] = $this->db->zscount("card_feedback","*","total","c_id = {$v['c_id']}");
                $result[$k]['share_num'] = 0;
                $result[$k]['is_follow'] = !empty($this->db->field("*")->table("zs_attention")->where(" uid = {$user_id} and f_id = {$v['id']} ")->select()) ? 1 : 0;
                $result[$k]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where(" u_id = {$user_id} and c_id = {$v['c_id']} ")->select()) ? 1 : 0;
                if(strlen($result[$k]['card_pic']) == 2){
                    $result[$k]['type'] = 3;
                }elseif(strstr($result[$k]['card_pic'], '.mp4') != false){
                    $result[$k]['type'] = 2;
                }else{
                    $result[$k]['type'] = 1;
                }
        }
        $this->ajax_return(0,$result);
    }
    //社区内容详细
    public function carddetailAction(){
        $user_id = $_POST['id'];
        $c_id = $_POST['c_id'];
        $sql = "
         SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,u.competence,t.title,c.id as c_id,c.content,c.create_time,c.lbs,c.card_pic,c.click
         FROM zs_card as c
         INNER JOIN zs_ios_user as u ON u.id = c.u_id
         INNER JOIN zs_card_type as t ON t.id = c.t_id
         WHERE c.id = {$c_id}
         ";
        $result = $this->db->action($sql);
        $arr_card = json_decode($result[0]['card_pic'],true);
        $arr = [];
        for($i=0;$i<count($arr_card);$i++){
            $arr[$i] = "/public/card/".$arr_card[$i];
        }
        $result[0]['card_pic'] = json_encode($arr,320);
        $result[0]['feedback_num'] = $this->db->zscount("card_feedback","*","total","c_id = {$result[0]['c_id']}");
        $result[0]['share_num'] = 0;
        $result[0]['is_follow'] = !empty($this->db->field("*")->table("zs_attention")->where(" uid = {$user_id} and f_id = {$result[0]['id']} ")->select()) ? 1 : 0;
        $result[0]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where(" u_id = {$user_id} and c_id = {$result[0]['c_id']} ")->select()) ? 1 : 0;
        if(strlen($result[0]['card_pic']) == 2){
            $result[0]['type'] = 3;
        }elseif(strstr($result[0]['card_pic'], '.mp4') != false){
            $result[0]['type'] = 2;
        }else{
            $result[0]['type'] = 1;
        }
        $this->ajax_return(0,$result[0]);
    }
    //社区展示宠友圈
    public function acrdpetcircleAction(){
        $user_id = $_POST['id'];
        $currentPage = empty($_POST["page"])?"1":$_POST["page"];
        $showPage = empty($_POST["showpage"])?"9":$_POST["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $res = $this->db->action("SELECT f_id FROM zs_attention WHERE uid ={$user_id}");
        if(!empty($res)){
            $indata = [];
            foreach ($res as $key=>$value){
                $indata[] = $value['f_id'];
            }
            $str = implode(",",$indata);
            $sql = "
         SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,u.competence,t.title,c.id as c_id,c.content,c.create_time,c.lbs,c.card_pic,c.click
         FROM zs_card as c
         INNER JOIN zs_ios_user as u ON u.id = c.u_id
         INNER JOIN zs_card_type as t ON t.id = c.t_id
         WHERE u.id IN ({$str})
         LIMIT {$start},{$showPage}";
            $result = $this->db->action($sql);
            if(!empty($result)){
                foreach ($result as $k=>$v){
                    $arr_card = json_decode($v['card_pic'],true);
                    $arr = [];
                    for($i=0;$i<count($arr_card);$i++){
                        $arr[$i] = "/public/card/".$arr_card[$i];
                    }
                    $result[$k]['card_pic'] = json_encode($arr,320);
                    $result[$k]['feedback_num'] = $this->db->zscount("card_feedback","*","total","c_id = {$v['c_id']}");
                    $result[$k]['share_num'] = 0;
                    $result[$k]['is_follow'] = !empty($this->db->field("*")->table("zs_attention")->where(" uid = {$user_id} and f_id = {$v['id']} ")->select()) ? 1 : 0;
                    $result[$k]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where(" u_id = {$user_id} and c_id = {$v['c_id']} ")->select()) ? 1 : 0;
                    if(strlen($result[$k]['card_pic']) == 2){
                        $result[$k]['type'] = 3;
                    }elseif(strstr($result[$k]['card_pic'], '.mp4') != false){
                        $result[$k]['type'] = 2;
                    }else{
                        $result[$k]['type'] = 1;
                    }
                }
                $this->ajax_return(0,$result);
            }else{
                $this->ajax_return(0,[]);
            }
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //社区搜索（用户）
    public function cardsearchAction(){
        $account = $_POST['userdata'];
        $fieldstr = "u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,p.longitude,p.latitude,p.lbs";
        $currentPage = empty($_POST["page"])?"1":$_POST["page"];
        $showPage = empty($_POST["showpage"])?"9":$_POST["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $where = "";
        $exp = '/^\d/';
        if(strtolower(substr($account,0,1)) == "c"){
            $where = "uid";
        }else {
            if(preg_match($exp,$account,$arr)){
                $where = "mobile_num";
            }else{
                $where = "nick_name";
            }
        }
        $sql = "SELECT {$fieldstr} 
        FROM zs_ios_user as u 
        LEFT JOIN zs_user_point as p ON u.id = p.u_id 
        WHERE u.{$where} LIKE '%{$account}%'
        LIMIT {$start},{$showPage}";
        $result = $this->db->action($sql);
        $this->ajax_return(0,$result);
    }
    //社区搜索（内容）
    public function cardsearchcontentAction(){
        $content = $_POST['content'];
        $fieldstr = "u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,u.competence,t.title,c.lbs,c.content,c.card_pic,c.create_time,c.click,c.id as c_id";
        $currentPage = empty($_POST["page"])?"1":$_POST["page"];
        $showPage = empty($_POST["showpage"])?"9":$_POST["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql = "SELECT {$fieldstr} 
        FROM zs_card as c 
        LEFT JOIN zs_ios_user as u ON u.id = c.u_id 
        LEFT JOIN zs_card_type as t ON t.id = c.t_id 
        WHERE c.content LIKE '%{$content}%'
        LIMIT {$start},{$showPage}";
        $result = $this->db->action($sql);
        foreach ($result as $k=>$v) {
            $arr_card = json_decode($v['card_pic'], true);
            $arr = [];
            for ($i = 0; $i < count($arr_card); $i++) {
                $arr[$i] = "/public/card/" . $arr_card[$i];
            }
            $result[$k]['card_pic'] = json_encode($arr, 320);
            $result[$k]['feedback_num'] = $this->db->zscount("card_feedback", "*", "total", "c_id = {$v['c_id']}");
            $result[$k]['share_num'] = 0;
            if(strlen($result[$k]['card_pic']) == 2){
                $result[$k]['type'] = 3;
            }elseif(strstr($result[$k]['card_pic'], '.mp4') != false){
                $result[$k]['type'] = 2;
            }else{
                $result[$k]['type'] = 1;
            }
        }
        $this->ajax_return(0,$result);
    }
    //社区内容详情评论展示
    public function cardfeedbackAction(){
        $m_id = $_POST['c_id'];
        $currentPage = empty($_POST["page"])?"1":$_POST["page"];
        $showPage = empty($_POST["showpage"])?"9":$_POST["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql  = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,f.id as feedback_id,f.body,f.feedtime
FROM zs_card_feedback as f 
INNER JOIN zs_ios_user as u ON u.id = f.u_id 
WHERE f.c_id = {$m_id} 
ORDER BY f.id DESC 
LIMIT {$start},{$showPage}";
        $this->db->action("set names utf8mb4");
        $result = $this->db->action($sql);
        foreach ($result as $k=>$v){
            $result[$k]['reply']['replysum'] = $this->replysum($v['feedback_id']);
            $result[$k]['reply']['replydata'] = $this->replydata($v['feedback_id']);
        }
        $this->ajax_return(0,$result);
    }
    //社区内容详情添加评论
    public function cardaddfeedbackAction(){
        $cardData = $this->db->field("*")->table("zs_card")->where(" id = {$_POST['c_id']}")->find();
        $user = $this->db->field("nick_name,avatar")->table("zs_ios_user")->where("id={$_POST['id']}")->find();
        $data['u_id'] = $_POST['id'];
        $data['c_id'] = $_POST['c_id'];
        $data['body'] = addslashes($_POST['body']);
        $data['feedtime'] = time();
        $data['card_user_id']=$cardData['u_id'];
        $this->db->action("set names utf8mb4");
        $bool = $this->db->action($this->db->insertSql("card_feedback",$data));
        if($bool){
            if($_POST['id']!=$cardData['u_id'])
            {
                if (!empty($user['avatar'])) {
                    $user['avatar'] = "http://www.pettap.cn/public/user/" . $user['avatar'];
                } else {
                    $user['avatar'] = "";
                }
                $arr=["type"=>"1","uid"=>$cardData['u_id'],"pid"=>$_POST['id'],"pnickname"=>$user['nick_name'],"pavatar"=>$user['avatar'],"data"=>$cardData['content']];
                //$this->ajax_return(0,$arr);
                $this->forward("index","message","send",["arr"=>$arr]);
            }
            else{
                $this->ajax_return(0);
            }
        }else{
            $this->ajax_return(500);
        }
    }
    //社区内容详情添加回复
    public function cardaddreplyAction(){
        if($_POST['floor_id']==0) {
            $result = $this->db->field("*")->table("zs_card_feedback")->where(" id = {$_POST['feedback_id']}")->find();
            $result['user_id']=$result['u_id'];
            $content=$result['body'];
        }
        else{
            $result = $this->db->field("*")->table("zs_card_reply")->where(" id = {$_POST['floor_id']}")->find();
            $content=$result['content'];
        }
        $user = $this->db->field("nick_name,avatar")->table("zs_ios_user")->where("id={$_POST['id']}")->find();
        $data['user_id'] = $_POST['id'];
        $data['feedback_id'] = $_POST['feedback_id'];
        $data['content'] = addslashes($_POST['content']);
        $data['floor_id'] = $_POST['floor_id'];
        $data['feedback_user_id'] =  $result['user_id'];
        $data['create_time'] = time();
        $this->db->action("set names utf8mb4");
        $bool = $this->db->action($this->db->insertSql("card_reply",$data));
        if($bool){
            if($_POST['id']!= $result['user_id'])
            {
                if (!empty($user['avatar'])) {
                    $user['avatar'] = "http://www.pettap.cn/public/user/" . $user['avatar'];
                } else {
                    $user['avatar'] = "";
                }
                $arr=["type"=>"2","uid"=>$result['user_id'],"pid"=>$_POST['id'],"pnickname"=>$user['nick_name'],"pavatar"=>$user['avatar'],"data"=>$content];
                //$this->ajax_return(0,$arr);
                $this->forward("index","message","send",["arr"=>$arr]);
            }
            else{
                $this->ajax_return(0);
            }
        }else{
            $this->ajax_return(500);
        }
    }
    //社区内容回复详情展示
    public function cardreplyAction(){
        $feedback_id = $_POST['feedback_id'];
        $currentPage = empty($_POST["page"])?"1":$_POST["page"];
        $showPage = empty($_POST["showpage"])?"9":$_POST["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $fsql  = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,f.id as feedback_id,f.body,f.feedtime
FROM zs_card_feedback as f 
INNER JOIN zs_ios_user as u ON u.id = f.u_id 
WHERE f.id = {$feedback_id}";
        $feedback = $this->db->action($fsql);
        $result['feedback'] = $feedback[0];
        $sql = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,r.id as r_id,r.content,r.create_time,r.floor_id 
FROM zs_card_reply as r 
INNER JOIN zs_ios_user as u ON u.id = r.user_id 
WHERE r.feedback_id = {$feedback_id} 
ORDER BY r.id DESC 
LIMIT {$start},{$showPage} ";
        $reply = $this->db->action($sql);
        $result['reply'] = $reply;
        $this->ajax_return(0,$result);
    }
    //社区内容点赞
    public function clickAction(){
        $reqdata = $_POST;
        if(!empty($reqdata)){
            $u_id = $reqdata['id'];
            $c_id = $reqdata['c_id'];
            $cardData = $this->db->field("*")->table("zs_card")->where(" id = {$c_id}")->findobj();
            $user = $this->db->field("nick_name,avatar")->table("zs_ios_user")->where("id={$u_id}")->find();
            if(!empty($cardData)){
                $arrData = $this->db->action("select * from zs_card_click where u_id={$u_id} and c_id={$c_id}");
                if(!empty($arrData))
                {
                //取消点赞
                    $cldata['click'] = (int)$cardData->click - 1;
                    //$this->ajax_return($this->db->updateSql("card",$cldata,"id = {$c_id}"));
                    $bool = $this->db->action($this->db->updateSql("card",$cldata,"id = {$c_id}"));
                    if($bool){
                        $this->db->action($this->db->deleteSql("card_click"," u_id = {$u_id} and c_id = {$c_id} "));
                        $this->ajax_return(0);
                    }else{
                        $this->ajax_return(500);
                    }
                }
                else{
                    $cldata['click'] = (int)$cardData->click + 1;
                    $bool = $this->db->action($this->db->updateSql("card",$cldata,"id = {$c_id}"));
                    if($bool){
                        $data2['c_id'] = $c_id;
                        $data2['u_id'] = $u_id;
                        $data2['create_time'] = time();
                        $data2['card_user_id'] = $cardData->u_id;
                        $data2['status'] = 1;
                        $this->db->action($this->db->insertSql("card_click",$data2));
                        if($u_id!=$cardData->u_id)
                        {
                            if (!empty($user['avatar'])) {
                                $user['avatar'] = "http://www.pettap.cn/public/user/" . $user['avatar'];
                            } else {
                                $user['avatar'] = "";
                            }
                            $arr=["type"=>"0","uid"=>$cardData->u_id,"pid"=>$u_id,"pnickname"=>$user['nick_name'],"pavatar"=>$user['avatar'],"data"=>$cardData->content];
                            //$this->ajax_return(0,$arr);
                            $this->forward("index","message","send",["arr"=>$arr]);
                        }
                        else{
                            $this->ajax_return(0);
                        }

                    }else{
                        $this->ajax_return(500);
                    }
                }

            }else{
                $this->ajax_return(1100);
            }
        }else{
            $this->ajax_return(104);
        }
    }

    public function replydata($data){
        $sql = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,r.id as r_id,r.content,r.create_time,r.floor_id 
FROM zs_card_reply as r 
INNER JOIN zs_ios_user as u ON u.id = r.user_id 
WHERE r.feedback_id = {$data} 
ORDER BY r.id DESC 
LIMIT 0,3 ";
        $data = $this->db->action($sql);
        return $data;
    }
    public function replysum($data){
        $sql = "
SELECT count(*) as total 
FROM zs_card_reply as r 
INNER JOIN zs_ios_user as u ON u.id = r.user_id 
WHERE r.feedback_id = {$data} 
ORDER BY r.id DESC 
LIMIT 0,3 ";
        $data = $this->db->action($sql);
        return $data[0]['total'];
    }

    //点赞通知
    public function clicklistAction(){
        $user_id=$_POST['id'];
        $currentPage = empty($_POST["page"]) ? "1" : $_POST["page"];
        $showPage = empty($_POST["showpage"]) ? "10" : $_POST["showpage"];
        $start = ($currentPage - 1) * $showPage;
        //帖子点赞
        $card=$this->db->action("select a.u_id as id,a.create_time,c.id as c_id,b.nick_name,b.avatar,c.content  from zs_card_click a left join zs_ios_user b on a.u_id=b.id 
 left join zs_card c on a.c_id=c.id   where a.card_user_id={$user_id} order by a.create_time desc limit {$start},{$showPage}");

        $this->ajax_return(0,$card);
    }
    //评论通知
    public function feedbacklistAction(){
        $user_id=$_POST['id'];
        $currentPage = empty($_POST["page"]) ? "1" : $_POST["page"];
        $showPage = empty($_POST["showpage"]) ? "10" : $_POST["showpage"];
        $start = ($currentPage - 1) * $showPage;
        //社区评论通知
        $card_feedback=$this->db->action("select a.u_id as id,a.feedtime as create_time,a.body as content,b.nick_name,b.avatar,c.content as ycontent ,c.id as c_id from zs_card_feedback a
left join zs_ios_user b on a.u_id=b.id left join zs_card c on a.c_id=c.id  where a.card_user_id={$user_id}");
        foreach ($card_feedback as $key=>$value)
        {
            $card_feedback[$key]['type']=1;
        }
        //社区评论回复通知
        $card_reply=$this->db->action("select a.user_id as id,a.create_time,a.content,a.floor_id,a.feedback_id,b.nick_name,b.avatar,a.feedback_id as c_id from zs_card_reply a 
left join zs_ios_user b on a.user_id=b.id left join zs_card_feedback c on a.feedback_id=c.id where a.feedback_user_id={$user_id}");
        foreach ($card_reply as $key=>$value)
        {
            $card_reply[$key]['type']=2;
            if($value['floor_id']==0)
            {
                $result = $this->db->field("body as content")->table("zs_card_feedback")->where(" id = {$value['feedback_id']}")->find();
            }
            else{
                $result = $this->db->field("*")->table("zs_card_reply")->where(" id = {$value['floor_id']}")->find();
            }
            $card_reply[$key]['ycontent']=$result['content'];
            unset($card_reply[$key]['floor_id']);
            unset($card_reply[$key]['feedback_id']);
        }
        //地图评论通知
        $map_feedback=$this->db->action("select a.user_id as id,a.feedtime as create_time,a.body as content,b.nick_name,b.avatar,c.content as ycontent ,c.id as c_id from zs_map_feedback a
left join zs_ios_user b on a.user_id=b.id left join zs_user_map c on a.m_id=c.id  where a.map_user_id={$user_id}");
        foreach ($map_feedback as $key=>$value)
        {
            $map_feedback[$key]['type']=3;
        }
        //地图评论回复通知
        $map_reply=$this->db->action("select a.user_id as id,a.create_time,a.content,a.floor_id,a.feedback_id,b.nick_name,b.avatar,a.feedback_id as c_id from zs_map_reply a 
left join zs_ios_user b on a.user_id=b.id left join zs_map_feedback c on a.feedback_id=c.id where a.feedback_user_id={$user_id}");
        foreach ($map_reply as $key=>$value)
        {
            $map_reply[$key]['type']=4;
            if($value['floor_id']==0)
            {
               $result = $this->db->field("body as content")->table("zs_map_feedback")->where(" id = {$value['feedback_id']}")->find();
            }
            else{
                $result = $this->db->field("*")->table("zs_map_reply")->where(" id = {$value['floor_id']}")->find();
            }
            $map_reply[$key]['ycontent']=$result['content'];
            unset($map_reply[$key]['floor_id']);
            unset($map_reply[$key]['feedback_id']);
        }
        $data = array_merge($card_feedback,$card_reply,$map_feedback,$map_reply);
        $data=$this->test($data);
        $data = array_slice($data,$start,$showPage);
        $this->ajax_return(0,$data);
    }
    public function test($data){

        $newArr = array();
 
        foreach($data as $key=>$v){
            $newArr[$key]['create_time'] = $v['create_time'];
        }
        array_multisort($newArr,SORT_DESC,$data);//SORT_DESC为降序，SORT_ASC为升序
        return $data;

    }
    //系统通知
    public function systemlistAction(){
        $user_id=$_POST['id'];
        if(empty($_POST['id']))
        {
            $user_id=0;
        }
        $currentPage = empty($_POST["page"]) ? "1" : $_POST["page"];
        $showPage = empty($_POST["showpage"]) ? "10" : $_POST["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $data=$this->db->action("select * from zs_system_notify order by create_time desc limit {$start},{$showPage}");
        foreach ($data as $key=>$value)
        {
            $result = $this->db->field("*")->table("zs_user_notice")->where("u_id={$user_id} and notice_id={$value['id']}")->find();
            if(!empty($result))
            {
                $data[$key]['status']=1;
            }
        }
        $this->ajax_return(0,$data);
    }
    //读取通知
    public function readnoticeAction(){
        $user_id=$_POST['id'];
        $notice_id=$_POST['notice_id'];
        $result = $this->db->field("*")->table("zs_user_notice")->where("u_id={$user_id} and notice_id={$notice_id}")->find();
        $notify = $this->db->field("*")->table("zs_system_notify")->where("id={$notice_id}")->find();
        unset($notify['status']);
        if(empty($result))
        {
            $data['u_id']=$user_id;
            $data['notice_id']=$notice_id;
            $bool=$this->db->action( $this->db->insertSql("user_notice",$data));
            if($bool)
            {
                $this->ajax_return(0,$notify);
            }
            else{
                $this->ajax_return(500);
            }
        }
        else{
            $this->ajax_return(0,$notify);
        }
    }
    //用户屏蔽帖子
    public function screenAction(){
        if(!empty($_POST['id']) && !empty($_POST['c_id'])){
            $data['u_id']=$_POST['id'];
            $data['c_id']=$_POST['c_id'];
            $bool = $this->db->action($this->db->insertSql("card_screen",$data));
            if($bool){
                $this->ajax_return(0);
            }else{
                $this->ajax_return(500);
            }
        }else{
            $this->ajax_return(102);
        }
    }

    public function screencard($user){
        $res = $this->db->action("SELECT c_id FROM zs_card_screen WHERE u_id = {$user}");
        if(!empty($res)){
            $arr = [];
            foreach ($res as $k=>$v){
                $arr[] = $v['c_id'];
            }
            $str = implode(",",$arr);
            return $str;
        }else{
            return false;
        }
    }
}