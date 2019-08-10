<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;

class Zscard extends Model{

    public $c_id;
    public $u_id;
	public $content;
	public $click;
	public $create_time;
	public $lbs;
	public $pic;
	public $pdb;
	public $share_num;

	public function initialize(){
        include APP_PATH."/core/Pdb.php";
        $this->pdb = new Pdb();
        $this->pdb->action("set names utf8mb4");
    }

    public function news_model($reqdata){
	    $type =empty($reqdata["type"])?"news":$reqdata["type"];
	    $id = $reqdata['id'];
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
        $start = ($currentPage-1)*$showPage;
        $field = "u.id,u.uid,u.nick_name,u.avatar,c.c_id,c.content,c.is_lbs,c.pic,c.lbs,c.longitude,c.latitude,c.create_time,c.click,c.share_num";
        if($type == "news"){
            $result = $this->pdb->field($field)
                ->table("zscard as c")
                ->join("zsuser as u","c.u_id = u.id")
                ->order("c.create_time DESC")
                ->limit($start,$showPage)
                ->select();
        }elseif($type == 'recommend'){
            $result = $this->pdb->field($field)
                ->table("zscard as c")
                ->join("zsuser as u","c.u_id = u.id")
                ->order("c.create_time DESC")
                ->limit($start,$showPage)
                ->select();
        }else{
            $feienddata = $this->pdb->field("friend_id")->table("zsfriend")->where(" status = '1' and me_id = {$id}")->select();
          //  if(!empty($feienddata)){
                $indata = [];
            if(!empty($feienddata)) {
                foreach ($feienddata as $key => $value) {
                    $indata[] = $value['friend_id'];
                }
            }
                array_push($indata,$id);
                $str = implode(",",$indata);
                $result = $this->pdb->field($field)
                    ->table("zscard as c")
                    ->join("zsuser as u","c.u_id = u.id")
                    ->where("u.id IN ({$str})")
                    ->order("c.c_id DESC")
                    ->limit($start,$showPage)
                    ->select();
           // }
        }
        if(!empty($result)){
            foreach ($result as $k=>$v){
                unset($result[$k]['click']);
                $result[$k]['is_click'] = !empty($this->pdb->field("*")->table("zscardclick")->where(" c_id = {$result[$k]['c_id']} and u_id = {$id}")->find())?1:0;
                $result[$k]['click_num'] = $this->pdb->zscount("cardclick","*","total","c_id={$result[$k]['c_id']}");;
                $result[$k]['feedback_num'] = $this->pdb->zscount("cardfeedback","*","total","c_id={$result[$k]['c_id']}");
                if(!empty($v['nick_name'])) {
                    $result[$k]['nick_name'] = parseHtmlemoji($result[$k]['nick_name']);
                }
                else{
                    $result[$k]['nick_name']=$v['uid'];
                }
                $result[$k]['ntype'] = 2;
                $result[$k]['pic']=json_decode($v['pic']);
                $result[$k]['content'] = htmlspecialchars_decode($result[$k]['content']);
            }
            return $result;
        }else{

            return [];
        }
    }

