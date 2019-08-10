<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;

class Zsgroup extends Model{

    public $g_id;
    public $banner;
	public $title;
	public $groupid;
    public $lbs;
	public $longitude;
	public $latitude;
	public $info;
    public $owner_id;
	public $status;
	public $members;
    public $create_time;

    public function index_model($reqdata){
        //一公里为：0.010
//        $zsmaprange = new Zsmaprange();
//        $resrange = $zsmaprange::findFirstById(1)->toArray();
//        $rule = 0.01 * (int)$resrange['range'];
//        $longitude = $reqdata['longitude'];
//        $latitude = $reqdata['latitude'];
//        $Letflong = $longitude - $rule;
//        $rightlong = $longitude + $rule;
//        $Letflat = $latitude - $rule;
//        $rightlat = $latitude + $rule;
//        $where = "WHERE ( m.longitude BETWEEN {$Letflong} and {$rightlong} ) and (m.latitude BETWEEN {$Letflat} and {$rightlat})";
//        //用户
//        $sql = "
//            SELECT u.id,u.uid,u.avatar,m.longitude,m.latitude,m.m_id
//            FROM zsmap as m
//            INNER JOIN zsuser as u ON u.id = m.user_id
//            {$where}";
//        $result = $this->pdb->action($sql);
//        if(!empty($result)){
//            $maplist = $result;
//        }else{
//            $maplist = [];
//        }
//        //商家
//        $mercwhere = "WHERE ( longitude BETWEEN {$Letflong} and {$rightlong} ) and (latitude BETWEEN {$Letflat} and {$rightlat})";
//        $mercsql = "SELECT h_id,name,longitude,latitude,merc_type FROM zshospital {$mercwhere}";
//        $mercresult = $this->pdb->action($mercsql);
//        if(!empty($mercresult)){
//            $merc = $mercresult;
//        }else{
//            $merc = [];
//        }
//        return ["maplist"=>$maplist,"merc"=>$merc];
    }

	public function addgroups_model($reqdata,$file){
        $addr=new Zsgroup();
        if(!empty($file)){
            if($addr::findFirst("title = '{$reqdata['title']}'")) {
                return 1500;
            }
            $addr->g_id = NULL;
            $addr->banner = $file;
            $addr->title = $reqdata['title'];
            $addr->groupid = "";
            $addr->info = $reqdata['info'];
            $addr->lbs = $reqdata['lbs'];
            $addr->longitude = $reqdata['longitude'];
            $addr->latitude = $reqdata['latitude'];
            $addr->owner_id = $reqdata['owner_id'];
            $addr->status = 0;
            $addr->members = json_encode([]);
            $addr->create_time = time();
            $bool = $addr->save();
            if($bool){
                return 0;
            }else{
                return 500;
            }
        }else{
            return 102;
        }
    }

    public function editgroups_model($reqdata){
        $g_id = $reqdata['g_id'];
        $addr=new Zsgroup();
        $result = $addr::findFirst("g_id = {$g_id}");
        $result->title = $reqdata['title'];
        $result->groupid = "";
        $result->info = $reqdata['info'];
        $result->lbs = $reqdata['lbs'];
        $result->longitude = $reqdata['longitude'];
        $result->latitude = $reqdata['latitude'];
        $result->owner_id = $reqdata['owner_id'];
        $result->status = 0;
        $result->members = json_encode([]);
        $result->create_time = time();
        $bool = $result->save();
        if($bool){
            return 0;
        }else{
            return 500;
        }
    }

