<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;

class Zsmap extends Model{

    public $m_id;
    public $user_id;
	public $content;
	public $pic;
	public $longitude;
	public $latitude;
	public $lbs;
	public $create_time;
	public $status;
    public $type;
    public $pdb;
    public $share_num;

	public function initialize(){
        include APP_PATH."/core/Pdb.php";
        $this->pdb = new Pdb();
        $this->pdb->action("set names utf8mb4");
    }

    public function index_model($reqdata){
        //一公里为：0.010
        $zsmaprange = new Zsmaprange();
        $resrange = $zsmaprange::findFirstById(1)->toArray();
        $rule = 0.01 * (int)$resrange['range'];
        $longitude = $reqdata['longitude'];
        $latitude = $reqdata['latitude'];
        $Letflong = $longitude - $rule;
        $rightlong = $longitude + $rule;
        $Letflat = $latitude - $rule;
        $rightlat = $latitude + $rule;
        $user_id=$reqdata['id']?$reqdata['id']:'-1';
         $where = "WHERE ( m.longitude BETWEEN {$Letflong} and {$rightlong} ) and (m.latitude BETWEEN {$Letflat} and {$rightlat})";
        //用户
        $sql = "
            SELECT u.id,u.uid,u.avatar,m.longitude,m.latitude,m.m_id
            FROM zsmap as m 
            INNER JOIN zsuser as u ON u.id = m.user_id
            {$where}";
        $result = $this->pdb->action($sql);
        if(!empty($result)){
            foreach ($result as $key=>$value)
            {
                $result[$key]['viewstatus']=0;
                if($user_id!='-1')
                {

                    $view=$this->pdb->action("select * from zsmapview where m_id={$value['m_id']} and u_id={$user_id}");
                    if(count($view)>0)
                    {
                        $result[$key]['viewstatus']=1;
                    }
                }

            }
            $maplist = $result;
        }else{
            $maplist = [];
        }
        //商家
        $mercwhere = "WHERE ( longitude BETWEEN {$Letflong} and {$rightlong} ) and (latitude BETWEEN {$Letflat} and {$rightlat})";
        $mercsql = "SELECT h_id,name,longitude,latitude,merc_type FROM zshospital {$mercwhere}";
        $mercresult = $this->pdb->action($mercsql);
        if(!empty($mercresult)){
            $merc = $mercresult;
        }else{
            $merc = [];
        }
        return ["maplist"=>$maplist,"merc"=>$merc];
    }