	public function release_model($reqdata,$file){
        $arrdata = $this->pdb->field("*")->table("zsuser")->where(" id = {$reqdata['id']}")->find();
        if(!empty($arrdata)){
            if( !is_null($reqdata['content']) && !is_null($reqdata['lbs']) ){
                if(!empty($file)){
                    $this->pdb->action("set names utf8mb4");
                    $data['c_id'] = NULL;
                    $data['u_id'] = $reqdata['id'];
                    $data['content'] = htmlspecialchars($reqdata['content']);
                    $data['click'] = 0;
                    $data['create_time'] = time();
                    $data['lbs'] = $reqdata['lbs'];
                    $data['pic'] = $file;
                    $data['longitude'] = $reqdata['longitude'];
                    $data['latitude'] = $reqdata['latitude'];
                    $data['is_lbs'] = $reqdata['is_lbs'];
                    $bool = $this->pdb->action($this->pdb->insertSql("zscard",$data));
                    if($bool){
//                        if(count($indata)>0)
//                        {
//                            include BASE_PATH."/vendor/JPush/Message.php";
//                            $user = $this->pdb->field("nick_name,avatar")->table("zsuser")->where("id={$reqdata['id']}")->find();
//                            if (!empty($user['avatar'])) {
//                                $user['avatar'] = "http://test.pettap.cn" . $user['avatar'];
//                            } else {
//                                $user['avatar'] = "";
//                            }
//                            $arr=["type"=>"4","cate"=>1,"uid"=>$indata,"pid"=>$reqdata['id'],"pnickname"=>$user['nick_name'],"pavatar"=>$user['avatar'],"data"=>$reqdata['content']];
//                            $message=new Message();
//                            $message->send($arr);
//                        }
                        return 0;
                    }else{
                        return 500;
                    }
                }else{
                    return 102;
                }
            }else{
                return 102;
            }
        }else{
            return 1013;
        }
    }

    public function detail_model($reqdata){
        $id = $reqdata['id'];
	    $c_id = $reqdata['c_id'];
        $result = $this->pdb->field("u.id,u.uid,u.nick_name,u.avatar,c.c_id,c.content,c.is_lbs,c.pic,c.lbs,c.longitude,c.latitude,c.create_time,c.share_num")
            ->table("zscard as c")
            ->join("zsuser as u","c.u_id = u.id")
            ->where(" c.c_id = {$c_id} ")
            ->find();
        if(!empty($result)){
            $result['is_click'] = !empty($this->pdb->field("*")->table("zscardclick")->where(" c_id = {$c_id} and u_id = {$id}")->find())?1:0;
            $result['click_num'] = $this->pdb->zscount("cardclick","*","total","c_id={$c_id}");;
            $result['feedback_num'] = $this->pdb->zscount("cardfeedback","*","total","c_id={$c_id}");
            $result['pic']=json_decode($result['pic']);
            if(!empty($result['nick_name'])) {
                $result['nick_name'] = parseHtmlemoji($result['nick_name']);
            }
            else{
                $result['nick_name'] = $result['uid'] ;
            }
            return $result;
        }else{
            return (object)[];
        }
    }

    public function click_model($reqdata){
        $c_id = $reqdata['c_id'];
        $id = $reqdata['id'];
        $zsuser = new Zsuser();
        $zscardclick = new Zscardclick();
        $zscard = new Zscard();
        $zscardressave = $zscard::findFirst("c_id = {$c_id}");
        $zscardres= $this->pdb->field("*")->table("zscard")->where(" c_id = {$c_id}")->find();
        if($zscardres){
            //$zscardressave->click = (int)$zscardres['click'] + 1;
           // $zscardressave->save();
            $arrdata = $zsuser::findFirstById($id)->toArray();
            if($arrdata){
                $clickdata = $zscardclick::findFirst(" c_id = {$c_id} and u_id = {$id} ");
                if($clickdata){
                    //修改
                    $bool = $clickdata->delete();
                }else{
                    //添加
                    $zscardclick->u_id = $id;
                    $zscardclick->c_id = $c_id;
                    $zscardclick->status = 0;
                    $zscardclick->create_time = time();
                    $zscardclick->card_user_id = $zscardres['u_id'];
                    $bool = $zscardclick->save();
                 if($reqdata['id']!=$zscardres['u_id'])
                {
                    include BASE_PATH."/vendor/JPush/Message.php";
                    if (!empty($arrdata['avatar'])) {
                        $arrdata['avatar'] = "http://test.pettap.cn" . $arrdata['avatar'];
                    } else {
                        $arrdata['avatar'] = "";
                    }
                    if(!empty($arrdata['nick_name']))
                    {
                        $pnickname=parseHtmlemoji($arrdata['nick_name']);
                    }
                    else{
                        $pnickname=$arrdata['uid'];
                    }
                    $arr=["type"=>"0","cate"=>2,"uid"=>$zscardres['u_id'],"pid"=>$reqdata['id'],"pnickname"=>$pnickname,"pavatar"=>$arrdata['avatar'],"data"=>htmlspecialchars_decode($zscardres['content']),"feedbackuser"=>""];
                    $message=new Message();
                    $message->send($arr);
                }
                }
                return $bool ? 0 : 500;
            }else{
                return 1013;
            }
        }else{
            return 1100;
        }
    }

