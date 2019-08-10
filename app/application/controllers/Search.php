<?php
class SearchController extends BaseController{
    //app搜索
    public function indexAction(){
        $type = empty($_GET['type'])?0:$_GET['type'];
        $uid=get('id')?get('id'):0;
       /* if(empty($_GET['search'])){
            $this->ajax_return(102);
        }*/
        $sql = null;
        $search = preg_replace("/[%_\s]+/","",ltrim(addslashes($_GET['search'])));
        $reslut = [];
        switch ($type){
            case 0:
               // $sql = "SELECT a.game_name,game_type_name,icon,game_address,game_size,and_dow_address,features FROM tab_game WHERE game_name LIKE '%{$search}%'";
                $sql="select a.game_name,a.game_type_name,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id 
      left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.game_name LIKE '%{$search}%' order by a.sort desc";
                $reslut = $this->db->action($sql);
                foreach ($reslut as $k=>$v){
                    if(!empty($v['icon'])) {
                        $reslut[$k]['icon'] = $this->get_cover($reslut[$k]['icon'], 'path');
                    }
                    if(!empty( $reslut[$k]['and_dow_address'])) {
                        $reslut[$k]['and_dow_address'] = ZSWH . substr( $reslut[$k]['and_dow_address'], 1);
                    }
                }
                break;
            case 1:
                $sql = "select a.id,a.title,a.create_time,a.pic,a.view,a.click,b.game_type_name as cate_title from tab_news a left join tab_game b on a.game_id=b.id
             where a.title like '%{$search}%' order by a.view desc ";
                $reslut = $this->db->action($sql);
                foreach ($reslut as $key=>$value)
                {
                    if(empty($value['cate_title']))
                    {
                        $reslut[$key]['cate_title']="";
                    }
                    $reslut[$key]['create_time']=get_time($value['create_time']);
                    //$reslut[$key]['click_count']=$this->db->zscount("news_click", "*", "total", "n_id={$value['id']}");
                }
                break;
            case 2:
                $gift= $this->db->action("select a.id as gift_id,a.game_id,a.giftbag_name,a.desribe,a.start_time,a.end_time,a.game_name,b.icon ,
 c.start_date,c.end_date from tab_giftbag a left join tab_game b  on 
a.game_id=b.id left join tab_gift_position c on a.id=c.gift_id
 where a.status=1 and b.game_status=1 and (a.giftbag_name like '%{$search}%' or a.game_name like '%{$search}%') order by a.id desc limit 12");
                foreach ($gift as $key => $value) {
                    $gift[$key]['icon'] = $this->get_cover($value['icon'], 'path');
                    $gift[$key]['novice_count'] = intval($this->db->zscount("gift_record", "*", "total", "gift_id={$value['gift_id']}"));
                    $ji=$this->db->field("novice")->table("tab_giftbag")->where("id={$value['gift_id']}")->find();
                    $at=explode(",",$ji['novice']);
                    $gift[$key]['novice_total']=intval($gift[$key]['novice_count']+count($at));
                    $isreceive="0";
                    $novice='';
                    $is_order=0;//不是预约礼包
                    if(!empty($value['start_date']))
                    {
                        $gift[$key]['start_time']=$value['start_date'];
                        $gift[$key]['end_time']=$value['end_date'];
                        $is_order=1;//是预约礼包
                    }
                    if(!empty($uid))
                    {
                        $record=$this->db->field("*")->table("tab_gift_record")->where("gift_id = {$value['gift_id']} and user_id={$uid}")->find();
                        if($record)
                        {
                            $isreceive="1";
                            if($is_order==0)
                            {
                                $novice=$record['novice'];
                            }
                        }
                    }
                    $gift[$key]['isreceive']=$isreceive;
                    $gift[$key]['novice']=$novice;
                    $gift[$key]['is_order']=$is_order;
                }
                $reslut=$gift;
                break;
            case 3:
                $sql = "SELECT id as group_type_id,game_name FROM tab_group_type WHERE game_name LIKE '%{$search}%'";
                $follow=$this->db->action($sql);
                $indata = [];
                foreach ($follow as $key=>$value){
                    $indata[] = $value['group_type_id'];
                }
                $str = implode(",",$indata);
                if(!empty($str)) {
                $sql = "
                SELECT A.id as a_id,U.id,U.avatar,U.nickname,A.create_time,A.content,A.pic,A.game_id,A.share_num
                FROM tab_user_group as A 
                INNER JOIN tab_user as U ON A.u_id = U.id
                WHERE A.game_id IN ($str);
                LIMIT 0,10
                ";
                   $reslut = $this->db->action($sql);
                   foreach ($reslut as $k => $v) {
                       $reslut[$k]['is_click'] = 0;
                       $reslut[$k]['create_time'] = get_time($v['create_time']);
                       $arrData = $this->db->field("*")->table("tab_group_click")->where("u_id={$uid} and group_log_id={$v['a_id']}")->find();
                       if (!empty($arrData)) {
                           $reslut[$k]['is_click'] = 1;
                       }
                       $reslut[$k]['click_num'] = $this->db->zscount("group_click", "*", "total", "group_log_id = {$v['a_id']} ");
                       $reslut[$k]['feedback_num'] = $this->db->zscount("group_feedback", "*", "total", "group_log_id = {$v['a_id']} ");
                       //$reslut[$k]['share_num'] = $this->db->zscount("group_share", "*", "total", "group_log_id = {$v['a_id']} ");
                   }
               }
                break;
            case 4:
                $sql = "SELECT id as group_type_id,icon,game_name FROM tab_group_type WHERE game_name LIKE '%{$search}%'";
                $reslut = $this->db->action($sql);
                foreach ($reslut as $k1=>$v1){
                    $reslut[$k1]['group_num'] = $this->db->zscount("user_group","*","total","game_id = {$v1['group_type_id']} ");
                    $reslut[$k1]['user_num'] = $this->db->zscount("group","*","total","group_type_id = {$v1['group_type_id']} ");
                    $reslut[$k1]['isfollow'] = is_follows($uid,$v1['group_type_id']);
                }
                break;
        }
        $this->ajax_return(0,$reslut);
    }
    //app换一换
    public function changeAction(){
        $len = $this->db->zscount("group_type");
        $start = rand(0,$len-8);
        $data = $this->db->action("SELECT game_name,game_id,icon,id as group_type_id FROM tab_group_type LIMIT {$start},8");
        $this->ajax_return(0,$data);
    }

    //搜索礼包
    public function giftbagAction(){
        $uid=get('id')?get('id'):0;
        if(empty($_GET['search'])){
            $this->ajax_return(102);
        }
        $search = preg_replace("/[%_\s]+/","",ltrim(addslashes($_GET['search'])));
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "5" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $gift= $this->db->action("select a.id as gift_id,a.giftbag_name,a.desribe,a.start_time,a.end_time,a.game_name,b.icon ,c.start_date,c.end_date 
from tab_giftbag a left join tab_game b  on a.game_id=b.id  left join tab_gift_position c on a.id=c.gift_id
where a.status=1 and b.game_status=1 and (a.giftbag_name like '%{$search}%' or a.game_name like '%{$search}%') order by a.id desc limit {$start},{$showPage}");
        foreach ($gift as $key => $value) {
            $gift[$key]['icon'] = $this->get_cover($value['icon'], 'path');
            $gift[$key]['novice_count'] = intval($this->db->zscount("gift_record", "*", "total", "gift_id={$value['gift_id']}"));
            $ji=$this->db->field("novice")->table("tab_giftbag")->where("id={$value['gift_id']}")->find();
            $at=explode(",",$ji['novice']);
            $gift[$key]['novice_total']=intval($gift[$key]['novice_count']+count($at));
            $isreceive=0;
            $novice='';
            $is_order=0;//不是预约礼包
            if(!empty($value['start_date']))
            {
                $gift[$key]['start_time']=$value['start_date'];
                $gift[$key]['end_time']=$value['end_date'];
                $is_order=1;//是预约礼包
            }
            if(!empty($uid))
            {
                $record=$this->db->field("*")->table("tab_gift_record")->where("gift_id = {$value['gift_id']} and user_id={$uid}")->find();
                if($record)
                {
                    $isreceive=1;
                    if($is_order==0)
                    {
                        $novice=$record['novice'];
                    }
                }
            }
            $gift[$key]['novice']=$novice;
            $gift[$key]['is_order']=$is_order;
            $gift[$key]['isreceive']=$isreceive;
        }
        $this->ajax_return(0,$gift);
    }

}