	public function release_model($reqdata,$file){
        $arrdata = $this->pdb->field("*")->table("zsuser")->where(" id = {$reqdata['id']}")->find();
        if(!empty($arrdata)){
            if( !is_null($reqdata['content']) && !is_null($reqdata['lbs']) ){
                if(!empty($file)){
                    $this->pdb->action("set names utf8mb4");
                    $data['m_id'] = NULL;
                    $data['user_id'] = $reqdata['id'];
                    $data['content'] = htmlspecialchars($reqdata['content']);
                    $data['pic'] = $file;
                    $data['longitude'] = $reqdata['longitude'];
                    $data['latitude'] = $reqdata['latitude'];
                    $data['lbs'] = $reqdata['lbs'];
                    $data['create_time'] = time();
                    $data['status'] = 1;
                    $rule = 0.05;
                    $Letflong = $reqdata['longitude'] - $rule;
                    $rightlong = $reqdata['longitude'] + $rule;
                    $Letflat = $reqdata['latitude'] - $rule;
                    $rightlat = $reqdata['latitude'] + $rule;
                    $where = " WHERE ( longitude BETWEEN {$Letflong} and {$rightlong} ) and (latitude BETWEEN {$Letflat} and {$rightlat}) and id!={$reqdata['id']}";
                    $sql = "select id from zsuser {$where}";
                    $result = $this->pdb->action($sql);
                    $indata=[];
                    foreach ($result as $key=>$value)
                    {
                        if($value['id']!=$reqdata['id'])
                        {
                            $indata[]=$value['id'];
                        }
                    }
                    $bool = $this->pdb->action($this->pdb->insertSql("zsmap",$data));
                    if($bool){
                        if(count($indata)>0)
                        {
                            include BASE_PATH."/vendor/JPush/Message.php";
                            //$user = $this->pdb->field("nick_name,avatar,uid,id,longitude,latitude")->table("zsuser")->where("id={$reqdata['id']}")->find();
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
                            $arr=["type"=>"4","cate"=>4,"uid"=>$indata,"pid"=>$reqdata['id'],"pnickname"=>$pnickname,"pavatar"=>$arrdata['avatar'],"data"=>$reqdata['content'],"feedbackuser"=>""];
                            $message=new Message();
                            $new=["puid"=>$arrdata['uid'],"longitude"=>$reqdata['longitude'],"latitude"=>$reqdata['latitude'],"m_id"=>$this->pdb->getInsertId(),"viewstatus"=>0];
                            $message->sendmap($arr,$new);
                        }
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
	    $sql = "SELECT u.id,u.uid,u.avatar,u.nick_name,m.m_id,m.create_time,m.content,m.pic,m.longitude,m.latitude,m.lbs,m.share_num
	    FROM zsuser as u 
	    INNER JOIN zsmap as m ON m.user_id = u.id
	    WHERE m.m_id = {$reqdata['m_id']}
	    ";
        $arrdata = $this->pdb->action($sql);
        if(!empty($arrdata)){
            if($reqdata['id']!="-1") { 
                $mapview = new Zsmapview();
                $result = $mapview::findFirst("u_id={$reqdata['id']} and m_id={$reqdata['m_id']}");
                if (empty($result)) {
                    $mapview->u_id = $reqdata['id'];
                    $mapview->m_id = $reqdata['m_id'];
                    $mapview->create_time = time();
                    $mapview->save();
                }
            }
            if(!empty( $arrdata[0]['nick_name']))
            {
                $arrdata[0]['nick_name']  =parseHtmlemoji($arrdata[0]['nick_name']);
            }
            else{
                $arrdata[0]['nick_name']=$arrdata[0]['uid'];
            }
            $arrdata[0]['pic'] = json_decode($arrdata[0]['pic']);
            $arrdata[0]['content'] = htmlspecialchars_decode($arrdata[0]['content']);
            $arrdata[0]['click_num'] =$this->pdb->zscount("mapclick","*","total","m_id={$arrdata[0]['m_id']}");
            $arrdata[0]['feedback_num'] = $this->pdb->zscount("mapfeedback","*","total","m_id={$arrdata[0]['m_id']}");
           // $arrdata[0]['share_num'] = "0";
            $arrdata[0]['is_click'] = !empty($this->pdb->field("*")->table("zsmapclick")->where(" m_id = {$reqdata['m_id']} and u_id = {$reqdata['id']}")->find())?1:0;
            return $arrdata[0];
        }else{
            return 1400;
        }
    }

    public function click_model($reqdata){
        $zsuser = new Zsuser();
        $arrdata = $zsuser::findFirstById($reqdata['id'])->toArray();
        //$user=$arrdata;
        if($arrdata){
            $zsmapclick = new Zsmapclick();
            $clickdata = $zsmapclick::findFirst(" m_id = '{$reqdata['m_id']}' and u_id = '{$reqdata['id']}' ");
            $zsmap = new Zsmap();
            if($clickdata){
                //修改
                $result = $zsmapclick::findFirstByM_id($reqdata['m_id']);
                $bool = $result->delete();
            }else{
                //添加
                //$userdata = $zsmap::findFirstByM_id($reqdata['m_id']);
                $userdata= $this->pdb->field("*")->table("zsmap")->where(" m_id = {$reqdata['m_id']}")->find();
                $zsmapclick->u_id = $reqdata['id'];
                $zsmapclick->m_id = $reqdata['m_id'];
                $zsmapclick->status = 0;
                $zsmapclick->create_time = time();
                $zsmapclick->map_user_id = $userdata['user_id'];
                $bool = $zsmapclick->save();
                if($reqdata['id']!=$userdata['user_id'])
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

                    $arr=["type"=>"0","cate"=>1,"uid"=>$userdata['user_id'],"pid"=>$reqdata['id'],"pnickname"=>$pnickname,"pavatar"=>$arrdata['avatar'],"data"=>htmlspecialchars_decode($userdata['content']),"feedbackuser"=>""];
                    $message=new Message();
                    $message->send($arr);
                }
            }
            return $bool ? 0 : 500;
        }else{
            return 1013;
        }
    }

    public function log_model($reqdata){
        $where = "";
        $intdata = $this->screen($reqdata['id']);
        if($reqdata['type'] == "all"){
            $where = "";
        }elseif( ($reqdata['type'] == "my") && ($reqdata['id'] <> -1) ){
            if($intdata){
                $where = "WHERE m.user_id = {$reqdata['id']}  AND m.m_id NOT IN ($intdata) ";
            }else{
                $where = "WHERE m.user_id = {$reqdata['id']}";
            }
        }else{
            $where = "";
        }
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql = "SELECT u.id,u.uid,u.avatar,u.nick_name,m.m_id,m.create_time,m.content,m.pic,m.longitude,m.latitude,m.lbs,m.share_num
	    FROM zsuser as u 
	    INNER JOIN zsmap as m ON m.user_id = u.id
	    {$where}
	    order by m.create_time desc
	    LIMIT {$start},{$showPage}
	    ";
        $arrdata = $this->pdb->action($sql);
        if(!empty($arrdata)){
            foreach ($arrdata as $k=>$v){
                $arrdata[$k]['pic'] = json_decode($v['pic']);
                $arrdata[$k]['content'] = htmlspecialchars_decode($v['content']);
                $arrdata[$k]['click_num'] = $this->pdb->zscount("mapclick","*","total","m_id={$v['m_id']}");
                $arrdata[$k]['feedback_num'] = $this->pdb->zscount("mapfeedback","*","total","m_id={$v['m_id']}");
                if(!empty($v['nick_name'])) {
                    $arrdata[$k]['nick_name'] = parseHtmlemoji($v['nick_name']);
                }
                else{
                    $arrdata[$k]['nick_name']=$v['uid'];
                }
                $arrdata[$k]['ntype']=1;
                $arrdata[$k]['is_click'] = !empty($this->pdb->field("*")->table("zsmapclick")->where(" m_id = {$v['m_id']} and u_id = {$reqdata['id']}")->find())?1:0;
            }
            return $arrdata;
        }else{
            return [];
        }
    }

    public function addfeedback_model($reqdata){
        $arrdata = $this->pdb->field("*")->table("zsuser")->where(" id = {$reqdata['id']}")->find();
        if(!empty($arrdata)){
            $data['user_id'] = $reqdata['id'];
            $data['m_id'] = $reqdata['m_id'];
            $data['body'] = htmlspecialchars($reqdata['body']);
            $data['feedtime'] = time();
            $map = $this->pdb->field("*")->table("zsmap")->where(" m_id = {$reqdata['m_id']}")->find();
            $data['map_user_id'] = $map['user_id'];
            $bool = $this->pdb->action($this->pdb->insertSql("zsmapfeedback",$data));
            if($bool){
                if($reqdata['id']!=$map['user_id'])
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
                    $arr=["type"=>"1","cate"=>1,"uid"=>$map['user_id'],"pid"=>$reqdata['id'],"pnickname"=>$pnickname,"pavatar"=>$arrdata['avatar'],"data"=>$reqdata['body'],"feedbackuser"=>""];
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
        $map = $this->pdb->field("*")->table("zsmap")->where(" m_id = {$reqdata['m_id']}")->find();
        if(!empty($map)){
            $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
            $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
            $start =  ($currentPage-1)*$showPage;
            $sql  = "
                SELECT u.id,u.uid,u.nick_name,u.avatar,f.id as feedback_id,f.body,f.feedtime
                FROM zsmapfeedback as f 
                INNER JOIN zsuser as u ON u.id = f.user_id 
                WHERE f.m_id = {$reqdata['m_id']} 
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
                $result[$k]['reply']['replydata'] = $this->replydata($v['feedback_id']);
            }
            return $result;
        }else{
            return 1400;
        }
    }

    public function addreply_model($reqdata){
        $arrdata = $this->pdb->field("*")->table("zsuser")->where(" id = {$reqdata['id']}")->find();
        if($reqdata['floor_id']==0) {
            $reply = $this->pdb->field("*")->table("zsmapfeedback")->where(" id = {$reqdata['feedback_id']}")->find();
            $content=$reply['body'];
        }
        else{
            $reply = $this->pdb->field("*")->table("zsmapreply")->where(" id = {$reqdata['floor_id']}")->find();
            $content=$reply['content'];
        }
        if(!empty($arrdata)){
            $data['user_id'] = $reqdata['id'];
            $data['feedback_id'] = $reqdata['feedback_id'];
            $data['content'] = addslashes($reqdata['content']);
            $data['floor_id'] = $reqdata['floor_id'];
            $data['feedback_user_id'] = $reply['user_id'];
            $data['m_id'] = $reply['m_id'];
            $data['create_time'] = time();
            $bool = $this->pdb->action($this->pdb->insertSql("zsmapreply",$data));
            if($bool){
                if($reqdata['id']!=$reply['user_id'])
                {
                    include BASE_PATH."/vendor/JPush/Message.php";
                    $uid[]=$reply['user_id'];
                    $feeduser="";
                    if(!empty($reply['m_id']))
                    {
                        $map=$this->pdb->field("user_id")->table("zsmap")->where(" m_id = {$reply['m_id']}")->find();
                        $feedbackuser=$this->pdb->field("*")->table("zsuser")->where(" id = {$reply['user_id']}")->find();;
                        if($map['user_id']!=$reply['user_id']) {
                            $uid[] = $map['user_id'];
                            if(!empty($feedbackuser['nick_name'])) {
                                $feeduser = parseHtmlemoji($feedbackuser['nick_name']);
                            }
                            else{
                                $feeduser=$feedbackuser['uid'];
                            }
                        }
                    }
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
                    $arr=["type"=>"2","cate"=>1,"uid"=>$uid,"pid"=>$reqdata['id'],"pnickname"=>$pnickname,"pavatar"=>$arrdata['avatar'],"data"=>$content,"feedbackuser"=>$feeduser];
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
            SELECT u.id,u.uid,u.nick_name,u.avatar,u.uid,f.id as feedback_id,f.body,f.feedtime
            FROM zsmapfeedback as f 
            INNER JOIN zsuser as u ON u.id = f.user_id 
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
            SELECT u.id,u.uid,u.nick_name,u.avatar,r.id as r_id,r.content,r.create_time,r.floor_id ,r.feedback_user_id
            FROM zsmapreply as r 
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
                $reply[$key]['nick_name'] =$value['uid'];
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
                $user = $this->pdb->action("select a.nick_name,a.id,a.uid from zsuser a left join zsmapreply b on a.id=b.user_id where b.id={$value['floor_id']}");
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

    public function lbs_model($reqdata){
        $zsuser = new Zsuser();
        $arrdata = $zsuser::findFirstById($reqdata['id']);
        if($arrdata){
            $arrdata->longitude = $reqdata['longitude'];
            $arrdata->latitude = $reqdata['latitude'];
            $bool = $arrdata->save();
            if($bool){
                return 0;
            }else {
                return 500;
            }
        }else{
            return 0;
        }
    }

    public function screen_model($reqdata){
        if(!empty($reqdata['id']) && !empty($reqdata['m_id'])){
            $zsmapscreen = new Zsmapscreen();
            $zsmapscreen->u_id = $reqdata['id'];
            $zsmapscreen->m_id = $reqdata['m_id'];
            $bool = $zsmapscreen->save();
            if($bool){
                $this->ajax_return(0);
            }else{
                $this->ajax_return(500);
            }
        }else{
            $this->ajax_return(102);
        }
    }

    public function merc_model($reqdata){
        $zshospital = new Zshospital();
        $result = $zshospital::findFirst("h_id = {$reqdata['h_id']}");
        if($result){
            $result->himg = json_decode($result->himg);
            return $result;
        }else{
            return (object)[];
        }
    }
    //通知
    public function message_model($reqdata){
        $user_id=$reqdata['id'];
        $currentPage = empty($reqdata["page"]) ? "1" : $reqdata["page"];
        $showPage = empty($reqdata["showpage"]) ? "10" : $reqdata["showpage"];
        $start = ($currentPage - 1) * $showPage;
      //地图帖子点赞
        $mapclick=$this->pdb->action("select a.u_id as id,a.create_time,a.m_id,b.nick_name,b.avatar,b.uid,c.content,c.pic from zsmapclick a left join zsuser b on a.u_id=b.id 
 left join zsmap c on a.m_id=c.m_id   where a.map_user_id={$user_id} ");
        foreach ($mapclick as $key=>$value)
        {
            $mapclick[$key]['content'] = htmlspecialchars_decode($value['content']);
            $mapclick[$key]['mtype']=1;//点赞
            $mapclick[$key]['pic']=json_decode($value['pic']);
            $mapclick[$key]['reply_user']="";
            if(!empty($value['nick_name'])) {
                $mapclick[$key]['nick_name'] = parseHtmlemoji($value['nick_name']);
            }
            else{
                $mapclick[$key]['nick_name'] =$value['uid'];
            }
        }
        //地图帖子评论
        $mapfeedback=$this->pdb->action("select a.user_id as id,a.feedtime as create_time,a.body as content,b.nick_name,b.avatar,b.uid,c.pic ,
a.m_id from zsmapfeedback a left join zsuser b on a.user_id=b.id left join zsmap c on a.m_id=c.m_id  where a.map_user_id={$user_id}");
        foreach ($mapfeedback as $key=>$value)
        {
            $mapfeedback[$key]['content'] = htmlspecialchars_decode($value['content']);
            $mapfeedback[$key]['mtype']=2;
            $mapfeedback[$key]['reply_user']="";
            $mapfeedback[$key]['pic']=json_decode($value['pic']);
            if(!empty($value['nick_name'])) {
                $mapfeedback[$key]['nick_name'] = parseHtmlemoji($value['nick_name']);
            }
            else{
                $mapfeedback[$key]['nick_name']=$value['uid'];
            }
        }
        //地图帖子回复
        $map_reply=$this->pdb->action("select a.user_id as id,a.create_time,a.content,a.floor_id,b.nick_name,b.avatar,b.uid,a.feedback_id as m_id from zsmapreply a 
left join zsuser b on a.user_id=b.id left join zsmapfeedback c on a.feedback_id=c.id where a.feedback_user_id={$user_id}");
        foreach ($map_reply as $key=>$value)
        {
            $map_reply[$key]['content'] = htmlspecialchars_decode($value['content']);
            $map_reply[$key]['mtype']=3;
            if(!empty($value['nick_name'])) {
                $map_reply[$key]['nick_name'] = parseHtmlemoji($value['nick_name']);
            }
            else{
                $map_reply[$key]['nick_name']=$value['uid'];
            }
            if($value['floor_id']==0)
            {
                $map_reply[$key]['reply_user']="你";
            }
            else{
                $result=$this->pdb->action("select a.nick_name,a.id,a.uid from zsuser a left join zsmapreply b on a.id=b.user_id where b.id={$value['floor_id']}");
                if(!empty($result[0]['nick_name'])) {
                    $map_reply[$key]['reply_user'] = parseHtmlemoji($result[0]['nick_name']);
                }
                else{
                    if(!empty($result)) {
                        $map_reply[$key]['reply_user'] = $result[0]['uid'];
                    }
                }

            }
            $map=$this->pdb->action("select a.pic from zsmap a left join zsmapfeedback b on a.m_id=b.m_id where b.id={$value['m_id']}");
            if(count($map)>0) {
                $map_reply[$key]['pic'] = json_decode($map[0]['pic']);
            }
            unset($map_reply[$key]['floor_id']);
            unset($map_reply[$key]['feedback_id']);
        }
        $data = array_merge($mapclick,$mapfeedback,$map_reply);
        $data=$this->test($data);
        $data = array_slice($data,$start,$showPage);
       return $data;
    }
    public function test($data){

        $newArr = array();

        foreach($data as $key=>$v){
            $newArr[$key]['create_time'] = $v['create_time'];
        }
        array_multisort($newArr,SORT_DESC,$data);//SORT_DESC为降序，SORT_ASC为升序
        return $data;

    }
    public function replydata($data){
        $sql = "
            SELECT u.id,u.uid,u.nick_name,u.avatar,r.id as r_id,r.content,r.create_time,r.floor_id,r.feedback_user_id
            FROM zsmapreply as r 
            INNER JOIN zsuser as u ON u.id = r.user_id 
            WHERE r.feedback_id = {$data} 
            ORDER BY r.id DESC 
            LIMIT 0,3 ";
        $data = $this->pdb->action($sql);
        foreach ($data as $key=>$value)
        {
            if(!empty($value['nick_name'])) {
                $data[$key]['nick_name'] = parseHtmlemoji($value['nick_name']);
            }
            else{
                $data[$key]['nick_name'] = $value['uid'];
            }
            if ($value['floor_id'] == 0) {
                $this->pdb->action("set names utf8mb4");
                $user = $this->pdb->action("select a.nick_name,a.id,a.uid from zsuser a where a.id={$value['feedback_user_id']}");
                if (!empty($user)) {
                    if(!empty($user[0]['nick_name'])) {
                        $data[$key]['reply_user'] = parseHtmlemoji($user[0]['nick_name']);
                    }
                    else{
                        $data[$key]['reply_user'] = $user[0]['uid'];
                    }
                }
            } else {
                $user = $this->pdb->action("select a.nick_name,a.id ,a.uid from zsuser a left join zsmapreply b on a.id=b.user_id where b.id={$value['floor_id']}");
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
            FROM zsmapreply as r 
            INNER JOIN zsuser as u ON u.id = r.user_id 
            WHERE r.feedback_id = {$data} 
            ORDER BY r.id DESC 
            LIMIT 0,3 ";
        $data = $this->pdb->action($sql);
        return $data[0]['total'];
    }
    public function screen($user){
        $zsmapscreen = new Zsmapscreen();
        $res = $zsmapscreen::find( " u_id = {$user} ")->toArray();
        if(!empty($res)){
            $arr = [];
            foreach ($res as $k=>$v){
                $arr[] = $v['m_id'];
            }
            $str = implode(",",$arr);
            return $str;
        }else{
            return false;
        }
    }


}