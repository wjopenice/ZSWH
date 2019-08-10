<?php
use Yaf\Application;
use Yaf\Dispatcher;
class AttentionController extends Yaf\Controller_Abstract{
    public $db;
    public function init(){
        $this->db = new dbModel();
    }
    //关注宠友
    public function friendsAction(){
        Dispatcher::getInstance()->autoRender(false);
		$data = file_get_contents("php://input");
		$reqdata = json_decode($data,true);
        $news['uid'] = $reqdata['id'];
        $news['f_id'] = $reqdata['f_id'];
        $arrData = $this->db->field("*")->table("zs_user")->where("id = {$reqdata['f_id']}")->find();
        if(!empty($arrData)){
            $bool = $this->db->action($this->db->insertSql("attention",$news));
            if($bool){
                echo json_encode(['code'=>0,'message'=>"success"]);
            }else{
                echo json_encode(['code'=>500,'message'=>"系统繁忙，请稍候再试"]);exit;
            }
        }else{
            echo json_encode(['code'=>1013,'message'=>"没有此用户"]);exit;
        }
    }
    //取消关注宠友
    public function outfriendsAction(){
        Dispatcher::getInstance()->autoRender(false);
        $data = file_get_contents("php://input");
        $reqdata = json_decode($data,true);
        $news['uid'] = $reqdata['id'];
        $news['f_id'] = $reqdata['f_id'];
        $arrData = $this->db->field("*")->table("zs_user")->where("id = {$reqdata['f_id']}")->find();
        if(!empty($arrData)){
            $bool = $this->db->action($this->db->deleteSql("attention","uid = {$reqdata['id']} and f_id = {$reqdata['f_id']}"));
            if($bool){
                echo json_encode(['code'=>0,'message'=>"success"]);
            }else{
                echo json_encode(['code'=>500,'message'=>"系统繁忙，请稍候再试"]);exit;
            }
        }else{
            echo json_encode(['code'=>1013,'message'=>"没有此用户"]);exit;
        }
    }
    //宠友互通
    public function petfriendAction(){
        Dispatcher::getInstance()->autoRender(false);
        $uid = get("id");
        $f_id = get("f_id");
        if(substr($f_id,0,1) == "C" ){
            $arrData = $this->db->field("*")->table("zs_user")->where("uid = '{$f_id}'")->find();
        }else{
            $arrData = $this->db->field("*")->table("zs_user")->where("id = {$f_id}")->find();
        }
        if(!empty($arrData)){
            $arr['f_id'] = $f_id;
            $arr['f_uid'] = $arrData['uid'];
            $arr['avatar'] = $arrData['avatar'];
            $arr['nick_name'] = $arrData['nick_name'];
            $arr['user_level'] = $arrData['user_level'];
            $arr['location_province'] = $arrData['location_province'];
            $arr['location_city'] = $arrData['location_city'];
            $arr['u_sign'] = $arrData['u_sign'];
            $sql = "SELECT id,u_id,content,card_pic FROM zs_card WHERE u_id = {$arrData['id']} ORDER BY id DESC LIMIT 0,3";
            $cardData = $this->db->action($sql);
            $arr['pzone'] = $cardData;
            $arr['is_attention'] = empty($this->db->field("*")->table("zs_attention")->where("uid = {$uid} and f_id = {$arrData['id']}")->find()) ? "0":"1";
            echo json_encode(['code'=>0,'message'=>"success","data"=>$arr]);exit;
        }else{
            echo json_encode(['code'=>1013,'message'=>"没有此用户"]);exit;
        }
    }
    //宠友圈
    public function petcircleAction(){
        Dispatcher::getInstance()->autoRender(false);
        $uid = get("id");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = empty($_GET["showpage"])?"10":$_GET["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $arrData = $this->db->field("f_id")->table("zs_attention")->where("uid = {$uid}")->select();
        if(!empty($arrData)){
            $newData = [];
            foreach ($arrData as $key=>$value){$newData[$key] = $value['f_id'];}
            $strData = implode(",",$newData);
            $sql = "
            SELECT c.id,u.nick_name,u.uid,u.avatar,u.competence,c.u_id,c.t_id,c.lbs,c.content,c.create_time,c.click,c.card_pic 
            FROM zs_card as c
            INNER JOIN zs_user as u ON u.id = c.u_id
            WHERE c.u_id IN ($strData) 
            ORDER BY c.id DESC
            LIMIT {$start},{$showPage}
         ";
            $result = $this->db->action($sql);
            foreach($result as $key=>$value){
                $result[$key]['is_click'] = empty($this->db->action("select * from zs_card_click where c_id = {$value['id']} and u_id = {$uid}"))?0:1;
            }
            echo json_encode(['code'=>0,'message'=>"success","data"=>$result]);exit;
        }else{
            echo json_encode(['code'=>0,'message'=>"success","data"=>[]]);exit;
        }
    }
    //我的关注
    public function myfriendsAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = empty($_GET["showpage"])?"10":$_GET["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql = "
              SELECT a.id,a.f_id,u.avatar,u.nick_name,u.uid,u.location_province,u.location_city
              FROM zs_attention as a
              INNER JOIN zs_user as u ON u.id = a.f_id
              WHERE a.uid = {$id} 
              LIMIT {$start},{$showPage} 
        ";
        $result = $this->db->action($sql);
        echo json_encode(['code'=>0,'message'=>"success","data"=>$result]);exit;
    }
    //我的收藏
    public function mycollectionAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = empty($_GET["showpage"])?"10":$_GET["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql = "
            SELECT cc.id,cc.n_id,cc.n_title,n.author,n.create_time,n.pic,n.type 
            FROM zs_collection as cc 
            INNER JOIN zs_news as n ON n.id = cc.n_id
            WHERE u_id = {$id}
            ORDER BY n.create_time desc,cc.id DESC 
            LIMIT {$start},{$showPage} 
            ";
        $result = $this->db->action($sql);
        foreach ($result as $key=>$value){
            $x = time()-$value['create_time'];
            $i = floor($x/60%60);
            $h = floor($x/60/60%24);
            $d = floor($x/60/60/24);
            $result[$key]['create_time'] =  $d."天".$h."时".$i."分";
        }
        echo json_encode(['code'=>0,'message'=>"success","data"=>$result]);exit;
    }
}