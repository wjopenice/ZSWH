<?php

class NativeController extends BaseController  {

    //首页
    public function indexAction()
    {
        $uid=get('id')?get('id'):0;
        //推荐游戏
        $recommend =$this->db->action("select a.game_name,a.game_type_name,a.cover,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id 
      left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=1 order by a.sort desc limit 4");
        foreach ($recommend as $key=>$value)
        {
            $recommend[$key]['icon']=$this->get_cover($value['icon'],'path');
            $recommend[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $recommend[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        //热门游戏
        $hot =$this->db->action("select a.game_name,a.game_type_name,a.cover,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,a.and_dow_address,a.version_num,b.apk_pck_name,c.file_size as game_size from tab_game a left join tab_game_set b on a.id=b.game_id 
       left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=2 order by a.sort desc limit 2");
        foreach ($hot as $key=>$value)
        {
            $hot[$key]['icon']=$this->get_cover($value['icon'],'path');
            $hot[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $hot[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }

        }
        //新游推荐
        $xin = $this->db->action("select a.game_name,a.game_type_name,a.cover,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id 
       left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=3 order by a.sort desc limit 3");
        foreach ($xin as $key=>$value)
        {
            $xin[$key]['icon']=$this->get_cover($value['icon'],'path');
            $xin[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $xin[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        //bt游戏
        $bt = $this->db->action("select a.game_name,a.game_type_name,a.cover,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id 
       left join tab_game_source c on a.id=c.game_id where a.game_type_id=17 
   order by a.sort desc limit 2");
        foreach ($bt as $key=>$value)
        {
            $bt[$key]['icon']=$this->get_cover($value['icon'],'path');
            $bt[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $bt[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
      //游戏礼包
        $giftbag=$this->db->action("SELECT tab_giftbag.id as gift_id,tab_giftbag.game_id,tab_giftbag.game_name,tab_giftbag.start_time,tab_giftbag.end_time,
tab_giftbag.giftbag_name,tab_giftbag.desribe,tab_game.icon,tab_gift_position.start_date,tab_gift_position.end_date FROM `tab_giftbag` 
INNER JOIN tab_game on tab_giftbag.game_id = tab_game.id inner join tab_gift_position  on tab_giftbag.id=tab_gift_position.gift_id
WHERE `game_status` = 1 and tab_giftbag.status=1  and position like '%1%' GROUP BY tab_giftbag.game_id ORDER BY tab_giftbag.id desc LIMIT 3 ");
        foreach ($giftbag as $key=>$value)
        {
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
            $giftbag[$key]['novice']=$novice;
            $giftbag[$key]['is_order']=$is_order;
            $giftbag[$key]['isreceive']=$isreceive;
            $giftbag[$key]['icon']=$this->get_cover($value['icon'],'path');
        }
        $newtype=$this->db->field("*")->table("tab_news_type")->select();
        foreach ($newtype as $key=>$value)
        {
            if(!empty($value['url'])) {
                $newtype[$key]['url'] = "http://app.zhishengwh.com".$value['url'];
            }
        }
        $data['recommend']=$recommend;
        $data['hot']=$hot;
        $data['xin']=$xin;
        $data['bt']=$bt;
        $data['news_type']=$newtype;
        $data['gift_bag']=$giftbag;
        $this->ajax_return(0, $data);

    }
    //评论点赞
    public function feedbackclickAction(){
        $id = $this->input['feedback_id'];
        $type=$this->input['type'];
        $uid=$this->input['id']?$this->input['id']:0;
        if(empty($uid))
        {
            $this->ajax_return(1013);
        }
        if($type==1)
        {
            if(!empty($id)){
                $arrData = $this->db->field("*")->table("tab_news_feedback_click")->where(" u_id = {$uid} and feedback_id = {$id} ")->find();
                if(!empty($arrData)){
                    //取消点赞
                    $bool = $this->db->action($this->db->deleteSql("news_feedback_click"," u_id = {$uid} and feedback_id = {$id} "));
                    if($bool){
                        $this->ajax_return(0);
                    }else{
                        $this->ajax_return(500);
                    }
                }else{
                    //点赞
                    $data2['u_id'] =$uid;
                    $data2['feedback_id'] = $id;
                    $data2['create_time']=time();
                    $result = $this->db->field("*")->table("tab_news_feedback")->where("id={$id}")->find();
                    if($uid!=$result['u_id'])
                    {
                        $data2['user_id']=$result['u_id'];
                    }
                    $bool = $this->db->action( $this->db->insertSql("news_feedback_click",$data2));
                    if($bool){
                        $user = $this->db->field("nickname,avatar")->table("tab_user")->where("id={$uid}")->find();
                        if($uid!=$result['u_id'])
                        {
                            if (!empty($user['avatar'])) {
                                $user['avatar'] = WZAPP . $user['avatar'];
                            } else {
                                $user['avatar'] = "";
                            }
                            $this->jpapi(3,$result['u_id'],$uid,$user['nickname'],$user['avatar'],$result['content']);

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
        elseif ($type==2)
        {
            if(!empty($id)){
                $arrData = $this->db->field("*")->table("tab_game_feedback_click")->where(" u_id = {$uid} and feedback_id = {$id} ")->find();
                if(!empty($arrData)){
                    //取消点赞
                    $bool = $this->db->action($this->db->deleteSql("game_feedback_click"," u_id = {$uid} and feedback_id = {$id} "));
                    if($bool){
                        $this->ajax_return(0);
                    }else{
                        $this->ajax_return(500);
                    }
                }else{
                    //点赞
                    $data2['u_id'] =$uid;
                    $data2['feedback_id'] = $id;
                    $data2['create_time']=time();
                    $result = $this->db->field("*")->table("tab_game_feedback")->where("id={$id}")->find();
                    if($uid!=$result['u_id'])
                    {
                        $data2['user_id']=$result['u_id'];
                    }
                    $bool = $this->db->action( $this->db->insertSql("game_feedback_click",$data2));
                    if($bool){
                        $user = $this->db->field("nickname,avatar")->table("tab_user")->where("id={$uid}")->find();
                        if($uid!=$result['u_id'])
                        {
                            if (!empty($user['avatar'])) {
                                $user['avatar'] = WZAPP . $user['avatar'];
                            } else {
                                $user['avatar'] = "";
                            }
                            $this->jpapi(3,$result['u_id'],$uid,$user['nickname'],$user['avatar'],$result['content']);

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
        elseif ($type==3)
        {
            if(!empty($id)){
                $arrData = $this->db->field("*")->table("tab_group_feedback_click")->where(" u_id = {$uid} and feedback_id = {$id} ")->find();
                if(!empty($arrData)){
                    //取消点赞
                    $bool = $this->db->action($this->db->deleteSql("group_feedback_click"," u_id = {$uid} and feedback_id = {$id} "));
                    if($bool){
                        $this->ajax_return(0);
                    }else{
                        $this->ajax_return(500);
                    }
                }else{
                    //点赞
                    $data2['u_id'] =$uid;
                    $data2['feedback_id'] = $id;
                    $data2['create_time']=time();
                    $result = $this->db->field("*")->table("tab_group_feedback")->where("id={$id}")->find();
                    if($uid!=$result['u_id'])
                    {
                        $data2['user_id']=$result['u_id'];
                    }
                    $bool = $this->db->action( $this->db->insertSql("group_feedback_click",$data2));
                    if($bool){
                        $user = $this->db->field("nickname,avatar")->table("tab_user")->where("id={$uid}")->find();
                        if($uid!=$result['u_id'])
                        {
                            if (!empty($user['avatar'])) {
                                $user['avatar'] = WZAPP . $user['avatar'];
                            } else {
                                $user['avatar'] = "";
                            }
                            $this->jpapi(3,$result['u_id'],$uid,$user['nickname'],$user['avatar'],$result['content']);

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
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //huifu详情展示
    public function replyAction(){
        $feedback_id = $this->input['feedback_id'];
         $type=$this->input['type'];
        $currentPage = empty($this->input["page"])?"1":$this->input["page"];
        $showPage = empty($this->input["showpage"])?"10":$this->input["showpage"];
        $start =  ($currentPage-1)*$showPage;
        if($type==1) {
            $fsql = "select a.id as feedback_id,a.u_id as id,a.news_id,a.content,a.create_time,b.nickname ,b.avatar from tab_news_feedback 
 a left join tab_user b on a.u_id=b.id WHERE a.id = {$feedback_id} ";
            $feedback = $this->db->action($fsql);
            $result['feedback'] = $feedback[0];
            $sql = "select a.id as reply_id,a.floor_id,a.content,a.create_time as reply_time,b.nickname,b.id ,b.avatar from tab_news_reply a 
left join tab_user b on a.u_id=b.id  WHERE a.feedback_id = {$feedback_id}  ORDER BY a.id DESC limit {$start},{$showPage}";
            $reply = $this->db->action($sql);
            foreach ($reply as $key => $value) {
                if (!empty($value['avatar'])) {
                    $reply[$key]['avatar'] = WZAPP . $value['avatar'];
                } else {
                    $reply[$key]['avatar'] = "";
                }
                if ($value['floor_id'] == 0) {
                    $reply[$key]['reply_user'] ="";
                } else {
                    $user = $this->db->action("select a.nickname,a.id from tab_user a left join tab_news_reply b on a.id=b.u_id where b.id={$value['floor_id']}");
                    if (!empty($user)) {
                        $reply[$key]['reply_user'] = $user[0]['nickname'] ? $user[0]['nickname'] : "用户".$user[0]['id'];
                    }
                }
            }
            $result['replydata'] = $reply;
        }
        elseif ($type==2)
        {
            $fsql = "select a.id as feedback_id,a.u_id as id,a.game_id,a.content,a.create_time,b.nickname ,b.avatar from tab_game_feedback 
 a left join tab_user b on a.u_id=b.id WHERE a.id = {$feedback_id} ";
            $feedback = $this->db->action($fsql);
            $result['feedback'] = $feedback[0];
            $sql = "select a.id as reply_id,a.floor_id,a.content,a.create_time as reply_time,b.nickname,b.id,b.avatar from tab_game_reply a 
left join tab_user b on a.u_id=b.id  WHERE a.feedback_id = {$feedback_id}  ORDER BY a.id DESC limit {$start},{$showPage}";
            $reply = $this->db->action($sql);
            foreach ($reply as $key => $value) {
                if (!empty($value['avatar'])) {
                    $reply[$key]['avatar'] = WZAPP . $value['avatar'];
                } else {
                    $reply[$key]['avatar'] = "";
                }
                if ($value['floor_id'] == 0) {
                    $reply[$key]['reply_user'] ="";
                } else {
                    $user = $this->db->action("select a.nickname,a.id from tab_user a left join tab_game_reply b on a.id=b.u_id where b.id={$value['floor_id']}");
                    if (!empty($user)) {
                        $reply[$key]['reply_user'] = $user[0]['nickname'] ? $user[0]['nickname'] : "用户".$user[0]['id'];
                    }
                }
            }
            $result['replydata'] = $reply;
        }
        elseif ($type==3)
        {
            $fsql = "select a.id as feedback_id,a.u_id as id,a.group_log_id,a.content,a.create_time,b.nickname ,b.avatar from tab_group_feedback 
 a left join tab_user b on a.u_id=b.id WHERE a.id = {$feedback_id} ";
            $feedback = $this->db->action($fsql);
            $result['feedback'] = $feedback[0];
            $sql = "select a.id as reply_id,a.floor_id,a.content,a.create_time as reply_time,b.nickname,b.id ,b.avatar from tab_group_reply a 
left join tab_user b on a.u_id=b.id  WHERE a.feedback_id = {$feedback_id}  ORDER BY a.id DESC limit {$start},{$showPage}";
            $reply = $this->db->action($sql);
            foreach ($reply as $key => $value) {
                if (!empty($value['avatar'])) {
                    $reply[$key]['avatar'] = WZAPP . $value['avatar'];
                } else {
                    $reply[$key]['avatar'] = "";
                }
                if ($value['floor_id'] == 0) {
                    $reply[$key]['reply_user'] = "";
                } else {
                    $user = $this->db->action("select a.nickname,a.id from tab_user a left join tab_group_reply b on a.id=b.u_id where b.id={$value['floor_id']}");
                    if (!empty($user)) {
                        $reply[$key]['reply_user'] = $user[0]['nickname'] ? $user[0]['nickname'] : "用户".$user[0]['id'];
                    }
                }
            }
            $result['replydata'] = $reply;
        }
        $this->ajax_return(0,$result);
    }
    //评论列表
    public function feedbackAction(){
        //$this->input=$_GET;
        $id = $this->input['comment_id'];
        $type=$this->input['type'];
        $uid=$this->input['id']?$this->input['id']:0;
        $currentPage = empty($this->input["page"])?"1":$this->input["page"];
        $showPage = empty($this->input["showpage"])?"9":$this->input["showpage"];
        $start =  ($currentPage-1)*$showPage;
        if($type==1) {
            $sql = "
           select a.id as feedback_id,a.u_id as id,a.news_id,a.content,a.create_time,b.nickname ,b.avatar from tab_news_feedback 
 a left join tab_user b on a.u_id=b.id where a.news_id={$id} order by a.create_time desc limit {$start},{$showPage}";
            $result = $this->db->action($sql);
            foreach ($result as $k => $v) {
                if (!empty($v['avatar'])) {
                    $result[$k]['avatar'] = WZAPP . $v['avatar'];
                } else {
                    $result[$k]['avatar'] = "";
                }
                $result[$k]['feedback_click']=intval($this->db->zscount("news_feedback_click","*","total","feedback_id={$v['feedback_id']} "));
                $is_click = 0;
                if(!empty($uid)) {
                    $click = $this->db->field("*")->table("tab_news_feedback_click")->where("u_id = {$uid} and  feedback_id={$v['feedback_id']}")->find();
                    if (!empty($click)) {
                        $is_click = 1;
                    }
                }
                $result[$k]['is_click'] = $is_click;
                $result[$k]['replysum'] = $this->replysum($v['feedback_id'],$type);
                $replydata = $this->replydata($v['feedback_id'],$type);
                foreach ($replydata as $key => $value) {
                    if ($value['floor_id'] == 0) {
                        $replydata[$key]['reply_user'] = $result[$k]['nickname'] ? $result[$k]['nickname'] : "用户".$result[$k]['id'];
                    } else {
                        $user = $this->db->action("select a.nickname,a.id from tab_user a left join tab_news_reply b on a.id=b.u_id where b.id={$value['floor_id']}");
                        if (!empty($user)) {
                            $replydata[$key]['reply_user'] = $user[0]['nickname'] ? $user[0]['nickname'] : "用户".$user[0]['id'];
                        }
                    }
                }
                $result[$k]['replydata'] = $replydata;

            }
        }
        elseif ($type==2)
        {
            $sql = "
           select a.id as feedback_id,a.u_id as id,a.game_id,a.content,a.create_time,b.nickname ,b.avatar from tab_game_feedback 
 a left join tab_user b on a.u_id=b.id where a.game_id={$id} order by a.create_time desc limit {$start},{$showPage}";
            $result = $this->db->action($sql);
            foreach ($result as $k => $v) {
                if (!empty($v['avatar'])) {
                    $result[$k]['avatar'] = WZAPP . $v['avatar'];
                } else {
                    $result[$k]['avatar'] = "";
                }
                $result[$k]['feedback_click']=intval($this->db->zscount("game_feedback_click","*","total","feedback_id={$v['feedback_id']} "));
                $is_click = 0;
                if(!empty($uid)) {
                    $click = $this->db->field("*")->table("tab_game_feedback_click")->where("u_id = {$uid} and  feedback_id={$v['feedback_id']}")->find();
                    if (!empty($click)) {
                        $is_click = 1;
                    }
                }
                $result[$k]['is_click'] = $is_click;
                $result[$k]['replysum'] = $this->replysum($v['feedback_id'],$type);
                $replydata = $this->replydata($v['feedback_id'],$type);
                foreach ($replydata as $key => $value) {
                    if ($value['floor_id'] == 0) {
                        $replydata[$key]['reply_user'] = $result[$k]['nickname'] ? $result[$k]['nickname'] : "用户".$result[$k]['id'];
                    } else {
                        $user = $this->db->action("select a.nickname,a.id from tab_user a left join tab_game_reply b on a.id=b.u_id where b.id={$value['floor_id']}");
                        if (!empty($user)) {
                            $replydata[$key]['reply_user'] = $user[0]['nickname'] ? $user[0]['nickname'] : "用户".$user[0]['id'];
                        }
                    }
                }
                $result[$k]['replydata'] = $replydata;

            }
        }
        elseif ($type==3)
        {
            $sql = "
           select a.id as feedback_id,a.u_id as id,a.group_log_id,a.content,a.create_time,b.nickname ,b.avatar from tab_group_feedback 
 a left join tab_user b on a.u_id=b.id where a.group_log_id={$id} order by a.create_time desc limit {$start},{$showPage}";
            $result = $this->db->action($sql);

            foreach ($result as $k => $v) {
                if (!empty($v['avatar'])) {
                    $result[$k]['avatar'] = WZAPP . $v['avatar'];
                } else {
                    $result[$k]['avatar'] = "";
                }
                $result[$k]['feedback_click']=intval($this->db->zscount("group_feedback_click","*","total","feedback_id={$v['feedback_id']} "));
                $is_click=0;
                if(!empty($uid)) {
                    $click = $this->db->field("*")->table("tab_group_feedback_click")->where("u_id = {$uid} and  feedback_id={$v['feedback_id']}")->find();
                    if (!empty($click)) {
                        $is_click=1;
                    }
                }
                $result[$k]['is_click'] = $is_click;
                $result[$k]['replysum'] = $this->replysum($v['feedback_id'],$type);
                $replydata = $this->replydata($v['feedback_id'],$type);
                foreach ($replydata as $key => $value) {
                    if ($value['floor_id'] == 0) {
                        $replydata[$key]['reply_user'] = $result[$k]['nickname'] ? $result[$k]['nickname'] : "用户".$result[$k]['id'];
                    } else {
                        $user = $this->db->action("select a.nickname,a.id from tab_user a left join tab_group_reply b on a.id=b.u_id where b.id={$value['floor_id']}");
                        if (!empty($user)) {
                            $replydata[$key]['reply_user'] = $user[0]['nickname'] ? $user[0]['nickname'] : "用户".$user[0]['id'];
                        }
                    }
                }
                $result[$k]['replydata'] = $replydata;

            }
        }
        $this->ajax_return(0,$result);
    }
    public function replydata($data,$type){
        if($type==1) {
            $sql = "select a.id as reply_id,a.floor_id,a.content,a.create_time as reply_time,b.nickname,b.id from tab_news_reply a 
left join tab_user b on a.u_id=b.id  WHERE a.feedback_id = {$data}  ORDER BY a.id DESC limit 0,3";
            $data = $this->db->action($sql);
        }
        elseif ($type==2){
            $sql = "select a.id as reply_id,a.floor_id,a.content,a.create_time as reply_time,b.nickname,b.id from tab_game_reply a 
left join tab_user b on a.u_id=b.id  WHERE a.feedback_id = {$data}  ORDER BY a.id DESC limit 0,3";
            $data = $this->db->action($sql);
        }
        elseif ($type==3){
            $sql = "select a.id as reply_id,a.floor_id,a.content,a.create_time as reply_time,b.nickname,b.id from tab_group_reply a 
left join tab_user b on a.u_id=b.id  WHERE a.feedback_id = {$data}  ORDER BY a.id DESC limit 0,3";
            $data = $this->db->action($sql);
        }
        return $data;
    }
    public function replysum($data,$type){
        if($type==1) {
            $sql = "
            SELECT count(*) as total 
            FROM tab_news_reply as r 
            INNER JOIN tab_user as u ON u.id = r.u_id 
            WHERE r.feedback_id = {$data} 
            ORDER BY r.id DESC ";
            $data = $this->db->action($sql);
        }
        elseif($type==2)
        {
            $sql = "
            SELECT count(*) as total 
            FROM tab_game_reply as r 
            INNER JOIN tab_user as u ON u.id = r.u_id 
            WHERE r.feedback_id = {$data} 
            ORDER BY r.id DESC ";
            $data = $this->db->action($sql);
        }
        elseif($type==3)
        {
            $sql = "
            SELECT count(*) as total 
            FROM tab_group_reply as r 
            INNER JOIN tab_user as u ON u.id = r.u_id 
            WHERE r.feedback_id = {$data} 
            ORDER BY r.id DESC ";
            $data = $this->db->action($sql);
        }
        return $data[0]['total'];
    }
    //资讯详情添加评论
    public function commentAction()
    {
        //$this->input=$_GET;
        $type=$this->input['type'];
        $uid=$this->input['id']?$this->input['id']:0;
        $id = $this->input['comment_id'];
        if(empty($uid))
        {
            $this->ajax_return(1013);
        }
        if($type==1) {
            if (!empty($id)) {
                $news = $this->db->field("*")->table("tab_news")->where("id = {$id}")->find();
                if (!empty($news)) {
                    $data2['u_id'] = $uid;
                    $data2['news_id'] = $id;
                    $data2['content'] = $this->str_rep(addslashes($this->input['content']));
                    $data2['create_time'] = time();
                    $this->db->action("set names utf8mb4");
                    $bool = $this->db->action($this->db->insertSql("news_feedback", $data2));
                    if ($bool) {
                        $this->ajax_return(0);
                    } else {
                        $this->ajax_return(500);
                    }
                } else {
                    $this->ajax_return(1209);
                }
            } else {
                $this->ajax_return(102);
            }
        }
        elseif ($type==2)
        {
            if (!empty($id)) {
                $game = $this->db->field("*")->table("tab_game")->where("id = {$id}")->find();
                if (!empty($game)) {
                    $data2['u_id'] = $uid;
                    $data2['game_id'] = $id;
                    $data2['content'] = $this->str_rep(addslashes($this->input['content']));
                    $data2['create_time'] = time();
                    $this->db->action("set names utf8mb4");
                    $bool = $this->db->action($this->db->insertSql("game_feedback", $data2));
                    if ($bool) {
                        $this->ajax_return(0);
                    } else {
                        $this->ajax_return(500);
                    }
                } else {
                    $this->ajax_return(1205);
                }
            } else {
                $this->ajax_return(102);
            }
        }
        elseif ($type==3)
        {
            if (!empty($id)) {
                //获取帖子
                $usergroup = $this->db->field("*")->table("tab_user_group")->where("id={$id}")->find();
                $data2['u_id'] = $uid;
                $data2['group_log_id'] = $id;
                $data2['content'] = $this->str_rep(addslashes($this->input['content']));
                $data2['create_time'] = time();
                $this->db->action("set names utf8mb4");
                if($uid!=$usergroup['u_id']){
                    $data2['group_user_id']=$usergroup['u_id'];
                }
                $bool = $this->db->action($this->db->insertSql("group_feedback", $data2));
                 $user = $this->db->field("nickname,avatar")->table("tab_user")->where("id={$uid}")->find();
                if ($bool) {
                    if($uid!=$usergroup['u_id'])
                    {
                        if (!empty($user['avatar'])) {
                            $user['avatar'] = WZAPP . $user['avatar'];
                        } else {
                            $user['avatar'] = "";
                        }
                        $this->jpapi(1,$usergroup['u_id'],$uid,$user['nickname'],$user['avatar'],$usergroup['content']);
                        //  $this->ajax_return(0);
                    }
                    else{
                        $this->ajax_return(0);
                    }
                } else {
                    $this->ajax_return(500);
                }

            } else {
                $this->ajax_return(102);
            }
        }
    }
    //详情添加回复
    public function addreplyAction(){
        $type=$this->input['type'];
        $data['u_id'] = $this->input['id'];
        $data['feedback_id'] = $this->input['feedback_id'];
        $data['content'] = addslashes($this->input['content']);
        $data['floor_id'] = $this->input['floor_id'];
        $data['create_time'] = time();
        $this->db->action("set names utf8mb4");
        if($type==1) {
            if($data['floor_id']==0) {
                $result = $this->db->field("*")->table("tab_news_feedback")->where("id={$this->input['feedback_id']}")->find();
            }
            else{
                $result = $this->db->field("*")->table("tab_news_reply")->where("id={$this->input['floor_id']}")->find();
            }
            if($this->input['id']!=$result['u_id'])
            {
                $data['user_id']=$result['u_id'];
            }
            $bool = $this->db->action($this->db->insertSql("news_reply", $data));
        }
        elseif ($type==2)
        {
            if($data['floor_id']==0) {
                $result = $this->db->field("*")->table("tab_game_feedback")->where("id={$this->input['feedback_id']}")->find();
            }
            else{
                $result = $this->db->field("*")->table("tab_game_reply")->where("id={$this->input['floor_id']}")->find();
            }
            if($this->input['id']!=$result['u_id'])
            {
                $data['user_id']=$result['u_id'];
            }
            $bool = $this->db->action($this->db->insertSql("game_reply", $data));
        }
        elseif ($type==3)
        {
            if($data['floor_id']==0) {
                $result = $this->db->field("*")->table("tab_group_feedback")->where("id={$this->input['feedback_id']}")->find();
            }
            else{
                $result = $this->db->field("*")->table("tab_group_reply")->where("id={$this->input['floor_id']}")->find();
            }
            if($this->input['id']!=$result['u_id'])
            {
                $data['user_id']=$result['u_id'];
            }
            $bool = $this->db->action($this->db->insertSql("group_reply", $data));
        }
        if ($bool) {
            $user = $this->db->field("nickname,avatar")->table("tab_user")->where("id={$this->input['id']}")->find();
            if($this->input['id']!=$result['u_id'])
            {
               if (!empty($user['avatar'])) {
                    $user['avatar'] = WZAPP . $user['avatar'];
                } else {
                    $user['avatar'] = "";
                }
                $this->jpapi(2,$result['u_id'],$this->input['id'],$user['nickname'],$user['avatar'],$result['content']);

            }
            else{
                $this->ajax_return(0);
            }
        } else {
            $this->ajax_return(500);
        }
    }
    //资讯详情
    public function newdetailAction(){
        $id=get('n_id');
        $u_id=get('id');
        $data = $this->db->field("id,title,content,create_time,game_id,view,click")->table("tab_news")->where("id = {$id}")->find();
        $data['is_click']=0;
        if(!empty($u_id))
        {
            $click=$this->db->field("*")->table("tab_news_click")->where("n_id = {$id} and u_id={$u_id}")->find();
            if(!empty($click))
            {
                $data['is_click']=1;
            }
        }
        if(!empty($data['game_id']))
        {
            $game=$this->db->field('game_name')->table('tab_game')->where("id={$data['game_id']}")->find();
            $data['game_name']=$game['game_name'];
        }
        else{
            $data['game_name']='';
        }
        $data['create_time']=date('Y-m-d H:i',$data['create_time']);
        $data['content'] = htmlspecialchars_decode($data['content']);
        $new['view']=(int)$data['view']+1;
        $this->db->action($this->db->updateSql("news",$new," id = '{$id}' "));
       $this->ajax_return(0,$data);
    }
    //资讯点赞
    public function clicknewAction()
    {
        //include APP_PATH."/vendor/message/sms.php";
        $reqdata=$this->phpinput();
        $id = $reqdata['n_id'];
        $uid=$reqdata['id'];
        $data = $this->db->field("click")->table("tab_news")->where("id = {$id}")->find();
        if(empty($uid))
        {
            $this->ajax_return(1013);
        }
        if(!empty($id)){
            $arrData = $this->db->field("*")->table("tab_news_click")->where(" u_id = {$uid} and n_id = {$id} ")->find();
            if(!empty($arrData)){
                //取消点赞
                $bool = $this->db->action($this->db->deleteSql("news_click"," u_id = {$uid} and n_id = {$id} "));
                $click['click']=$data['click']-1;
                $this->db->action($this->db->updateSql("news",$click,"id = {$id}"));
                if($bool){
                   $this->ajax_return(0);
                }else{
                    $this->ajax_return(500);
                }
            }else{
                //点赞
                $data2['u_id'] =$uid;
                $data2['n_id'] = $id;
                $arrData['click']=intval($data['click'])+1;
                $this->db->action($this->db->updateSql("news",$arrData,"id = {$id}"));
                $bool = $this->db->action( $this->db->insertSql("news_click",$data2));
                if($bool){
                 /*   $demo = new Sms("5790414467e58ebc2f0008ae", "dgbtvr7myr3flllbpc6bww4gkfwjpnmv");
                    $demo->sendAndroidUnicast();*/
                    $this->ajax_return(0);
                }else{
                    $this->ajax_return(500);
                }
            }
        }else{
            $this->ajax_return(104);
        }
    }
    //资讯中心
    public function newsAction(){
        $type=get('type');
        $user_id=get('id')?get('id'):0;
        $game_id=get('game_id')?get('game_id'):0;
        //$search = preg_replace("/[%_\s]+/","",ltrim(addslashes($_GET['search'])));
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "5" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        if(!empty($game_id))
        {
            $news=$this->db->action("select a.id,a.title,a.create_time,a.pic,a.click,b.game_type_name as cate_title from tab_news a left join tab_game b on a.game_id=b.id
        where a.game_id={$game_id}    order by a.view desc limit {$start},{$showPage}");
        }
        else{
            if (!isset($type) || empty($type)) {
                $where = " a.position  LIKE '%2%' ";
                $news = $this->db->action("select a.id,a.title,a.create_time,a.pic,a.click,b.game_type_name as cate_title from tab_news a left join tab_game b on a.game_id=b.id
              where {$where} order by a.view desc limit {$start},{$showPage}");
            } else {
                $where = " a.type='{$type}' ";
                $news = $this->db->action("select a.id,a.title,a.create_time,a.pic,a.click,b.game_type_name as cate_title from tab_news a left join tab_game b on a.game_id=b.id
        where {$where}  order by a.view desc limit {$start},{$showPage}");
            }
        }
        foreach ($news as $key=>$value)
        {
            if(empty($value['cate_title']))
            {
                $news[$key]['cate_title']="";
            }
            $news[$key]['create_time'] = get_time($value['create_time']);
            if(empty($value['cate_title']))
            {
                $news[$key]['cate_title']="";
            }
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
    //资讯分类
    public function newtypeAction(){
        $typeData = $this->db->field("id,type")->table("tab_news_type")->order("id asc")->select();
        $this->ajax_return(0,$typeData);
    }
    //游戏分类
    public function gameAction(){
        //top
        $banner = $this->db->field("*")->table("tab_game_type_banner")->where("type='游戏分类'")->order("id asc")->select();
        $data['top']=$banner;
        $type=get('type');
        if(!empty($type))
        {
            $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
            $showPage = 10;
            $start = ($currentPage - 1) * $showPage;
            $game=$this->db->action(" select a.id, a.game_name,a.game_type_name,a.icon,a.version,c.file_size as game_size ,a.and_dow_address,a.version_num,b.apk_pck_name,a.game_address
  from tab_game a left join tab_game_set b on a.id=b.game_id  left join tab_game_source c on a.id=c.game_id where a.game_type_id='{$type}' and a.game_status=1 order by a.sort desc  limit {$start},{$showPage}");
        }
        else{
            $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
            $showPage = empty($_GET["showpage"]) ? "30" : $_GET["showpage"];
            $start = ($currentPage - 1) * $showPage;
            $game=$this->db->action("select a.id,a.game_name,a.game_type_name,a.icon,a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name ,a.game_address
  from tab_game a left join tab_game_set b on a.id=b.game_id  left join tab_game_source c on a.id=c.game_id where (a.recommend_status=1 or a.recommend_status=2) and a.game_status=1 order by a.sort desc  limit {$start},{$showPage}");
        }
        foreach ($game as $key=>$value)
        {
            $game[$key]['icon']=$this->get_cover($value['icon'],'path');
            if(!empty($value['and_dow_address'])) {
                $game[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        $data['game']=$game;
        $this->ajax_return(0,$data);

    }
    public function gametypeAction(){
        $typeData = $this->db->field("id,type_name")->table("tab_game_type")->where("status=1")->order("app_sort asc")->select();
        $this->ajax_return(0,$typeData);
    }
    //新游精选
    public function  xinAction()
    {

        $data=$this->db->action("select a.game_name,a.game_type_name,a.cover,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id where a.game_status=1 
    and a.recommend_status=3 order by a.sort desc limit 1");
        if(!empty($data)) {
             if(!empty($data[0]['and_dow_address'])) {
                $data[0]['and_dow_address'] = ZSWH . substr($data[0]['and_dow_address'], 1);
            }
         }
        else{
            $this->ajax_return(0,1205);
        }
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "5" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $game = $this->db->action("select a.game_name,a.game_type_name,a.icon,a.introduction,a.id,a.features,a.game_address,c.file_size as game_size,
 a.version,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id   left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=3 and a.id!={$data[0]['id']} order by a.sort desc limit {$start},{$showPage}");
        $data[0]['icon']=$this->get_cover($data[0]['icon'],'path');
        $data[0]['cover']=$this->get_cover($data[0]['cover'],'path');
        foreach ($game as $key=>$value)
        {
            if(!empty($value['and_dow_address'])) {
                $game[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
            $game[$key]['icon']=$this->get_cover($value['icon'],'path');
        }
        $arrdata['top']=$data[0];
        $arrdata['game']=$game;
       $this->ajax_return(0,$arrdata);

    }
    //新游首发
    public function startAction(){
        $xin = $this->db->action("select a.game_name,a.game_address,a.icon,a.cover,a.id,a.version,a.and_dow_address,a.version_num,b.apk_pck_name ,a.create_time from tab_game a left join tab_game_set b on a.id=b.game_id where a.game_status=1 
    and a.recommend_status=3 order by a.sort desc limit 6");
        foreach ($xin as $key=>$value)
        {
            $xin[$key]['icon']=$this->get_cover($value['icon'],'path');
            $xin[$key]['create_time']=date("m月d日",$value['create_time']);
            $xin[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $xin[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        $starttime=strtotime(date("Y-m-d",strtotime("-5 day")));
        $endtime=strtotime(date("Y-m-d"))-1;
        $game = $this->db->action("select a.game_name,a.game_type_name,a.icon,a.id,a.version,a.and_dow_address,a.version_num,b.apk_pck_name ,a.introduction,a.id,a.features,a.game_address,
FROM_UNIXTIME(a.create_time,'%m-%d') as create_time  from tab_game a left join tab_game_set b on a.id=b.game_id where a.game_status=1 
    and a.create_time>={$starttime} and a.create_time<={$endtime}  order by a.create_time desc ");
        foreach ($game as $key=>$value)
        {
            $value['icon']=$this->get_cover($value['icon'],'path');
            if(!empty($value['and_dow_address'])) {
                $value['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
             }
            if($value['create_time']==date("m-d",strtotime("-1 day")))
            {
                $arr[]=$value;
            }
            else if($value['create_time']==date("m-d",strtotime("-2 day"))){
                $arr1[]=$value;
            }
            else if($value['create_time']==date("m-d",strtotime("-3 day"))){
                $arr2[]=$value;
            }
            else if($value['create_time']==date("m-d",strtotime("-4 day"))){
                $arr3[]=$value;
            }
            else if($value['create_time']==date("m-d",strtotime("-5 day"))){
                $arr4[]=$value;
            }
        }
        $data['xin']=$xin;
        $data['onegame']['ts']="昨日";
        if(!empty($arr)) {
            $data['onegame']['game'] = $arr;
        }
        else{
            $data['onegame']['game'] = [];
        }
        $data['twogame']['ts']=date("m月d日",strtotime("-2 day"));
        if(!empty($arr1)) {
            $data['twogame']['game'] = $arr1;
        }
        else{
            $data['twogame']['game'] = [];
        }
        $data['threegame']['ts']=date("m月d日",strtotime("-3 day"));
        if(!empty($arr2)) {
            $data['threegame']['game'] = $arr2;
        }
        else {
            $data['threegame']['game'] = [];
        }
            $data['fourgame']['ts']=date("m月d日",strtotime("-4 day"));
        if(!empty($arr3)) {
            $data['fourgame']['game'] = $arr3;
        }
        else {
            $data['fourgame']['game'] = [];
        };
        $data['fivegame']['ts']=date("m月d日",strtotime("-5 day"));
        if(!empty($arr4)) {
            $data['fivegame']['game'] = $arr4;
        }
        else {
            $data['fivegame']['game'] = [];
        }
        $this->ajax_return(0,$data);
    }
    //下载管理
    public function  downloadAction(){
        //推荐
        $recommend=$this->db->action("select a.game_name,a.game_type_name,a.icon,a.game_address,a.and_dow_address, a.version,a.version_num,b.apk_pck_name,b.game_id,a.introduction,a.features
        from tab_game a left join tab_game_set b on a.id=b.game_id where a.recommend_status=1 and a.game_status=1  order by a.sort desc limit 5");
        foreach ($recommend as $key=> $value)
        {
            if(!empty($value['and_dow_address'])) {
                $recommend[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
            $recommend[$key]['icon']=$this->get_cover($value['icon'],'path');
        }
        $this->ajax_return(0,$recommend);
    }
    //游戏更新
    public function  updateAction(){
        //推荐
        $recommend=$this->db->action("select a.game_name,a.game_type_name,a.icon,a.game_address,a.and_dow_address, a.version,a.version_num,b.apk_pck_name,b.game_id,a.introduction,a.features
        from tab_game a left join tab_game_set b on a.id=b.game_id where a.recommend_status=1 and a.game_status=1  order by a.sort desc limit 5");
        foreach ($recommend as $key=> $value)
        {
            if(!empty($value['and_dow_address'])) {
                $recommend[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
            $recommend[$key]['icon']=$this->get_cover($value['icon'],'path');
        }
        $this->ajax_return(0,$recommend);
    }
    //本周热游
    public function hotAction(){
        $type=get('type');
        //top
        $top = $this->db->field("id,cover")->table("tab_game")->where("game_status=1 and (recommend_status=2 or recommend_status=1)")->order("sort desc")->find();
        if (!empty($top['cover'])) {
            $top['cover'] = $this->get_cover($top['cover'], 'path');
        }
        $data['top']=$top;
        if($type==1)
        //当前日期
        {
            $where=" and 1=1 ";
            //本周热游
            $hot = $this->db->action("select a.game_name,a.game_type_name,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id 
       left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=2 order by a.sort desc limit 4");
            foreach ($hot as $key => $value) {
                $hot[$key]['icon'] = $this->get_cover($value['icon'], 'path');
                if (!empty($value['and_dow_address'])) {
                    $hot[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
                }

            }
         }
        else{
            //bt推荐
            $hot =$this->db->action("select a.game_name,a.game_type_name,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id
        left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=2 and a.game_type_id=17  order by a.sort desc limit 4");
            foreach ($hot as $key=>$value)
            {
                $hot[$key]['icon']=$this->get_cover($value['icon'],'path');
                if(!empty($value['and_dow_address'])) {
                    $hot[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
                }

            }
            $where=" and  a.game_type_id=17  ";
        }
        //热门推荐
        $recommend =$this->db->action("select a.game_name,a.game_type_name,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id 
       left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=1 {$where} order by a.sort desc limit 4");
        foreach ($recommend as $key=>$value)
        {
            $recommend[$key]['icon']=$this->get_cover($value['icon'],'path');
            if(!empty($value['and_dow_address'])) {
                $recommend[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        $data['hot']=$hot;
        $data['recommend']=$recommend;
        $this->ajax_return(0,$data);
    }
    //批量获取用户信息
    public function userlistAction(){
        $indata = [];
        if(empty($_GET['keys']))
        {
            $result=[];
        }
        else {
            $userid = json_decode($_GET['keys']);
            foreach ($userid as $value) {
                $indata[] = $value;
            }
            $user = implode(",", $indata);
            $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
            $showPage = empty($_GET["showpage"]) ? "6" : $_GET["showpage"];
            $start = ($currentPage - 1) * $showPage;
            $result = $this->db->action(" select id,account,nickname,avatar,sex from tab_user where id in ($user) limit {$start},{$showPage}");
            foreach ($result as $k => $v) {
                if (!empty($v['avatar'])) {
                    $result[$k]['avatar'] = WZAPP . $v['avatar'];
                } else {
                    $result[$k]['avatar'] = "";
                }
                if ($v['sex'] == 0) {
                    $result[$k]['sex'] = "男";
                } else {
                    $result[$k]['sex'] = "女";
                }
            }
        }
        $this->ajax_return(0,$result);

    }
    //圈子点赞
    public function clickgroupAction()
    {
        $id = $_GET['a_id'];
        $user_id=$_GET['id'];
        //获取帖子
        $usergroup = $this->db->field("*")->table("tab_user_group")->where("id={$id}")->find();
      //  $this->ajax_return(0,$usergroup);
        $user = $this->db->field("nickname,avatar")->table("tab_user")->where("id={$user_id}")->find();
        if(!empty($id)){
            $arrData = $this->db->action("select * from tab_group_click where u_id={$user_id} and group_log_id={$id}");
            if(!empty($arrData)){
                //取消点赞
                $bool = $this->db->action($this->db->deleteSql("group_click"," u_id = {$user_id} and group_log_id = {$id} "));
                if($bool){

                    $this->ajax_return(0);
                }else{
                    $this->ajax_return(500);
                }
            }else{
                //点赞
                $data2['u_id'] =$user_id;
                $data2['group_log_id'] = $id;
                $data2['create_time']=time();
                if($user_id!=$usergroup['u_id']){
                    $data2['group_user_id']=$usergroup['u_id'];
                }
                $bool = $this->db->action( $this->db->insertSql("group_click",$data2));
                if($bool){
                    if($user_id!=$usergroup['u_id'])
                    {
                        if (!empty($user['avatar'])) {
                            $user['avatar'] = WZAPP . $user['avatar'];
                        } else {
                            $user['avatar'] = "";
                        }
                        $this->jpapi(0,$usergroup['u_id'],$user_id,$user['nickname'],$user['avatar'],$usergroup['content']);
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

        $this->forward("index","message","test2",["id"=>"123"]);


    }
    public function test($data){

        $newArr = array();

        foreach($data as $key=>$v){
            $newArr[$key]['create_time'] = $v['create_time'];
        }
        array_multisort($newArr,SORT_DESC,$data);//SORT_DESC为降序，SORT_ASC为升序
        return $data;

    }
    //点赞通知
    public function clicklistAction(){
        $user_id=$_GET['id'];
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "10" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        //圈子点赞
        $usergroup=$this->db->action("select a.u_id as id,a.create_time,c.id as c_id,b.nickname,b.avatar,c.content,d.game_name  from tab_group_click a left join tab_user b on a.u_id=b.id 
 left join tab_user_group c on a.group_log_id=c.id  left join tab_group_type d on c.game_id=d.id where a.group_user_id={$user_id} 
order by a.id desc ");
        foreach ($usergroup as $key=>$value)
        {
            $usergroup[$key]['type']=1;
            if (!empty($value['avatar'])) {
                $usergroup[$key]['avatar'] = WZAPP . $value['avatar'];
            } else {
                $usergroup[$key]['avatar'] = '';
            }
           // $usergroup[$key]['create_time']=date(" Y-m-d H:i",$value['create_time']);
        }
        //资讯点评点赞
        $newclick=$this->db->action("select a.u_id as id,a.create_time,b.nickname,b.avatar,c.id as c_id,c.content,e.game_name from tab_news_feedback_click a 
left join tab_user b on a.u_id=b.id left join tab_news_feedback c on a.feedback_id=c.id left join tab_news d on c.news_id=d.id  left join tab_game e on d.game_id=e.id  where a.user_id={$user_id}");
        foreach ($newclick as $key=>$value)
        {
            $newclick[$key]['type']=2;
            $newclick[$key]['game_name']=$value['game_name']?$value['game_name']:'';
            if (!empty($value['avatar'])) {
                $newclick[$key]['avatar'] = WZAPP . $value['avatar'];
            } else {
                $newclick[$key]['avatar'] = '';
            }
           // $newclick[$key]['create_time']=date("Y-m-d H:i",$value['create_time']);
        }
        //游戏点评点赞
        $gameclick=$this->db->action("select a.u_id as id,a.create_time,b.nickname,b.avatar,c.id as c_id,c.content,d.game_name from tab_game_feedback_click a 
left join tab_user b on a.u_id=b.id left join tab_game_feedback c on a.feedback_id=c.id   left join tab_game d on c.game_id=d.id  where a.user_id={$user_id}");
        foreach ($gameclick as $key=>$value)
        {
            $gameclick[$key]['type']=3;
            $gameclick[$key]['game_name']=$value['game_name']?$value['game_name']:'';
            if (!empty($value['avatar'])) {
                $gameclick[$key]['avatar'] = WZAPP . $value['avatar'];
            } else {
                $gameclick[$key]['avatar'] = '';
            }
           // $gameclick[$key]['create_time']=date("Y-m-d H:i",$value['create_time']);
        }
        //圈子点评点赞
        $groupclick=$this->db->action("select a.u_id as id,a.create_time,b.nickname,b.avatar,c.id as c_id,c.content,e.game_name from tab_group_feedback_click a 
left join tab_user b on a.u_id=b.id left join tab_group_feedback c on a.feedback_id=c.id left join tab_user_group d on c.group_log_id=d.id  left join tab_group_type e on d.game_id=e.id 
 where a.user_id={$user_id}");
        foreach ($groupclick as $key=>$value)
        {
            $groupclick[$key]['type']=4;
            $groupclick[$key]['game_name']=$value['game_name']?$value['game_name']:'';
            if (!empty($value['avatar'])) {
                $groupclick[$key]['avatar'] = WZAPP . $value['avatar'];
            } else {
                $groupclick[$key]['avatar'] = '';
            }
        }
        $data = array_merge($usergroup,$newclick,$gameclick,$groupclick);
        $data=$this->test($data);
        $data = array_slice($data,$start,$showPage);
        $this->ajax_return(0,$data);
    }
    //评论通知
    public function feedbacklistAction(){
        $user_id=$_GET['id'];
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "10" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        //圈子评论通知
        $group_feedback=$this->db->action("select a.u_id as id,a.create_time,a.content,b.nickname,b.avatar,c.content as ycontent ,c.id as c_id,d.game_name from tab_group_feedback a
left join tab_user b on a.u_id=b.id left join tab_user_group c on a.group_log_id=c.id left join tab_group_type d on c.game_id=d.id where a.group_user_id={$user_id}");
        foreach ($group_feedback as $key=>$value)
        {
            $group_feedback[$key]['game_name']=$value['game_name']?$value['game_name']:'';
            if (!empty($value['avatar'])) {
                $group_feedback[$key]['avatar'] = WZAPP . $value['avatar'];
            } else {
                $group_feedback[$key]['avatar'] = '';
            }
            $group_feedback[$key]['type']=1;
        }
        //圈子评论回复通知
        $group_reply= $this->db->action("select a.u_id as id,a.create_time,a.content,a.floor_id,a.feedback_id,b.nickname,b.avatar,e.game_name,a.feedback_id as c_id from tab_group_reply a
left join tab_user b on a.u_id=b.id left join tab_group_feedback c on a.feedback_id=c.id left join tab_user_group d on c.group_log_id=d.id left join 
 tab_group_type e on d.game_id=e.id where a.user_id={$user_id}");
        foreach ($group_reply as $key=>$value)
        {
            $group_reply[$key]['game_name']=$value['game_name']?$value['game_name']:'';
            if (!empty($value['avatar'])) {
                $group_reply[$key]['avatar'] = WZAPP . $value['avatar'];
            } else {
                $group_reply[$key]['avatar'] = '';
            }
            $group_reply[$key]['type']=4;
            if($value['floor_id']==0)
            {
                $result = $this->db->field("content")->table("tab_group_feedback")->where(" id = {$value['feedback_id']}")->find();
            }
            else{
                $result = $this->db->field("*")->table("tab_group_reply")->where(" id = {$value['floor_id']}")->find();
            }
            $group_reply[$key]['ycontent']=$result['content'];
            unset($group_reply[$key]['floor_id']);
            unset($group_reply[$key]['feedback_id']);
        }
        //资讯评论回复通知
        $new_reply=$this->db->action("select a.u_id as id,a.create_time,a.content,a.floor_id,a.feedback_id,b.nickname,b.avatar,a.feedback_id as c_id,e.game_name from tab_news_reply a
left join tab_user b on a.u_id=b.id left join tab_news_feedback c on a.feedback_id=c.id left join tab_news d on c.news_id=d.id left join 
 tab_game e on d.game_id=e.id where a.user_id={$user_id}");
        foreach ($new_reply as $key=>$value)
        {
            $new_reply[$key]['game_name']=$value['game_name']?$value['game_name']:'';
            if (!empty($value['avatar'])) {
                $new_reply[$key]['avatar'] = WZAPP . $value['avatar'];
            } else {
                $new_reply[$key]['avatar'] = '';
            }
            $new_reply[$key]['type']=2;
            if($value['floor_id']==0)
            {
                $result = $this->db->field("content")->table("tab_news_feedback")->where(" id = {$value['feedback_id']}")->find();
            }
            else{
                $result = $this->db->field("*")->table("tab_news_reply")->where(" id = {$value['floor_id']}")->find();
            }
            $new_reply[$key]['ycontent']=$result['content'];
            unset($new_reply[$key]['floor_id']);
            unset($new_reply[$key]['feedback_id']);
        }
        //游戏评论回复通知
        $game_reply=$this->db->action("select a.u_id as id,a.create_time,a.content,a.floor_id,a.feedback_id,b.nickname,b.avatar,a.feedback_id as c_id,d.game_name from tab_game_reply a
left join tab_user b on a.u_id=b.id left join tab_game_feedback c on a.feedback_id=c.id left join tab_game d on c.game_id=d.id  where a.user_id={$user_id}");
        foreach ($game_reply as $key=>$value)
        {
            $game_reply[$key]['game_name']=$value['game_name']?$value['game_name']:'';
            if (!empty($value['avatar'])) {
                $game_reply[$key]['avatar'] = WZAPP . $value['avatar'];
            } else {
                $game_reply[$key]['avatar'] = '';
            }
            $game_reply[$key]['type']=3;
            if($value['floor_id']==0)
            {
                $result = $this->db->field("content")->table("tab_game_feedback")->where(" id = {$value['feedback_id']}")->find();
            }
            else{
                $result = $this->db->field("*")->table("tab_game_reply")->where(" id = {$value['floor_id']}")->find();
            }
            $game_reply[$key]['ycontent']=$result['content'];
            unset($game_reply[$key]['floor_id']);
            unset($game_reply[$key]['feedback_id']);
        }
        $data = array_merge($group_feedback,$group_reply,$new_reply,$game_reply);
        $data=$this->test($data);
        $data = array_slice($data,$start,$showPage);
        $this->ajax_return(0,$data);
    }
    //系统通知
    public function systemlistAction(){
        $user_id=$_GET['id'];
        if(empty($_GET['id']))
        {
            $user_id=0;
        }
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "10" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $data=$this->db->action("select * from tab_system_notify order by create_time desc limit {$start},{$showPage}");
        foreach ($data as $key=>$value)
        {
            $result = $this->db->field("*")->table("tab_user_notice")->where("u_id={$user_id} and notice_id={$value['id']}")->find();
            if(!empty($result))
            {
                $data[$key]['status']=1;
            }
        }
        $this->ajax_return(0,$data);
    }
    //读取通知
    public function readnoticeAction(){
        $user_id=$_GET['id'];
        $notice_id=$_GET['notice_id'];
        $result = $this->db->field("*")->table("tab_user_notice")->where("u_id={$user_id} and notice_id={$notice_id}")->find();
        $notify = $this->db->field("*")->table("tab_system_notify")->where("id={$notice_id}")->find();
        unset($notify['status']);
        if(empty($result))
        {
            $data['u_id']=$user_id;
            $data['notice_id']=$notice_id;
            $bool=$this->db->action( $this->db->insertSql("user_notice",$data));
            if($bool)
            {
                $this->ajax_return(0,$notify);
            }
            else{
                $this->ajax_return(500);
            }
        }
        else{
            $this->ajax_return(0,$notify);
        }
    }
    //APk线上地址
    public function apkuploadAction(){
        $type =  empty($_GET['type'])?"pgyer":$_GET['type'];
        $arrData = $this->db
            ->field("update_address")
            ->table("tab_setting")
            ->where("type = '{$type}'")
            ->order("id desc")
            ->find();
        if(!empty($arrData)){
            $addr = "http://".$_SERVER['SERVER_NAME'].$arrData['update_address'];
            header("Content-type:application/vnd.android.package-archive");
            $filename = "wanzhuan_".$type.".apk";
            header("Content-Disposition:attachment;filename = ".$filename);
            header("Accept-ranges:bytes");
            header("Accept-length:".filesize($addr));
            readfile($addr);
            exit;
        }else{
            exit("暂无下载地址");
        }
    }
}
