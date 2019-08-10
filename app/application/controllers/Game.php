<?php

class GameController extends BaseController  {
    //游戏详情首页
    public function indexAction(){
        $game_id=get('game_id');
        $game=$this->db->action("select a.game_name,a.game_type_name,a.game_type_id,a.cover,a.icon,a.introduction,a.id,a.game_address,a.create_time,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name,a.screenshot,d.id as group_type_id from tab_game a left join tab_game_set b 
      on a.id=b.game_id left join tab_group_type d on a.id=d.game_id left join tab_game_source c on a.id=c.game_id   where a.id={$game_id}");
        if(empty($game))
        {
            $this->ajax_return(1205);
        }
        if(empty($game[0]['group_type_id']))
        {
            $game[0]['group_type_id']="";
        }
        $screen=explode(",",$game[0]['screenshot']);
        foreach($screen as $value)
        {
            $screenshot[]=$this->get_cover($value,'path');
        }
        if(!empty( $game[0]['and_dow_address'])) {
            $game[0]['and_dow_address'] = ZSWH . substr( $game[0]['and_dow_address'], 1);
        }
        $game[0]['icon']=$this->get_cover($game[0]['icon'],'path');
        $game[0]['cover']=$this->get_cover($game[0]['cover'],'path');
        $game[0]['screenshot']=$screenshot;
        //热门
      $hot=$this->db->action("select a.game_name,a.game_type_name,a.icon,a.game_address,a.and_dow_address, a.version,a.version_num,b.apk_pck_name,a.id,a.introduction,a.features,
       c.file_size as game_size from tab_game a left join tab_game_set b on a.id=b.game_id  left join tab_game_source c on a.id=c.game_id  where a.recommend_status=2 and a.game_status=1 and a.id !={$game_id} and a.game_type_id={$game[0]['game_type_id']} 
         order by a.sort desc limit 8");
        foreach ($hot as $key=> $value)
        {
            if(!empty($value['and_dow_address'])) {
                $hot[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
            $hot[$key]['icon']=$this->get_cover($value['icon'],'path');
        }
        //推荐
        $recommend=$this->db->action("select a.game_name,a.game_type_name,a.icon,a.game_address,a.and_dow_address, a.version,a.version_num,b.apk_pck_name,a.id,a.introduction,a.features,c.file_size as game_size
        from tab_game a left join tab_game_set b on a.id=b.game_id  left join tab_game_source c on a.id=c.game_id  where a.game_type_id={$game[0]['game_type_id']} and a.game_status=1 and a.id !={$game_id} order by a.sort desc limit 5");
        foreach ($recommend as $key=> $value)
        {
            if(!empty($value['and_dow_address'])) {
                $recommend[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
            $recommend[$key]['icon']=$this->get_cover($value['icon'],'path');
        }
        $data['game']=$game[0];
        $data['hot']=$hot;
        //猜你喜欢
        $data['like']=$recommend;
        $this->ajax_return(0,$data);
    }
    //游戏详情资讯
    public function newsAction(){
        $game_id=get('game_id');
        $user_id=get('id')?get('id'):0;
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "6" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $news=$this->db->action("select a.id,a.title,a.create_time,a.pic,a.click,b.game_type_name as cate_title from tab_news a left join tab_game b on a.game_id=b.id
        where a.game_id={$game_id}    order by a.view desc limit {$start},{$showPage}");
        foreach ($news as $key=>$value)
        {
            if(empty($value['cate_title']))
            {
                $news[$key]['cate_title']="";
            }
            $news[$key]['create_time'] = get_time($value['create_time']);
            $is_click=0;
            if(!empty($user_id))
            {
                $arrData = $this->db->field("*")->table("tab_news_click")->where(" u_id = {$user_id} and n_id = {$value['id']} ")->find();
                if(!empty($arrData))
                {
                    $is_click=1;
                }
            }
            $news[$key]['is_click']=$is_click;
        }
        $this->ajax_return(0,$news);
    }
    //游戏详情礼包
    public function giftbagAction(){
        $game_id=get('game_id');
        $user_id=get('id')?get('id'):0;
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "6" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $giftbag=$this->db->action("select a.id as gift_id,a.game_id,a.game_name,a.giftbag_name,a.desribe,a.start_time,a.end_time,c.start_date,c.end_date,
b.icon from tab_giftbag a left join tab_game b  on 
a.game_id=b.id left join tab_gift_position c on a.id=c.gift_id where a.status=1 and a.game_id={$game_id} limit {$start},{$showPage}");
        foreach($giftbag as $key=>$value)
        {
            $giftbag[$key]['icon']=$this->get_cover($value['icon'],'path');
            $giftbag[$key]['novice_count']=intval($this->db->zscount("gift_record","*","total","gift_id={$value['gift_id']}"));
            $ji=$this->db->field("novice")->table("tab_giftbag")->where("id={$value['gift_id']}")->find();
            $at=explode(",",$ji['novice']);
            $giftbag[$key]['novice_total']=intval($giftbag[$key]['novice_count']+count($at));
            $isreceive=0;
            $novice='';
            $is_order=0;//不是预约礼包
            if(!empty($value['start_date']))
            {
                $giftbag[$key]['start_time']=$value['start_date'];
                $giftbag[$key]['end_time']=$value['end_date'];
                $is_order=1;//是预约礼包
            }
            if(!empty($user_id))
            {
                $record=$this->db->field("*")->table("tab_gift_record")->where("gift_id = {$value['gift_id']} and user_id={$user_id}")->find();
                if($record)
                {
                    $isreceive=1;
                }
                if($is_order==0)
                {
                    $novice=$record['novice'];
                }
            }
            $giftbag[$key]['isreceive']=$isreceive;
            $giftbag[$key]['novice']=$novice;
            $giftbag[$key]['is_order']=$is_order;
        }
        $gameData = $this->db->field("*")->table("tab_game")->where("id = {$game_id}")->find();
      //游戏推荐
        $game=$this->db->action("select a.game_name,a.game_type_name,a.icon,a.id,a.game_address,a.version,c.file_size as game_size,a.and_dow_address,a.version_num,
b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id  left join tab_game_source c on a.id=c.game_id  where  a.game_type_id={$gameData['game_type_id']} and  a.game_status=1 and a.recommend_status=2 and a.id!={$game_id} order by a.sort desc limit 8");
        foreach ($game as $key=>$value)
        {
            $game[$key]['icon']=$this->get_cover($value['icon'],'path');
            if(!empty($value['and_dow_address'])) {
                $game[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        $data['gift_bag']=$giftbag;
        $data['hot']=$game;
        $this->ajax_return(0,$data);
    }
    
    //猜你喜欢
    public function likeAction(){
       $game_type=$this->db->action("select id from tab_game_type where status=1");
        $indata = [];
        if(!empty($_GET['type'])) {
             $gettype=json_decode($_GET['type']);
            foreach ($gettype as $value)
            {
                $type_name = $this->db->field("id")->table("tab_game_type")->where("type_name = '{$value}'")->find();
                $indata[] = $type_name['id'];
            }
        }
        else{
            foreach ($game_type as $key=>$value){
                $indata[] = $value['id'];
            }
        }
        $game_type_id = implode(",",$indata);
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "6" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;

        $game=$this->db->action(" select a.id, a.game_name,a.game_type_name,a.icon,a.version,c.file_size as game_size ,a.and_dow_address,a.version_num,b.apk_pck_name,a.game_address,a.introduction,a.features,a.cover
  from tab_game a left join tab_game_set b on a.id=b.game_id  left join tab_game_source c on a.id=c.game_id where a.game_type_id in  ($game_type_id)
  and a.game_status=1 order by a.sort desc  limit {$start},{$showPage}");
        foreach ($game as $key=>$value)
        {
            $game[$key]['icon']=$this->get_cover($value['icon'],'path');
            $game[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $game[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        $this->ajax_return(0,$game);

    }

    //游戏更新
    public function updateAction(){
        $reqdata = json_decode(file_get_contents("php://input"),true);
        $packages = json_decode($reqdata['packages'],true);
        if($packages){
             $arr = [];
             foreach ($packages as $key=>$value){
                 $sql = "select a.id,a.game_name,a.game_type_name,a.cover,a.icon,a.introduction,a.features,a.version,c.file_size as game_size,a.and_dow_address,a.game_address,a.version_num,b.apk_pck_name
  from tab_game a left join tab_game_set b on a.id=b.game_id  left join tab_game_source c on a.id=c.game_id where b.apk_pck_name = '{$value['package_name']}' ";
                 $arrData = $this->db->action($sql);

                 foreach ($arrData as $k1=>$v1)
                 {
                     $arrData[$k1]['cover']=$this->get_cover($v1['cover'],'path');
                     $arrData[$k1]['icon']=$this->get_cover($v1['icon'],'path');
                     if(!empty($v1['and_dow_address'])) {
                         $arrData[$k1]['and_dow_address'] = ZSWH . substr($v1['and_dow_address'], 1);
                     }
                 }

                 if(!empty($arrData[0])){
                     if($arrData[0]['version_num'] > $value['version_code']){
                         $arr[] = $arrData[0];
                     }
                 }
             }
             $this->ajax_return(0,$arr);
        }else{
            $this->ajax_return(102);
        }
    }

    //下载游戏
    public function downloadgameAction(){
        $user_id=$_GET['id'];
        $apk_pck_name=$_GET['apk_pck_name'];
        $game = $this->db->field("game_id")->table("tab_game_set")->where("apk_pck_name = '{$apk_pck_name}'")->find();
        $data2['user_id'] =$user_id;
        $data2['game_id'] = $game['game_id'];
        $bool = $this->db->action( $this->db->insertSql("download_game",$data2));
        if($bool)
        {
            $task=$this->db->field("game_id,game_name,is_member,point,v1,v2,v3,v4,v5,v6,v7,v8")->table("tab_download_point")->where("game_id = {$game['game_id']} ")->find();
            $point=$task['point'];
            $user=$this->db->field("vip_level")->table("tab_user")->where("id = {$user_id} ")->find();
            $vip_level=$user['vip_level'];
            if(!empty($task['is_member']))
            {
                switch ($vip_level){
                    case 1:
                        $point=$point+$task['v1'];
                        break;
                    case 2:
                        $point=$point+$task['v2'];
                        break;
                    case 3:
                        $point=$point+$task['v3'];
                        break;
                    case 4:
                        $point=$point+$task['v4'];
                        break;
                    case 5:
                        $point=$point+$task['v5'];
                        break;
                    case 6:
                        $point=$point+$task['v6'];
                        break;
                    case 7:
                        $point=$point+$task['v7'];
                        break;
                    case 8:
                        $point=$point+$task['v8'];
                        break;
                }
            }
            $this->game_bp($user_id, "+".$point."积分", time(), "下载".$task['game_name'], $point, 2,0,$task['game_id']);
            $this->ajax_return(0);
        }
        else{
            $this->ajax_return(500);
        }
    }
}