    public function addfeedback_model($reqdata){
        $arrdata = $this->pdb->field("*")->table("zsuser")->where(" id = {$reqdata['id']}")->find();
        if(!empty($arrdata)){
            $data['u_id'] = $reqdata['id'];
            $data['c_id'] = $reqdata['c_id'];
            $data['body'] = htmlspecialchars($reqdata['body']);
            $data['feedtime'] = time();
            $card = $this->pdb->field("*")->table("zscard")->where(" c_id = {$reqdata['c_id']}")->find();
            $data['card_user_id'] = $card['u_id'];
            $bool = $this->pdb->action($this->pdb->insertSql("zscardfeedback",$data));
            if($bool){
                if($reqdata['id']!=$card['u_id'])
                {
                    include BASE_PATH."/vendor/JPush/Message.php";
                    if (!empty($arrdata['avatar'])) {
                        $arrdata['avatar'] = "http://test.pettap.cn" . $arrdata['avatar'];
                    } else {
                        $arrdata['avatar'] = "";
                    }
                    if(!empty($arrdata['nick_name']))
                    {
                        $pnickname=parseHtmlemoji($arrdata['nick_name']);
                    }
                    else{
                        $pnickname=$arrdata['uid'];
                    }
                    $arr=["type"=>"1","cate"=>2,"uid"=>$card['u_id'],"pid"=>$reqdata['id'],"pnickname"=>$pnickname,"pavatar"=>$arrdata['avatar'],"data"=>$reqdata['body'],"feedbackuser"=>""];
                    $message=new Message();
                    $message->send($arr);
                }
                return 0;
            }else{
                return 500;
            }
        }else{
            return 1013;
        }
    }

    public function feedback_model($reqdata){
        $map = $this->pdb->field("*")->table("zscard")->where(" c_id = {$reqdata['c_id']}")->find();
        if(!empty($map)){
            $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
            $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
            $start =  ($currentPage-1)*$showPage;
            $sql  = "
                SELECT u.id,u.uid,u.nick_name,u.avatar,f.id as feedback_id,f.body,f.feedtime
                FROM zscardfeedback as f
                INNER JOIN zsuser as u ON u.id = f.u_id
                WHERE f.c_id = {$reqdata['c_id']}
                ORDER BY f.id DESC
                LIMIT {$start},{$showPage}";
            $this->pdb->action("set names utf8mb4");
            $result = $this->pdb->action($sql);
            foreach ($result as $k=>$v){
                if(!empty($v['nick_name'])) {
                    $result[$k]['nick_name'] = parseHtmlemoji($result[$k]['nick_name']);
                }
                else{
                    $result[$k]['nick_name']=$v['uid'];
                }
                $result[$k]['reply']['replysum'] = $this->replysum($v['feedback_id']);
                $result[$k]['reply']['replydata']=$this->replydata($v['feedback_id']);

            }
            return $result;
        }else{
            return 1400;
        }
    }