    public function groups_model($reqdata){
        $uid = $reqdata['uid'];
        $rule = 0.010*1000;
        $longitude = $reqdata['longitude'];
        $latitude = $reqdata['latitude'];
        $currentPage = empty($reqdata["page"])?"1":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $Letflong = $longitude - $rule;
        $rightlong = $longitude + $rule;
        $Letflat = $latitude - $rule;
        $rightlat = $latitude + $rule;
        $where = "status = 1 and (longitude BETWEEN {$Letflong} and {$rightlong} ) and (latitude BETWEEN {$Letflat} and {$rightlat})";
        // $where = "status = 1 and (longitude BETWEEN {$Letflong} and {$rightlong} ) and (latitude BETWEEN {$Letflat} and {$rightlat}) and owner_id not like '%{$uid}%' and members not like '%{$uid}%'";
       /* $robots = $this->modelsManager->createBuilder()
            ->columns(["g_id","banner","title","groupid","info","lbs","longitude","latitude","members","owner_id"])
            ->from('Zsgroup')
            ->where($where)
            ->limit($showPage,$start)
            ->getQuery()
            ->execute()
            ->toArray();*/
       // include APP_PATH."/core/Pdb.php";
        $pdb = new \app\core\Pdb();
        $pdb->action("set names utf8mb4");
        $sql = "select g_id,banner,title,groupid,info,lbs,longitude,latitude,members,owner_id from zsgroup where {$where} order by g_id desc LIMIT {$start},{$showPage}  ";
       // echo $sql;exit;
        $robots = $pdb->action($sql);
        if($robots){
            foreach ($robots as $k=>$v){
                $robots[$k]['group_num'] = count(json_decode($v['members'],true))+1;
                $robots[$k]['title']=htmlspecialchars_decode($v['title']);
                $robots[$k]['info']=htmlspecialchars_decode($v['info']);
                unset($robots[$k]['members']);
                $mdata = json_decode($v['members'],true);
                if($uid == -1){
                    $member_status = -1;
                }elseif($uid == $v['owner_id']){
                    $member_status = 1;
                }elseif(in_array($uid,$mdata)){
                    $member_status = 2;
                }else{
                    $member_status = 3;
                }
                $robots[$k]['member_status'] = $member_status;
                unset($robots[$k]['owner_id']);
            }
            return $robots;
        }else{
            return [];
        }

    }

    public function groupsmembers_model($reqdata){
        $uid = $reqdata['uid'];
        $g_id = $reqdata['g_id'];
        $currentPage = empty($reqdata["page"])?"0":$reqdata["page"];
        $showPage = empty($reqdata["showpage"])?"9":$reqdata["showpage"];
        $zsgroup=new Zsgroup();
        $result = $zsgroup::findFirst("g_id = {$g_id} or groupid={$g_id}");
        if($result){
            $data['owner'] = $this->getuserinfo($result->owner_id,"owner");
            $mdata = json_decode($result->members,true);
            $len = count($mdata);
            if( $len > 0 ){
                $arr = [];
                for($i=0;$i<$len;$i++){
                    $arr[$i] = $this->getuserinfo($mdata[$i],"members");
                }
                $data['members'] =array_slice($arr,$currentPage,$showPage);
            }else{
                $data['members'] = [];
            }
            $data['group_num'] = $len+1;
            $data['group_status'] = $result->status;
            if($uid == -1){
                $data['member_status'] = -1;
            }elseif($uid == $result->owner_id){
                $data['member_status'] = 1;
            }elseif(in_array($uid,$mdata)){
                $data['member_status'] = 2;
            }else{
                $data['member_status'] = 3;
            }
            return $data;
        }else{
            return 102;
        }
    }

    public function detail_model($reqdata){
        $g_id = $reqdata['g_id'];
       /* $zsgroup=new Zsgroup();
        $result = $zsgroup::findFirst("g_id = {$g_id} or groupid={$g_id}");*/
        $pdb=new Pdb();
        $pdb->action("set names utf8mb4");
        $result=$pdb->field("*")->table("zsgroup")->where(" g_id = {$g_id} or groupid={$g_id}")->find();
        if($result){
            $detaildata['g_id'] = $result['g_id'];
            $detaildata['banner'] = $result['banner'];
            $detaildata['title'] =htmlspecialchars_decode($result['title']);
            $detaildata['groupid'] = $result['groupid'];
            $detaildata['info'] =htmlspecialchars_decode($result['info']);
            $detaildata['lbs'] = $result['lbs'];
            $detaildata['longitude'] = $result['longitude'];
            $detaildata['latitude'] = $result['latitude'];
            return $detaildata;
        }else{
            return [];
        }
    }

    //获取用户信息
    public function getuserinfo($uid,$type){
        $zsuser = new Zsuser();
        $userdata = $zsuser::findFirst("uid = '{$uid}'");
        if($userdata){
            $userinfo["id"] = $userdata->id;
            $userinfo[$type."_id"] = $uid;
            $userinfo[$type."_avatar"] = $userdata->avatar;
            if($userdata->nick_name) {
                $userinfo[$type . "_nick_name"] = parseHtmlemoji($userdata->nick_name);
            }
            else{
                $userinfo[$type . "_nick_name"] =$userdata->uid;
            }
            return $userinfo;
        }else{
           return [];
        }
    }




}