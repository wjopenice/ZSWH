<?php
use Yaf\Application;
use Yaf\Dispatcher;
class UcController extends Yaf\Controller_Abstract  {
    public $db;
    public $data; //sdk接收数据
    const APP_KEY = "MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    public function init(){
        $this->db = new dbModel();
        $this->data = file_get_contents("php://input");
        if(empty($this->data)){
            echo json_encode(['code'=>104,'message'=>"请求方式错误"]);exit;
        }
    }
    //Dispatcher::getInstance()->autoRender(false);
    //用户基本信息SDK接口
    public function userinfoAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $resultdb = $this->db->field("*")->table("zs_user")->where("uid = '{$reqdata['uid']}'")->find();
        if(empty($resultdb)){
            echo json_encode(['code'=>1004,'message'=>"手机号不存在，请注册"]);exit;
        }else{
            $u_id = $resultdb['id'];
            $resultdb['groupid'] = 1;
            $resultdb['fans_num'] = 10;
            $arrx = get_userinfo_data($u_id);
            $resultdb['follow_num'] = $arrx['follow_num'];
            $resultdb['collection_num'] = $arrx['collection_num'];
            $resultdb['commonweal_price'] = $arrx['commonweal_price'];
            $resultdb['card_num'] = count($this->db->field("*")->table("zs_card")->where("u_id = {$u_id}")->select());
            //$resultdb['tagdata'] = $this->db->field("id,tag,u_id")->table("zs_tag")->where("u_id = {$u_id}")->limit(0,10)->select();
            echo json_encode(['code'=>0,'message'=>"success","data"=>$resultdb]);exit;
        }
    }
    //用户空间列表SDK接口
    public function pzoneAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $u_id = $reqdata['id'];
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"10":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $this->db->action("set names utf8mb4");
        if(empty($reqdata["u_id"])){
            //个人帖子列表
            $userinfo = $this->db->field("*")->table("zs_user")->where("id = {$u_id}")->find();
            if(!empty($userinfo)){
                $sqlstr = "SELECT c.id as c_id,u.avatar,u.nick_name,u.competence,c.lbs,c.content,c.card_pic,c.click as click_num,c.create_time FROM zs_card as c LEFT JOIN zs_user as u ON u.id = c.u_id WHERE u.id = {$u_id} ORDER BY c.id DESC LIMIT {$start},{$showPage}";
                $userinfocard = $this->db->action($sqlstr);
                foreach ($userinfocard as $key=>$value){
                    $userinfocard[$key]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where(" u_id = {$u_id} and c_id = {$value['c_id']}")->select()) ? 1 : 0;
                }
                echo json_encode(["code"=>0,"message"=>"success","data"=>$userinfocard]);
            }else{
                echo json_encode(["code"=>1004,"message"=>"手机号不存在，请注册"]);
            }
        }else{
            //宠友帖子列表
            $pet_id = $reqdata["u_id"];
            if(substr($pet_id,0,1) == "C" ){
                $userinfo = $this->db->field("*")->table("zs_user")->where("uid = '{$pet_id}'")->find();
            }else{
                $userinfo = $this->db->field("*")->table("zs_user")->where("id = {$pet_id}")->find();
            }
            if(!empty($userinfo)){
                $sqlstr = "SELECT c.id as c_id,u.avatar,u.nick_name,u.competence,c.lbs,c.content,c.card_pic,c.click as click_num,c.create_time FROM zs_card as c LEFT JOIN zs_user as u ON u.id = c.u_id WHERE u.id = {$userinfo['id']} ORDER BY c.id DESC LIMIT {$start},{$showPage}";
                $userinfocard = $this->db->action($sqlstr);
                foreach ($userinfocard as $key=>$value){
                    $userinfocard[$key]['is_click'] = !empty($this->db->field("*")->table("zs_card_click")->where(" u_id = {$userinfo['id']} and c_id = {$value['c_id']}")->select()) ? 1 : 0;
                }
                echo json_encode(["code"=>0,"message"=>"success","data"=>$userinfocard]);
            }else{
                echo json_encode(["code"=>1004,"message"=>"手机号不存在，请注册"]);
            }
        }
    }
    //用户添加好友SDK接口
    public function addfrinedAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $data['frined_id'] = $reqdata['follow_id'];
        $data['me_id'] = $reqdata['id'];
        $data['ts'] = $reqdata['ts'];
        $data['frined_account'] = $reqdata['follow_account'];
        $bool = $this->db->action($this->db->insertSql("frined",$data));
        if($bool){
            echo json_encode(["code"=>0,"message"=>"success"]);
        }else{
            echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
        }
    }
    //用户查找好友SDK接口
    public function searchAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $account = $reqdata['userdata'];
        $fieldstr = "id,uid,nick_name,avatar,location_province,location_city";
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        if(strtolower(substr($account,0,1)) == "c"){
            $result = $this->db->field($fieldstr)->table("zs_user")->where("uid")->like($account)->limit($start,$showPage)->select();
        }else {
            $result = $this->db->field($fieldstr)->table("zs_user")->where("mobile_num")->like($account)->limit($start,$showPage)->select();
            if(empty($result)){
                $result = $this->db->field($fieldstr)->table("zs_user")->where("nick_name")->like($account)->limit($start,$showPage)->select();
            }
        }
        echo json_encode(["code"=>"0","message"=>"success","data"=>$result]);
    }
    //用户我的好友SDK接口
    public function frinedAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $u_id = $reqdata['id'];
        $arrData = $this->db->field("frined_id,frined_account")->table("zs_frined")->where("me_id = {$u_id}")->select();
        echo json_encode(["code"=>0,"message"=>"success","data"=>$arrData]);
    }
    //批量获取用户信息
    public function userlistinfoAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $uidlist = $reqdata['u_id'];
        $arrData = explode(",",$uidlist);
        $userData = [];
        for($i=0;$i<count($arrData);$i++){
            $resultdb = $this->db->field("uid as id,nick_name,avatar")->table("zs_user")->where("uid = '{$arrData[$i]}'")->find();
            $userData[$i] = $resultdb;
        }
        echo json_encode(['code'=>0,'message'=>"success","data"=>$userData]);exit;
    }
    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }
    //用户删除标签SDK接口