    public function addreply_model($reqdata){
        $arrdata = $this->pdb->field("*")->table("zsuser")->where(" id = {$reqdata['id']}")->find();
        if($reqdata['floor_id']==0) {
            $reply = $this->pdb->field("*")->table("zscardfeedback")->where(" id = {$reqdata['feedback_id']}")->find();
            $content=$reply['body'];
        }
        else{
            $reply = $this->pdb->field("*")->table("zscardreply")->where(" id = {$reqdata['floor_id']}")->find();
            $content=$reply['content'];
        }
        if(!empty($arrdata)){
            if($reqdata['floor_id']==0) {
                $result = $this->pdb->field("u_id as user_id")->table("zscardfeedback")->where(" id = {$reqdata['feedback_id']}")->find();
            }else{
                $result = $this->pdb->field("*")->table("zscardreply")->where(" id = {$reqdata['floor_id']}")->find();
            }
            $data['user_id'] = $reqdata['id'];
            $data['feedback_id'] = $reqdata['feedback_id'];
            $data['content'] = addslashes($reqdata['content']);
            $data['floor_id'] = $reqdata['floor_id'];
            $data['feedback_user_id'] = $result['user_id'];
            $data['c_id'] = $reply['c_id'];
            $data['create_time'] = time();
            $bool = $this->pdb->action($this->pdb->insertSql("zscardreply",$data));
            if($bool){
                $uid=[];
                if($reqdata['id']!=$result['user_id'])
                {
                    $uid[]=$result['user_id'];
                    $feeduser="";
                    if(!empty($reply['c_id']))
                    {
                        //echo $reply['c_id'];exit;
                        $card=$this->pdb->field("u_id")->table("zscard")->where(" c_id = {$reply['c_id']}")->find();
                        $feedbackuser=$this->pdb->field("*")->table("zsuser")->where(" id = {$result['user_id']}")->find();;
                        if($card['u_id']!=$result['user_id']) {
                            $uid[] = $card['u_id'];
                            if(!empty($feedbackuser['nick_name'])) {
                                $feeduser = parseHtmlemoji($feedbackuser['nick_name']);
                                }
                            else{
                                $feeduser=$feedbackuser['uid'];
                            }
                        }
                    }
                    include BASE_PATH."/vendor/JPush/Message.php";
                    if (!empty($arrdata['avatar'])) {
                        $arrdata['avatar'] = "http://test.pettap.cn" . $arrdata['avatar'];
                    } else {
                        $arrdata['avatar'] = "";
                    }
                    if(!empty($arrdata['nick_name']))
                    {
                        $pnickname=parseHtmlemoji($arrdata['nick_name']);
                    }
                    else{
                        $pnickname=$arrdata['uid'];
                    }
                    $arr=["type"=>"2","cate"=>2,"uid"=>$uid,"pid"=>$reqdata['id'],"pnickname"=>$pnickname,"pavatar"=>$arrdata['avatar'],"data"=>$content,"feedbackuser"=>$feeduser];
                    $message=new Message();
                    $message->send($arr);
                }
                return 0;
            }else{
                return 500;
            }
        }else{
            return 1013;
        }
    }

    public function reply_model($reqdata){
        $feedback_id = $reqdata['feedback_id'];
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $fsql  = "
            SELECT u.id,u.uid,u.nick_name,u.avatar,f.id as feedback_id,f.body,f.feedtime
            FROM zscardfeedback as f
            INNER JOIN zsuser as u ON u.id = f.u_id
            WHERE f.id = {$feedback_id}";
        $feedback = $this->pdb->action($fsql);
        if(!empty($feedback[0]['nick_name'])) {
            $feedback[0]['nick_name'] = parseHtmlemoji($feedback[0]['nick_name']);
        }
        else{
            $feedback[0]['nick_name']=$feedback[0]['uid'];
        }
        $result['feedback'] = $feedback[0];
        $sql = "
            SELECT u.id,u.uid,u.nick_name,u.avatar,r.id as r_id,r.content,r.create_time,r.floor_id,r.feedback_user_id
            FROM zscardreply as r
            INNER JOIN zsuser as u ON u.id = r.user_id
            WHERE r.feedback_id = {$feedback_id}
            ORDER BY r.id DESC
            LIMIT {$start},{$showPage} ";
        $reply = $this->pdb->action($sql);
        foreach ($reply as $key=>$value)
        {
            if(!empty($value['nick_name'])) {
                $reply[$key]['nick_name'] = parseHtmlemoji($reply[$key]['nick_name']);
            }
            else{
                $reply[$key]['nick_name']=$value['uid'];
            }
            if ($value['floor_id'] == 0) {
                $this->pdb->action("set names utf8mb4");
                $user = $this->pdb->action("select a.nick_name,a.id,a.uid from zsuser a where a.id={$value['feedback_user_id']}");
                if (!empty($user)) {
                    if(!empty($user[0]['nick_name'])) {
                        $reply[$key]['reply_user'] = parseHtmlemoji($user[0]['nick_name']);
                    }
                    else{
                        $reply[$key]['reply_user'] = $user[0]['uid'];
                    }
                }
            } else {
                $user = $this->pdb->action("select a.nick_name,a.id,a.uid from zsuser a left join zscardreply b on a.id=b.user_id where b.id={$value['floor_id']}");
                if (!empty($user)) {
                    if(!empty($user[0]['nick_name'])) {
                        $reply[$key]['reply_user'] = parseHtmlemoji($user[0]['nick_name']);
                    }
                    else{
                        $reply[$key]['reply_user'] = $user[0]['uid'];
                    }
                }
            }
            unset( $reply[$key]['feedback_user_id']);
        }
        $result['replydata'] = $reply;
        return $result;
    }

