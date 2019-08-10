<?php
//use Yaf\Application;
//use Yaf\Dispatcher;
//class IosmapController extends Yaf\Controller_Abstract{
class IosmapController extends IosbaseController{
     //地图用户信息查询
     public function maplbsAction(){
         //一公里为：0.010
         $rule = 0.05;
         $longitude = $_POST['longitude'];
         $latitude = $_POST['latitude'];
         $Letflong = $longitude - $rule;
         $rightlong = $longitude + $rule;
         $Letflat = $latitude - $rule;
         $rightlat = $latitude + $rule;
         $where = "WHERE ( m.longitude BETWEEN {$Letflong} and {$rightlong} ) and (m.latitude BETWEEN {$Letflat} and {$rightlat})";
         $sql = "
SELECT u.id,u.uid,u.avatar,m.longitude,m.latitude,m.id as m_id,t.color
FROM zs_user_map as m 
INNER JOIN zs_ios_user as u ON u.id = m.user_id
INNER JOIN zs_map_type as t ON t.map_id = m.map_id
{$where}";
         $result = $this->db->action($sql);
         if(!empty($result)){
             $this->ajax_return(0,$result);
         }else{
             $this->ajax_return(0,[]);
         }
     }
     //地图用户信息点击头像
     public function maplbsclickAction(){
        $m_id = $_POST['m_id'];
        $sql = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,m.id as m_id,m.title,m.pic,m.longitude,m.latitude,m.create_time,m.lbs,t.color
FROM zs_user_map as m 
INNER JOIN zs_ios_user as u ON u.id = m.user_id
INNER JOIN zs_map_type as t ON t.map_id = m.map_id
WHERE m.id = {$m_id}";
        $result = $this->db->action($sql);
         foreach ($result as $k=>$v){
             if(strlen($result[$k]['pic']) == 2){
                 $result[$k]['type'] = 3;
             }elseif(strstr($result[$k]['pic'], '.mp4') != false){
                 $result[$k]['type'] = 2;
             }else{
                 $result[$k]['type'] = 1;
             }
         }
        if(!empty($result)){
            $this->ajax_return(0,$result[0]);
        }else{
            $this->ajax_return(0,(object)[]);
        }
     }
     //地图发布类型
     public function mapaddtypeAction(){
         $data = $this->db->action("SELECT * FROM zs_map_type");
         $this->ajax_return(0,$data);
     }
     //地图发布帖子
     public function mapaddcardAction(){
         //$post = $this->getRequest()->getPost();
         //一公里为：0.010
         $rule = 0.05;
         $longitude = $_POST['longitude'];
         $latitude = $_POST['latitude'];
         $Letflong = $longitude - $rule;
         $rightlong = $longitude + $rule;
         $Letflat = $latitude - $rule;
         $rightlat = $latitude + $rule;
         $where = "WHERE ( p.longitude BETWEEN {$Letflong} and {$rightlong} ) and (p.latitude BETWEEN {$Letflat} and {$rightlat})";
         $sql = "select u.id from zs_ios_user as u inner join zs_user_point as p on u.id=p.u_id {$where}";
         $result = $this->db->action($sql);
         $indata=[];
         $str="";
         foreach ($result as $key=>$value)
         {
             if($value['id']!=$_POST['id'])
             {
                 $indata[]=$value['id'];
             }
         }
         $res = $_POST;
         if(!empty($this->files['pic']['name'])){
             $file = $this->files['pic'];
             $data['pic'] = $this->uploadss($file,"map");
             $data['user_id'] = $res['id'];
             $data['map_id'] = $res['map_id'];
             $data['object'] = $res['object'];
             $data['title'] = $res['title'];
             $data['address'] = $res['address'];
             $data['content'] = $res['content'];
             $data['longitude'] = $res['longitude'];
             $data['latitude'] = $res['latitude'];
             $data['tel'] = $res['tel'];
             $data['create_time'] = time();
             $data['status'] = 1;
             $data['end_time'] = $res['end_time'];
             $data['lbs'] = $res['lbs'];
             $this->db->action("set names utf8mb4");
             $bool = $this->db->action($this->db->insertSql("user_map",$data));
             if($bool){
                 if(count($indata)>0)
                 {
                     $user = $this->db->field("nick_name,avatar")->table("zs_ios_user")->where("id={$res['id']}")->find();
                     if (!empty($user['avatar'])) {
                         $user['avatar'] = "http://www.pettap.cn/public/user/" . $user['avatar'];
                     } else {
                         $user['avatar'] = "";
                     }
                     $arr=["type"=>"4","uid"=>$indata,"pid"=>$res['id'],"pnickname"=>$user['nick_name'],"pavatar"=>$user['avatar'],"data"=>$res['title']];
                     $this->send($arr);
                 }
                 $this->ajax_return(0);
             }else{
                 $this->ajax_return(500);
             }
         }else{
             $this->ajax_return(106);
         }
     }
     //地图内容详情
     public function mapdetailAction(){
         $m_id = $_POST['m_id'];
         $sql = "
SELECT m.id as m_id,u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,u.competence,t.title,t.color,m.longitude,m.latitude,m.address,m.object,m.content,m.pic,m.tel,m.title as m_title,m.create_time,m.lbs,m.end_time
FROM zs_user_map as m 
INNER JOIN zs_ios_user as u ON u.id = m.user_id 
INNER JOIN zs_map_type as t ON t.map_id = m.map_id 
WHERE m.id = {$m_id}";
         $result = $this->db->action($sql);
         $this->ajax_return(0,$result[0]);
     }
     //地图内容详情评论展示
     public function mapfeedbackAction(){
         $m_id = $_POST['m_id'];
         $currentPage = empty($_POST["page"])?"1":$_POST["page"];
         $showPage = empty($_POST["showpage"])?"9":$_POST["showpage"];
         $start =  ($currentPage-1)*$showPage;
         $sql  = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,f.id as feedback_id,f.body,f.feedtime
FROM zs_map_feedback as f 
INNER JOIN zs_ios_user as u ON u.id = f.user_id 
WHERE f.m_id = {$m_id} 
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
     //地图内容详情添加评论
     public function mapaddfeedbackAction(){
         $cardData = $this->db->field("*")->table("zs_user_map")->where(" id = {$_POST['m_id']}")->find();
         $user = $this->db->field("nick_name,avatar")->table("zs_ios_user")->where("id={$_POST['id']}")->find();
         $data['user_id'] = $_POST['id'];
         $data['m_id'] = $_POST['m_id'];
         $data['body'] = addslashes($_POST['body']);
         $data['feedtime'] = time();
         $data['map_user_id']=$cardData['user_id'];
         $this->db->action("set names utf8mb4");
         $bool = $this->db->action($this->db->insertSql("map_feedback",$data));
         if($bool){
             if($_POST['id']!=$cardData['user_id'])
             {
                 if (!empty($user['avatar'])) {
                     $user['avatar'] = "http://www.pettap.cn/public/user/" . $user['avatar'];
                 } else {
                     $user['avatar'] = "";
                 }
                 $arr=["type"=>"1","uid"=>$cardData['user_id'],"pid"=>$_POST['id'],"pnickname"=>$user['nick_name'],"pavatar"=>$user['avatar'],"data"=>$cardData['title']];
                 //$this->ajax_return(0,$arr);
                 $this->send($arr);  
             }
             else{
                 $this->ajax_return(0);
             }
         }else{
             $this->ajax_return(500);
         }
     }
     //地图内容详情添加回复
     public function mapaddreplyAction(){
         if($_POST['floor_id']==0) {
             $result = $this->db->field("*")->table("zs_map_feedback")->where(" id = {$_POST['feedback_id']}")->find();
             $content=$result['body'];
         }
         else{
             $result = $this->db->field("*")->table("zs_map_reply")->where(" id = {$_POST['floor_id']}")->find();
             $content=$result['content'];
         }
         $user = $this->db->field("nick_name,avatar")->table("zs_ios_user")->where("id={$_POST['id']}")->find();
         $data['user_id'] = $_POST['id'];
         $data['feedback_id'] = $_POST['feedback_id'];
         $data['content'] = addslashes($_POST['content']);
         $data['floor_id'] = $_POST['floor_id'];
         $data['feedback_user_id'] = $result['user_id'];
         $data['create_time'] = time();
         $this->db->action("set names utf8mb4");
         $bool = $this->db->action($this->db->insertSql("map_reply",$data));
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
                $this->send($arr);
             }
             else{
                 $this->ajax_return(0);
             }
         }else{
             $this->ajax_return(500);
         }
     }
     //地图内容回复详情展示
     public function mapreplyAction(){
         $feedback_id = $_POST['feedback_id'];
         $currentPage = empty($_POST["page"])?"1":$_POST["page"];
         $showPage = empty($_POST["showpage"])?"9":$_POST["showpage"];
         $start =  ($currentPage-1)*$showPage;
         $fsql  = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,f.id as feedback_id,f.body,f.feedtime
FROM zs_map_feedback as f 
INNER JOIN zs_ios_user as u ON u.id = f.user_id 
WHERE f.id = {$feedback_id}";
         $feedback = $this->db->action($fsql);
         $result['feedback'] = $feedback[0];
         $sql = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,r.id as r_id,r.content,r.create_time,r.floor_id 
FROM zs_map_reply as r 
INNER JOIN zs_ios_user as u ON u.id = r.user_id 
WHERE r.feedback_id = {$feedback_id} 
ORDER BY r.id DESC 
LIMIT {$start},{$showPage} ";
         $reply = $this->db->action($sql);
         $result['reply'] = $reply;
         $this->ajax_return(0,$result);
     }
     //用户实时跟新定位
     public function lbsAction(){
         $id = $_POST['id'];
         $longitude = $_POST['longitude'];
         $latitude = $_POST['latitude'];
         $userData = $this->db->field("id")->table("zs_ios_user")->where(" id = {$id}")->find();
         if(!empty($userData)){
             $this->ajax_return(500);
         }
         $cardData = $this->db->field("u_id,longitude,latitude")->table("zs_user_point")->where(" u_id = {$id}")->find();
         $data['u_id'] = $id;
         $data['longitude'] = $longitude;
         $data['latitude'] = $latitude;
         if(!empty($cardData)){
             $bool = $this->db->action($this->db->updateSql("user_point",$data,"u_id = {$id}"));
         }else{
             $bool = $this->db->action($this->db->insertSql("user_point",$data));
         }
         if($bool){
             $this->ajax_return(0);
         }else {
             $this->ajax_return(500);
         }
     }
     public function replydata($data){
         $sql = "
SELECT u.id,u.uid,u.nick_name,u.avatar,u.user_age,u.sex,u.pet_age,r.id as r_id,r.content,r.create_time,r.floor_id 
FROM zs_map_reply as r 
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
FROM zs_map_reply as r 
INNER JOIN zs_ios_user as u ON u.id = r.user_id 
WHERE r.feedback_id = {$data} 
ORDER BY r.id DESC 
LIMIT 0,3 ";
         $data = $this->db->action($sql);
         return $data[0]['total'];
     }

}