//    public function usertagdelAction(){
//        Dispatcher::getInstance()->autoRender(false);
//        $reqdata = json_decode($this->data,true);
//        $tag_id = $reqdata['tag_id'];
//        $u_id = $reqdata['id'];
//        $result = $this->db->field("*")->table("zs_tag")->where("id = {$tag_id} and u_id = {$u_id}")->find();
//        if(!empty($result)){
//            $bool = $this->db->action($this->db->deleteSql("tag","id = {$tag_id} and u_id = {$u_id}"));
//            if($bool){
//                echo json_encode(["code"=>0,"message"=>"success"]);
//            }else{
//                echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
//            }
//        }else{
//            echo json_encode(["code"=>1008,"message"=>"没有找到此私有标签"]);
//        }
//    }
    //用户添加标签SDK接口
//    public function usertagaddAction(){
//        Dispatcher::getInstance()->autoRender(false);
//        $reqdata = json_decode($this->data,true);
//        $data['tag'] = $reqdata['tag'];
//        $data['u_id'] = $reqdata['id'];
//        $bool = $this->db->action($this->db->insertSql("tag",$data));
//        if($bool){
//            echo json_encode(["code"=>0,"message"=>"success"]);
//        }else{
//            echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);
//        }
//    }
    //用户标签列表SDK接口
//    public function usertagAction(){
//        Dispatcher::getInstance()->autoRender(false);
//        $reqdata = json_decode($this->data,true);
//        $u_id = $reqdata['id'];
//        //获取系统标签
//        $arrData1 = $this->db->field("id,tag,u_id")->table("zs_tag")->where("u_id = 0")->limit(rand(3,35),10)->select();
//        if(!empty($arrData1)){
//            echo json_encode($arrData1);
//        }else{
//            //echo json_encode(['code'=>1009,'message'=>"标签为空"]);
//            echo json_encode([]);
//        }
//    }
    //用户主页标签列表SDK接口
//    public function usertaglistAction(){
//        Dispatcher::getInstance()->autoRender(false);
//        $reqdata = json_decode($this->data,true);
//        $u_id = $reqdata['id'];
//        //获取私有标签
//        $arrData2 = $this->db->field("id,tag,u_id")->table("zs_tag")->where("u_id = {$u_id}")->limit(0,10)->select();
//        if(!empty($arrData2)){
//            echo json_encode($arrData2);
//        }else{
//            //echo json_encode(['code'=>1009,'message'=>"标签为空"]);
//            echo json_encode([]);
//        }
//    }

}