    public function replydata($data){
        $sql = "
            SELECT u.id,u.uid,u.nick_name,u.avatar,r.id as r_id,r.content,r.create_time,r.floor_id,r.feedback_user_id
            FROM zscardreply as r
            INNER JOIN zsuser as u ON u.id = r.user_id
            WHERE r.feedback_id = {$data}
            ORDER BY r.id DESC
            LIMIT 0,3 ";
        $data = $this->pdb->action($sql);
        foreach ($data as $key=>$value)
        {
            if(!empty($value['nick_name'])) {
                $data[$key]['nick_name'] = parseHtmlemoji($data[$key]['nick_name']);
            }
            else{
                $data[$key]['nick_name'] = $value['uid'];
            }
            if ($value['floor_id'] == 0) {
                $this->pdb->action("set names utf8mb4");
                $user = $this->pdb->action("select a.nick_name,a.id ,a.uid from zsuser a where a.id={$value['feedback_user_id']}");
                if (!empty($user)) {
                    if(!empty($user[0]['nick_name'])) {
                        $data[$key]['reply_user'] = parseHtmlemoji($user[0]['nick_name']);
                    }
                    else{
                        $data[$key]['reply_user'] = $user[0]['uid'];
                    }
                }
            } else {
                $user = $this->pdb->action("select a.nick_name,a.id,a.uid from zsuser a left join zscardreply b on a.id=b.user_id where b.id={$value['floor_id']}");
                if (!empty($user)) {
                    if(!empty($user[0]['nick_name'])) {
                        $data[$key]['reply_user'] = parseHtmlemoji($user[0]['nick_name']);
                    }
                    else{
                        $data[$key]['reply_user'] = $user[0]['uid'];
                    }
                }
            }
            unset( $data[$key]['feedback_user_id']);
        }
        return $data;
    }
    public function replysum($data){
        $sql = "
            SELECT count(*) as total
            FROM zscardreply as r
            INNER JOIN zsuser as u ON u.id = r.user_id
            WHERE r.feedback_id = {$data}
            ORDER BY r.id DESC
            LIMIT 0,3 ";
        $data = $this->pdb->action($sql);
        return $data[0]['total'];
    }

    //    public function screen_model($reqdata){
//        if(!empty($reqdata['id']) && !empty($reqdata['m_id'])){
//            $zsmapscreen = new Zsmapscreen();
//            $zsmapscreen->u_id = $reqdata['id'];
//            $zsmapscreen->m_id = $reqdata['m_id'];
//            $bool = $zsmapscreen->save();
//            if($bool){
//                $this->ajax_return(0);
//            }else{
//                $this->ajax_return(500);
//            }
//        }else{
//            $this->ajax_return(102);
//        }
//    }
//

//    public function merc_model($reqdata){
//        $zshospital = new Zshospital();
//        $result = $zshospital::findFirst("h_id = {$reqdata['h_id']}");
//        if($result){
//            return $result;
//        }else{
//            return (object)[];
//        }
//    }




}