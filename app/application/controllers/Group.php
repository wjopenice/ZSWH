<?php

class GroupController extends BaseController{
    public $u_id = 0;
    public function init(){
        parent::init();
        if(empty($this->input['id'])){
            $this->ajax_return(1013);
        }else{
           $this->u_id = $this->input['id'];
        }
    }
    //圈子热门
    public function indexAction(){
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $banner =$this->db->action("select a.game_name,a.game_type_name,a.cover,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id 
      left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=1 order by a.create_time desc limit 5");
        foreach ($banner as $key=>$value)
        {
            $banner[$key]['icon']=$this->get_cover($value['icon'],'path');
            $banner[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $banner[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        $data['banner'] = $banner;
        $sql = "
        SELECT A.id as a_id,A.game_id as group_type_id,U.id,T.id as t_id,U.avatar,U.nickname,A.create_time,A.content,A.pic,A.share_num,T.icon,T.game_name,G.game_type_name,G.game_size
        FROM tab_user_group as A 
        INNER JOIN tab_group_type as T ON A.game_id = T.id
        INNER JOIN tab_user as U ON A.u_id = U.id
        INNER JOIN tab_game as G ON T.game_id = G.id
        ORDER BY A.id DESC 
        LIMIT {$start},{$showPage}
        ";
        $result = $this->db->action($sql);
        foreach ($result as $k=>$v){
            $result[$k]['create_time'] = get_time($v['create_time']);
            $result[$k]['isfollow'] = is_follows($this->u_id,$v['t_id']);
            $result[$k]['is_click']=0;
            $arrData=$this->db->field("*")->table("tab_group_click")->where("u_id={$this->u_id} and group_log_id={$v['a_id']}")->find();
            if(!empty($arrData))
            {
                $result[$k]['is_click']=1;
            }
            $result[$k]['click_num'] = $this->db->zscount("group_click","*","total","group_log_id = {$v['a_id']} ");
            $result[$k]['feedback_num'] = $this->db->zscount("group_feedback","*","total","group_log_id = {$v['a_id']} ");
            //$result[$k]['share_num'] = $this->db->zscount("group_share","*","total","group_log_id = {$v['a_id']} ");
            $result[$k]['group_num'] = $this->db->zscount("user_group","*","total","game_id = {$v['group_type_id']} ");
            $result[$k]['user_num'] = $this->db->zscount("group","*","total","group_type_id = {$v['group_type_id']} ");
        }
        $data['group'] = $result;
        $this->ajax_return(0,$data);
    }
    //圈子关注
    public function followlistAction(){
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $sql = "
            SELECT tgt.id,tg.group_type_id,tg.user_id,tgt.icon,tgt.game_name
            FROM tab_group as tg 
            INNER JOIN tab_group_type as tgt ON tg.group_type_id = tgt.id 
            WHERE tg.user_id = {$this->u_id}";
        $follow=$this->db->action($sql);
        if(empty($follow)){
            $data['follow'] = [];
            $data['group'] = [];
            $this->ajax_return(0,$data);
        }else{
            $data['follow'] = $follow;
        }
        $indata = [];
        foreach ($follow as $key=>$value){
            $indata[] = $value['id'];
        }
        $str = implode(",",$indata);
        $group_type_id = isset($this->input["group_type_id"]) ? $this->input["group_type_id"] : $str;
        $sql = "
        SELECT A.id as a_id,U.id,U.avatar,U.nickname,A.create_time,A.content,A.pic,A.game_id,A.share_num
        FROM tab_user_group as A 
        INNER JOIN tab_user as U ON A.u_id = U.id
        WHERE A.game_id IN ($group_type_id)
        ORDER BY A.id DESC 
        LIMIT {$start},{$showPage}
        ";
        $result = $this->db->action($sql);
        foreach ($result as $k=>$v){
            $result[$k]['create_time'] = get_time($v['create_time']);
            $result[$k]['is_click']=0;
            $arrData=$this->db->field("*")->table("tab_group_click")->where("u_id={$this->u_id} and group_log_id={$v['a_id']}")->find();
            if(!empty($arrData))
            {
                $result[$k]['is_click']=1;
            }
            $result[$k]['click_num'] = $this->db->zscount("group_click","*","total","group_log_id = {$v['a_id']} ");
            $result[$k]['feedback_num'] = $this->db->zscount("group_feedback","*","total","group_log_id = {$v['a_id']} ");
           // $result[$k]['share_num'] = $this->db->zscount("group_share","*","total","group_log_id = {$v['a_id']} ");
        }

        if(!empty($result)){
            $data['group'] = $result;
        }else{
            $data['group'] = [];
        }
        $this->ajax_return(0,$data);
    }
    //圈子广场
    public function circlesquareAction(){
          //热门圈子
          $len = $this->db->zscount("group_type");
          $strart = rand(0,$len-4);
          $hot = $this->db->action("SELECT id as group_type_id,icon,game_name FROM tab_group_type LIMIT {$strart},4 ");
          foreach ($hot as $k1=>$v1){
              $hot[$k1]['group_num'] = $this->db->zscount("user_group","*","total","game_id = {$v1['group_type_id']} ");
              $hot[$k1]['user_num'] = $this->db->zscount("group","*","total","group_type_id = {$v1['group_type_id']} ");
          }
          $resultdb['hot'] =$hot;
          //推荐圈子
          $strart1 = rand(0,$len-6);
          $recommend = $this->db->action("SELECT id as group_type_id,icon,game_name FROM tab_group_type LIMIT {$strart1},6 ");
          foreach ($recommend as $k1=>$v1){
             $recommend[$k1]['group_num'] = $this->db->zscount("user_group","*","total","game_id = {$v1['group_type_id']} ");
              $recommend[$k1]['user_num'] = $this->db->zscount("group","*","total","group_type_id = {$v1['group_type_id']} ");
          }
          $resultdb['recommend'] =$recommend;
          $this->ajax_return(0,$resultdb);
    }
    //我的圈子
    public function mycircleAction(){
        $sql = "
            SELECT tgt.id,tg.group_type_id,tg.user_id,tgt.icon,tgt.game_name,tgt.game_id 
            FROM tab_group as tg 
            INNER JOIN tab_group_type as tgt ON tg.group_type_id = tgt.id 
            WHERE tg.user_id = {$this->u_id}";
        $result=$this->db->action($sql);
        foreach ($result as $k1=>$v1){
            $result[$k1]['group_num'] = $this->db->zscount("user_group","*","total","game_id = {$v1['group_type_id']} ");
            $result[$k1]['user_num'] = $this->db->zscount("group","*","total","group_type_id = {$v1['group_type_id']} ");
        }
        if($result){
            $this->ajax_return(0,$result);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //圈子资讯
    public function newsAction(){
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
       $group_type_id = $this->input['group_type_id'];
        $result=$this->db->field("id as group_type_id,icon,game_name,game_id")->table("tab_group_type")->where("id = {$group_type_id}")->find();
        $news=$this->db->field("id,title,create_time,pic,click,type")->table("tab_news")->where("game_id = {$result['game_id']}")->limit($start,$showPage)->select();
        foreach ($news as $key=>$value)
        {
            if(empty($value['cate_title']))
            {
                $news[$key]['cate_title']="";
            }
            $news[$key]['create_time'] = get_time($value['create_time']);
            $is_click=0;
            if(!empty($this->u_id))
            {
                $arrData = $this->db->field("*")->table("tab_news_click")->where(" u_id = {$this->u_id} and n_id = {$value['id']} ")->find();
                if(!empty($arrData))
                {
                    $is_click=1;
                }else{
                    $is_click=0;
                }
            }
            $news[$key]['is_click']=$is_click;
        }
        $this->ajax_return(0,$news);
    }
    //圈子礼包
    public function giftAction(){
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $group_type_id = $this->input['group_type_id'];
        $result=$this->db->field("id as group_type_id,icon,game_name,game_id")->table("tab_group_type")->where("id = {$group_type_id}")->find();
        $giftbag=$this->db->action("select a.id as gift_id,a.game_id,a.game_name,a.giftbag_name,a.desribe,a.start_time,a.end_time,b.icon from tab_giftbag a left join tab_game b  on 
a.game_id=b.id where a.status=1 and a.game_id={$result['game_id']} limit {$start},{$showPage}");
        foreach($giftbag as $key=>$value)
        {
            $giftbag[$key]['icon']=$this->get_cover($value['icon'],'path');
            $giftbag[$key]['novice_count']=intval($this->db->zscount("gift_record","*","total","gift_id={$value['gift_id']}"));
            $ji=$this->db->field("novice")->table("tab_giftbag")->where("id={$value['gift_id']}")->find();
            $at=explode(",",$ji['novice']);
            $giftbag[$key]['novice_total']=intval($giftbag[$key]['novice_count']+count($at));
            $isreceive="0";
            if(!empty($this->u_id))
            {
                $record=$this->db->field("*")->table("tab_gift_record")->where("gift_id = {$value['gift_id']} and user_id={$this->u_id}")->find();
                if($record)
                {
                    $isreceive="1";
                }
            }
            $giftbag[$key]['isreceive']=$isreceive;
        }
        $this->ajax_return(0,$giftbag);
    }
    //圈子（取消/关注）
    public function savefollowAction(){
        if(isset($this->input['game_id']) && !isset($this->input['group_type_id'])){
            $group_type_id = get_group_type_id($this->input['game_id']);
        }else{
            $group_type_id = $this->input['group_type_id'];
        }
        $result=$this->db->field("*")->table("tab_group")->where("user_id = {$this->u_id} and group_type_id = {$group_type_id}")->find();
        if(!empty($result)){
            $bool = $this->db->action($this->db->deleteSql("group","id = {$result['id']}"));
        }else{
            $data['user_id'] = $this->input['id'];
            $data['group_type_id'] = $group_type_id;
            $bool = $this->db->action($this->db->insertSql("group",$data));
        }
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //圈子游戏
    public function gameAction(){
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        /*$group_type_id = $this->input['group_type_id'];
        $result=$this->db->field("id as group_type_id,icon,game_name,game_id")->table("tab_group_type")->where("id = {$group_type_id}")->find();
        $result['game_type_name'] = get_game_type_name($result['game_id']);
        $result['isfollow'] = is_follows($this->u_id,$group_type_id);
        $result['user_num'] = 0;
        $result['group_num'] = 0;*/
        $group_type_id = $this->input['group_type_id'];
        $sql = "
        SELECT A.id as a_id,U.id,U.avatar,U.nickname,A.create_time,A.content,A.pic,A.share_num
        FROM tab_user_group as A 
        INNER JOIN tab_user as U ON A.u_id = U.id
        WHERE A.game_id = {$group_type_id}
        ORDER BY A.id DESC 
        LIMIT {$start},{$showPage}
        ";
        $groupData = $this->db->action($sql);
        foreach ($groupData as $k=>$v){
            $groupData[$k]['create_time']=get_time($v['create_time']);
            $groupData[$k]['is_click']=0;
            $arrData=$this->db->field("*")->table("tab_group_click")->where("u_id={$this->u_id} and group_log_id={$v['a_id']}")->find();
            if(!empty($arrData))
            {
                $groupData[$k]['is_click']=1;
            }
            $groupData[$k]['click_num'] = $this->db->zscount("group_click","*","total","group_log_id = {$v['a_id']} ");
            $groupData[$k]['feedback_num'] = $this->db->zscount("group_feedback","*","total","group_log_id = {$v['a_id']} ");
            //$groupData[$k]['share_num'] = $this->db->zscount("group_share","*","total","group_log_id = {$v['a_id']} ");
        }

        $this->ajax_return(0,$groupData);
    }
    //圈子
    public function groupcontentAction(){
        $group_type_id = $this->input['group_type_id'];
        $result=$this->db->field("id as group_type_id,icon,game_name,game_id")->table("tab_group_type")->where("id = {$group_type_id}")->find();
        $result['game_type_name'] = get_game_type_name($result['game_id']);
        $result['isfollow'] = is_follows($this->u_id,$group_type_id);
        $result['group_num'] = $this->db->zscount("user_group","*","total","game_id = {$group_type_id} ");
        $result['user_num'] = $this->db->zscount("group","*","total","group_type_id = {$group_type_id} ");
        $this->ajax_return(0,$result);
    }
    //圈子正文
    public function contentAction(){
       /* $this->input=$_GET;
        $this->u_id=187492;*/
        $a_id = $this->input['a_id']; 
        $group_t1=$this->db->field("pic,content,create_time,u_id")->table("tab_user_group")->where("id = {$a_id}")->find();
        $group_u1=$this->db->field("id,avatar,nickname,register_time")->table("tab_user")->where("id = {$group_t1['u_id']}")->find();
        $group_t1['create_time']=get_time($group_t1['create_time']);
        $group_c1['click_num']=$this->db->zscount("group_click","*","total","group_log_id={$a_id} ");
        $is_click=0;
        $click=$this->db->field("*")->table("tab_group_click")->where("u_id = {$this->u_id} and  group_log_id={$a_id}")->find();
        if(!empty($click))
        {
            $is_click=1;
        }
        $group_c1['is_click']=$is_click;
        $result = array_merge($group_u1,$group_t1,$group_c1);

        $this->ajax_return(0,$result);
    }
    //朋友圈子
    public function friendcontentAction(){
        $pf_id = $this->input['pf_id'];
        $data=$this->db->field("id,avatar,nickname,u_sign")->table("tab_user")->where("id = {$pf_id}")->find();
        $res = $this->db->field("*")->table("tab_group")->where("user_id = {$pf_id}")->select();
        $data['user_num'] = $this->db->zscount("user_follow","*","total","pf_id = {$pf_id} ");
        $data['group_type_num'] = count($res);
        $data['is_user_follow'] = is_user_follows($pf_id,$this->u_id);
        $this->ajax_return(0,$data);
    }
    public function circlefriendsAction(){
        $pf_id = $this->input['pf_id'];
        $res = $this->db->field("*")->table("tab_group")->where("user_id = {$pf_id}")->select();
        $is_user_follow = is_user_follows($pf_id,$this->u_id);
         $indata = [];
         foreach ($res as $key=>$value){
             $indata[] = $value['group_type_id'];
         }
         $str = implode(",",$indata);
         $showpage = ($is_user_follow) ? 3 : 10;
         if(!empty($str)) {
             $sql = "
         SELECT A.id as a_id,U.id,U.avatar,U.nickname,U.account,A.create_time,A.content,A.pic,A.game_id,A.share_num
         FROM tab_user_group as A
         INNER JOIN tab_user as U ON A.u_id = U.id
         WHERE A.game_id IN ($str) AND U.id = {$pf_id}
         ORDER BY A.id DESC
         LIMIT 0,{$showpage}
         ";
             $result = $this->db->action($sql);
             foreach ($result as $k => $v) {
                 $result[$k]['content'] = htmlspecialchars_decode($v['content']);
                 $result[$k]['create_time'] = get_time($v['create_time']);
                 $result[$k]['is_click'] = 0;
                 $arrData = $this->db->field("*")->table("tab_group_click")->where("u_id={$this->u_id} and group_log_id={$v['a_id']}")->find();
                 if (!empty($arrData)) {
                     $result[$k]['is_click'] = 1;
                 }
                 $result[$k]['click_num'] = $this->db->zscount("group_click", "*", "total", "group_log_id = {$v['a_id']} ");
                 $result[$k]['feedback_num'] = $this->db->zscount("group_feedback", "*", "total", "group_log_id = {$v['a_id']} ");
                // $result[$k]['share_num'] = $this->db->zscount("group_share", "*", "total", "group_log_id = {$v['a_id']} ");
             }
         }
         else{
             $result=[];
         }
        $this->ajax_return(0,$result);
    }
    //朋友圈子游戏下载
    public function downloadAction(){
        $pf_id = $this->input['pf_id'];
        //$res = $this->db->field("*")->table("tab_group")->where("user_id = {$pf_id}")->select();
        $res=$this->db->action("select a.game_id from tab_group_type a inner join tab_group b on a.id=b.group_type_id where b.user_id={$pf_id}");
        $is_user_follow = is_user_follows($pf_id,$this->u_id);
        $indata = [];
        foreach ($res as $key=>$value){
            $indata[] = $value['game_id'];
        }
        $str = implode(",",$indata);
        $showpage = ($is_user_follow == 0) ? 3 : 10;
        if (!empty($str)) {
            $sql = "SELECT a.game_name,a.game_type_name,a.icon,a.game_address,a.and_dow_address,a.features,a.introduction,a.version,a.id as game_id,
 a.version_num,b.apk_pck_name FROM tab_game a left join tab_game_set b on a.id=b.game_id WHERE a.id IN ({$str}) and a.game_status=1 LIMIT 0,{$showpage}";
            $result = $this->db->action($sql);
            foreach ($result as $k => $v) {
                $result[$k]['icon'] = $this->get_cover($result[$k]['icon'], 'path');
                if (!empty($result[$k]['and_dow_address'])) {
                    $result[$k]['and_dow_address'] = ZSWH . substr($result[$k]['and_dow_address'], 1);
                }
            }
        }
        else{
            $result=[];
        }
        $this->ajax_return(0,$result);
    }
    //朋友圈子动态
    public function frienddynamicAction(){
        $pf_id = $this->input['pf_id'];
        $is_user_follow = is_user_follows($pf_id,$this->u_id);
        $showpage = ($is_user_follow == 0) ? 3 : 10;
        $month=$this->db->action("SELECT from_unixtime(f.create_time,'%Y-%m') as feedback_time
    FROM tab_group_reply as r
    INNER JOIN tab_group_feedback as f
    ON f.id = r.feedback_id INNER JOIN tab_user u on f.u_id=u.id
    WHERE r.u_id = {$pf_id}  group by from_unixtime(f.create_time,'%Y-%m') order by f.create_time desc");
       /* $currentPage = empty($this->input["page"])?"1":$this->input["page"];
        $showPage = empty($this->input["showpage"])?"10":$this->input["showpage"];
        $start =  ($currentPage-1)*$showPage;*/
        foreach($month as $k=>$v)
        {
            $sql =
                "SELECT f.content,f.create_time,f.id as feedback_id,f.u_id  as id,u.nickname
    FROM tab_group_reply as r
    INNER JOIN tab_group_feedback as f
    ON f.id = r.feedback_id INNER JOIN tab_user u on f.u_id=u.id
    WHERE r.u_id = {$pf_id}  and from_unixtime(f.create_time,'%Y-%m')='{$v['feedback_time']}' order by f.create_time desc 
    LIMIT 0,{$showpage}";
            $feedbackdata=$this->db->action($sql);
            foreach ($feedbackdata as $key=>$value)
            {
                $feedbackdata[$key]['create_time']=date("Y-m-d",$value['create_time']);
                $feedback=$this->db->action("SELECT a.content ,a.create_time as reply_time,a.id as reply_id,b.nickname,b.id FROM  tab_group_reply a left join tab_user b on a.u_id=b.id
 where a.feedback_id={$value['feedback_id']} and a.u_id={$pf_id}
 order by a.create_time desc LIMIT 0,1");
                $feedback[0]['reply_time']=date("Y-m-d",$feedback[0]['reply_time']);
                $feedbackdata[$key]['reply'] =$feedback[0];
                $feedbackdata[$key]['reply']['reply_user']=$value['nickname']?$value['nickname']:$value['id'];
                $floor=$this->db->action("SELECT a.content ,a.create_time as reply_time,b.nickname,b.id FROM  tab_group_reply a left join tab_user b on a.u_id=b.id
 where a.feedback_id={$value['feedback_id']} and a.floor_id={$feedback[0]['reply_id']}
 order by a.create_time desc LIMIT 0,1");
                if(!empty($floor[0])) {
                    $floor[0]['reply_time']=date("Y-m-d",$floor[0]['reply_time']);
                    $feedbackdata[$key]['floor'] = $floor[0];
                    $feedbackdata[$key]['floor']['reply_user'] = $feedback[0]['nickname'] ? $feedback[0]['nickname'] : $feedback[0]['id'];
                }
                else{
                    $feedbackdata[$key]['floor'] = [];
                }
            }
            $month[$k]['feedbackdata']=$feedbackdata;
        }

        $this->ajax_return(0,$month);
    }
    //圈子点赞
    public function clickgroupAction()
    {
         $id = $this->input['a_id'];
         //获取帖子
        $usergroup = $this->db->field("*")->table("tab_user_group")->where("game_id={$id}")->find();
        $user = $this->db->field("nickname,avatar")->table("tab_user")->where("id={$this->u_id}")->find();
         if(!empty($id)){
            $arrData = $this->db->action("select * from tab_group_click where u_id={$this->u_id} and group_log_id={$id}");
            if(!empty($arrData)){
                //取消点赞
                $bool = $this->db->action($this->db->deleteSql("group_click"," u_id = {$this->u_id} and group_log_id = {$id} "));
                if($bool){

                    $this->ajax_return(0);
                }else{
                    $this->ajax_return(500);
                }
            }else{
                //点赞
                $data2['u_id'] =$this->u_id;
                $data2['group_log_id'] = $id;
                $bool = $this->db->action( $this->db->insertSql("group_click",$data2));
                if($bool){
                    if($this->u_id!=$usergroup['u_id'])
                    {
                        if (!empty($user['avatar'])) {
                            $user['avatar'] = WZAPP . $user['avatar'];
                        } else {
                            $user['avatar'] = "";
                        }
                          $this->jpapi(0,$usergroup['u_id'],$this->u_id,$user['nickname'],$user['avatar'],$usergroup['content']);
                      //  $this->ajax_return(0);
                    }
                    else{
                        $this->ajax_return(0);
                    }
                }else{
                    $this->ajax_return(500);
                }
            }
        }else{
            $this->ajax_return(104);
        }
    }
    public function testAction(){
        $arr=["type"=>"1","uid"=>"164105","pid"=>"164096","pnickname"=>"多肉APP","pavatar"=>"","data"=>"大家好***"];
        $this->forward("index","message","send",["arr"=>$arr]);
    }
}
