<?php
class IospersonController extends IosbaseController{
    //宠我
    public function woAction(){
        $user_id=$_GET['id'];
        $resultdb=$this->db->action("select a.id,a.uid,a.sex,a.nick_name,a.pet_age,a.user_age,a.pet_cur,a.u_sign,a.avatar,
a.user_level,a.pet_breed,a.pet_sex,a.pet_sterilization,b.lbs,b.my_background from zs_ios_user a 
 left join zs_user_point b on a.id=b.u_id where a.id={$user_id}");
        $user=$this->db->field("location_province,location_city,location_area")->table("zs_user")->where("id = {$user_id}")->find();
         $arrx = get_userinfo_data($user_id);
         //$resultdb[0]['avatar']="/public/user/". $resultdb[0]['avatar'];
        $resultdb[0]['follow_num'] = $arrx['follow_num'];
        $resultdb[0]['fans_num'] = $arrx['fans_num'];
        $resultdb[0]['location_province'] = $user['location_province'];
        $resultdb[0]['location_city'] = $user['location_city'];
        $resultdb[0]['location_area'] = $user['location_area']?$user['location_area']:'';
        $resultdb[0]['lbs'] =  $resultdb[0]['lbs']? $resultdb[0]['lbs']:'';
        $resultdb[0]['pet_breed'] =  $resultdb[0]['pet_breed']? $resultdb[0]['pet_breed']:'';
        $resultdb[0]['u_sign'] =  $resultdb[0]['u_sign']? $resultdb[0]['u_sign']:'';
        $resultdb[0]['my_background'] =  $resultdb[0]['my_background']? $resultdb[0]['my_background']:'';
        $resultdb[0]['commonweal_price']= $arrx['commonweal_price'];
        $this->ajax_return(0,$resultdb[0]);
    }
    //朋友主页动态
    public function pzoneAction(){
        $reqdata =$_GET;
        $u_id = $reqdata['id'];
        $f_id = get("f_id");
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"10":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        if($u_id!=$f_id) {
            if (substr($f_id, 0, 1) == "C") {
                $arrData = $this->db->field("*")->table("zs_user")->where("uid = '{$f_id}'")->find();
            } else {
                $arrData = $this->db->field("*")->table("zs_user")->where("id = {$f_id}")->find();
            }
            $is_attention = empty($this->db->field("*")->table("zs_attention")->where("uid = {$u_id} and f_id = {$arrData['id']}")->find()) ? "0":"1";
            if($is_attention==0)
            {
                $start=0;
                $showPage=3;
            }
        }
        else{
            $is_attention=0;
        }
        $this->db->action("set names utf8mb4");
          //帖子列表
            $userinfo = $this->db->field("*")->table("zs_ios_user")->where("id = {$f_id}")->find();
            if(!empty($userinfo)){
                $sqlstr = "SELECT c.id as c_id,u.avatar,u.nick_name,u.competence,u.sex,u.user_age,u.pet_age,u.pet_sex, u.id,u.uid,
c.lbs,c.content,c.card_pic,c.click ,c.create_time,t.title 
 FROM zs_card as c LEFT JOIN zs_ios_user as u ON u.id = c.u_id  left join zs_card_type t on c.t_id=t.id WHERE u.id = {$f_id} ORDER BY c.id DESC LIMIT {$start},{$showPage}";
                $userinfocard = $this->db->action($sqlstr);
                foreach ($userinfocard as $key=>$value){
                    $arr_card = json_decode($value['card_pic'],true);
                    $arr = [];
                    for($i=0;$i<count($arr_card);$i++){
                        $arr[$i] = "/public/card/".$arr_card[$i];
                    }
                    $userinfocard[$key]['card_pic'] = json_encode($arr,320);
                    $userinfocard[$key]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where("u_id = {$u_id} and c_id = {$value['c_id']}")->select()) ? 1 : 0;
                    $userinfocard[$key]['feedback_num']=$this->db->zscount("card_feedback","*","total","c_id={$value['c_id']}");
                    $userinfocard[$key]['share_num']=$this->db->zscount("card_share","*","total","c_id={$value['c_id']}");
                    if(strlen($userinfocard[$key]['card_pic']) == 2){
                        $userinfocard[$key]['type'] = 3;
                    }elseif(strstr($userinfocard[$key]['card_pic'], '.mp4') != false){
                        $userinfocard[$key]['type'] = 2;
                    }else{
                        $userinfocard[$key]['type'] = 1;
                    }
                }
                $data['pzone']=$userinfocard;
                $data['is_attention']=$is_attention;
                $this->ajax_return(0,$data);
            }else{
                $this->ajax_return(1004);
            }

    }
    //我的关注
    public function myfriendsAction(){
        $id = get("id");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = empty($_GET["showpage"])?"10":$_GET["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql = "
              SELECT a.id,a.f_id,u.avatar,u.nick_name,u.uid,u.sex,u.user_age,u.pet_age,u.pet_sex,l.lbs
              FROM zs_attention as a
              INNER JOIN zs_ios_user as u ON u.id = a.f_id left join zs_user_point l on u.id=l.u_id
              WHERE a.uid = {$id} 
              LIMIT {$start},{$showPage} 
        ";
        $result = $this->db->action($sql);
        /*foreach ($result as $key=>$value)
        {
            $result[$key]['avatar']="/public/user/".$value['avatar'];
        }*/
       $this->ajax_return(0,$result);
    }
    //我的粉丝
    public function myfansAction(){
        $id = get("id");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = empty($_GET["showpage"])?"10":$_GET["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql = "
              SELECT a.id,a.f_id,u.avatar,u.nick_name,u.uid,u.sex,u.user_age,u.pet_age,u.pet_sex,l.lbs
              FROM zs_fans as a
              INNER JOIN zs_ios_user as u ON u.id = a.f_id left join zs_user_point l on u.id=l.u_id
              WHERE a.uid = {$id} 
              LIMIT {$start},{$showPage} 
        ";
        $result = $this->db->action($sql);
       /* foreach ($result as $key=>$value)
        {
            $result[$key]['avatar']="/public/user/".$value['avatar'];
        }*/
        $this->ajax_return(0,$result);
    }
    //取消关注宠友
    public function outfriendsAction(){
        $reqdata = $_GET;
        $news['uid'] = $reqdata['id'];
        $news['f_id'] = $reqdata['f_id'];
        $arrData = $this->db->field("*")->table("zs_ios_user")->where("id = {$reqdata['f_id']}")->find();
        if(!empty($arrData)){
            $bool = $this->db->action($this->db->deleteSql("attention","uid = {$reqdata['id']} and f_id = {$reqdata['f_id']}"));
            if($bool){
                $this->db->action($this->db->deleteSql("fans","f_id = {$reqdata['id']} and uid = {$reqdata['f_id']}"));
                $this->ajax_return(0);
            }else{
                $this->ajax_return(500);
            }
        }else{
            $this->ajax_return(1013);
        }
    }
    //关注宠友
    public function addfriendsAction(){
        $reqdata = $_GET;
        $news['uid'] = $reqdata['id'];
        $news['f_id'] = $reqdata['f_id'];
        $arrData = $this->db->field("*")->table("zs_ios_user")->where("id = {$reqdata['f_id']}")->find();
        if(!empty($arrData)){
            $result=$this->db->field("*")->table("zs_attention")->where("uid = {$reqdata['id']} and f_id={$reqdata['f_id']}")->find();
            if(!empty($result))
            {
                $this->ajax_return(1103);
            }
            $bool = $this->db->action($this->db->insertSql("attention",$news));
            if($bool){
                $fans['uid']=$reqdata['f_id'];
                $fans['f_id']=$reqdata['id'];
                $this->db->action($this->db->insertSql("fans",$fans));
                $this->ajax_return(0);
            }else{
                $this->ajax_return(500);
            }
        }else{
            $this->ajax_return(1013);
        }
    }
    //关于我们
    public function aboutusAction(){
        $setting=$this->db->field("*")->table("zs_ios_setting")->find();
        $this->ajax_return(0,$setting);
    }
    //app意见反馈
    public function debugAction(){
        $data['phone']=$_POST['phone'];
        $data['debug']=$_POST['debug'];
        $data['create_time'] = time();
        $bool = $this->db->action($this->db->insertSql("user_debug",$data));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //我的订单
    public function myorderAction(){
        $user_id = $_POST['id'];
        $type = empty($_POST['type'])?0:$_POST['type'];
        $field = "order_id,shop_banner,shop_name,shop_type,shop_num,shop_total_price,out_trade_no,express_status";
        $where = "";
        switch ($type){
            case 0: $where = "user_id = {$user_id} and pay_status = 1 and close_status = 1"; break;
            case 1: $where = "user_id = {$user_id} and pay_status = 1 and express_status = 0 and close_status = 1"; break;
            case 2: $where = "user_id = {$user_id} and pay_status = 1 and express_status = 1 and close_status = 1"; break;
            case 3: $where = "user_id = {$user_id} and pay_status = 1 and express_status = 2 and close_status = 1"; break;
            case 4: $where = "user_id = {$user_id} and pay_status = 1 and express_status = 4 and close_status = 1"; break;
            default : $where = "user_id = {$user_id} and pay_status = 1"; break;
        }
        $data = $this->db->action("SELECT {$field} FROM zs_shop_order WHERE {$where} ORDER BY order_id DESC");
        if(!empty($data)){
            $this->ajax_return(0,$data);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //删除订单
    public function delorderAction(){
        $user_id = $_POST['id'];
        $out_trade_no = $_POST['out_trade_no'];
        $where = "user_id = {$user_id} and out_trade_no = '{$out_trade_no}' and pay_status = 1";
        $bool = $this->db->action($this->db->updateSql('shop_order',["close_status"=>0],$where));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //申请退货
    public function orderreturnAction(){
        $user_id = $_POST['id'];
        $out_trade_no = $_POST['out_trade_no'];
        $where = "user_id = {$user_id} and out_trade_no = '{$out_trade_no}' and pay_status = 1";
        $bool = $this->db->action($this->db->updateSql('shop_order',["express_status"=>3],$where));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //查看物流
    public function selectexpressAction(){
        $user_id = $_POST['id'];
        $out_trade_no = $_POST['out_trade_no'];
        $where = "o.user_id = {$user_id} and o.out_trade_no = '{$out_trade_no}' and o.pay_status = 1";
        $data = $this->db->field("e.express_company,e.express_order")
            ->table("zs_shop_order as o")
            ->join("zs_shop_express as e","e.out_trade_no = o.out_trade_no")
            ->where($where)
            ->find();
        if(!empty($data)){
            $this->ajax_return(0,$data);
        }else{
            $this->ajax_return(0,(object)[]);
        }
    }
    //宠物品种
    public function petbreedAction(){
        $pid = get("p_id");
        if(!empty($pid)){
            $result = $this->db->field("*")->table("zs_pet_breed")->where("p_id = {$pid}")->select();
            if(!empty($result)){
                $currentPage = empty($_GET["page"])?"1":$_GET["page"];
                $showPage = empty($_GET["showpage"])?"9":$_GET["showpage"];
                $start =  ($currentPage-1)*$showPage;
                $page = $this->db->action("SELECT * FROM zs_pet_breed WHERE p_id = {$pid} LIMIT {$start},{$showPage}");
                $this->ajax_return(0,$page);
            }else{
                $this->ajax_return(1011);
            }
        }else{
            $this->ajax_return(1010);
        }
    }

    public function testAction(){
        $time = time();
        $fileicon = $_FILES["my_avatar"];
        $dir = APP_PATH."/public/user/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $pathicon = $dir."/".$fileicon['name'];
        move_uploaded_file( $fileicon['tmp_name'],$pathicon);
        $fileArr = $time."/".$fileicon['name'];
        $data['avatar'] = $fileArr;
        $this->ajax_return(0,$data);
    }
    /**
     * 账号注册
     */
    public function accountregisterAction(){
        $arr['username'] = '17688830052';
        $resultdb = $this->db->field("*")->table("zs_ios_user")->where("account = '{$arr['username']}'")->findobj();
        if(!empty($resultdb)){
            $this->ajax_return(1009);
        }else{
            $arr['password']='a41ae6153c3ab3f2f756d8adaf1cdc2730dcb6eed44522a1b3d9308cc4a5fd0c';
            $arr['ts'] ='1558346409';
            // $arr['uuid'] = $reqdata['uuid'];
            // $sign = $reqdata['sign'];
            //验证sign
            // $result = $this->issign($arr,$sign);
            /*  if($result == "ok"){*/
            $user['uid'] =  "C".substr(time(),1);
            $user['account'] =  $arr['username'];
            $user['password'] =  $arr['password'];
            $user['mobile_num'] =  $arr['username'];
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
            $user['register_time'] =  $arr['ts'];
            $user['register_ip'] =  server("REMOTE_ADDR");
            $user['last_activity_date'] =  $arr['ts'];
            $user['is_online'] =  '1';
            $user['avatar'] = '';
            $outtime = $this->get7day($arr['ts']);
            $rand1 = range("a","z");
            $rand2 = range("0","9");
            $rand3 = range("A","Z");
            $randstr = $rand1[0].$rand2[0].$rand3[0];
            $basestr = $user['uid'].$outtime.$randstr;
            $user['access_token'] = hash_hmac("sha256",$basestr,self::APP_KEY);
            $bool = $this->db->action($this->db->insertSql("ios_user",$user));
            if($bool){
                $this->ajax_return(0);
            }else{
                $this->ajax_return(0,$this->db->insertSql("ios_user",$user));
            }
        }
        /*}*/

    }

}