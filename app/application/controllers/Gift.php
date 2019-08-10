<?php

class GiftController extends BaseController  {
      //福利
    public function indexAction()
    {
        $user_id=get('id')?get('id'):0;
        //推荐游戏
//        $recommend =$this->db->action("select a.cover,a.id from tab_game a  where a.game_status=1 and a.recommend_status=1 order by a.sort desc limit 4");
//        foreach ($recommend as $key=>$value)
//        {
//            $recommend[$key]['cover']=$this->get_cover($value['cover'],'path');
//        }
        $banner =$this->db->action("select a.game_name,a.game_type_name,a.cover,a.icon,a.introduction,a.id,a.features,a.game_address,
      a.version,c.file_size as game_size,a.and_dow_address,a.version_num,b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id 
      left join tab_game_source c on a.id=c.game_id where a.game_status=1 
    and a.recommend_status=3 order by a.create_time desc limit 0,5");
        foreach ($banner as $key=>$value)
        {
            $banner[$key]['icon']=$this->get_cover($value['icon'],'path');
            $banner[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $banner[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        //游戏礼包
        $giftbag=$this->db->action("SELECT tab_giftbag.id as gift_id,tab_giftbag.game_id,tab_giftbag.game_name,tab_giftbag.start_time,tab_giftbag.end_time,
tab_giftbag.giftbag_name,tab_giftbag.desribe,tab_game.icon,tab_gift_position.start_date,tab_gift_position.end_date FROM `tab_giftbag` 
INNER JOIN tab_game on tab_giftbag.game_id = tab_game.id inner join tab_gift_position  on tab_giftbag.id=tab_gift_position.gift_id
WHERE `game_status` = 1 and tab_giftbag.status=1   and position like '%4%' GROUP BY tab_giftbag.game_id ORDER BY tab_giftbag.id desc LIMIT 6 ");
        foreach ($giftbag as $key=>$value)
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
                    if($is_order==0)
                    {
                        $novice=$record['novice'];
                    }
                }
            }
            $giftbag[$key]['novice']=$novice;
            $giftbag[$key]['is_order']=$is_order;
            $giftbag[$key]['isreceive']=$isreceive;
        }
        $data['recommend']=$banner;
        $data['gift_bag']=$giftbag;
        $this->ajax_return(0,$data);

    }
    //领取礼包
    public function getgiftAction()
    {
        $u_id=get('id');
        $gift_id=get('gift_id');
        if($u_id==0){
           $this->ajax_return(1201);
        }
        $record=$this->db->field("*")->table("tab_gift_record")->where("user_id = {$u_id} and gift_id={$gift_id}")->find();
        if(!empty($record))
        {
            $this->ajax_return(1202);
        }
        else{
            $ji=$this->db->field("novice,game_id,game_name,giftbag_name")->table("tab_giftbag")->where("id={$gift_id}")->find();
            if(empty($ji['novice'])){
                $this->ajax_return(1203);
            }else{
                $at=explode(",",$ji['novice']);
                $add['game_id']=$ji['game_id'];
                $add['game_name']=$ji['game_name'];
                $add['gift_id']=$gift_id;
                $add['gift_name']=$ji['giftbag_name'];
                $add['status']=0;
                $add['novice']=$at[0];
                $add['user_id'] =$u_id;
                $add['create_time']=strtotime(date('Y-m-d h:i:s',time()));
                $this->db->action($this->db->insertSql("gift_record",$add));
                $new=$at;
                if(in_array($new[0],$new)){
                    $sd=array_search($new[0],$new);
                    unset($new[$sd]);
                }
                $act['novice']=implode(",", $new);
                $this->db->action($this->db->updateSql("giftbag",$act,"id = {$gift_id}"));
                $data['novice']=$at[0];
                $this->ajax_return(0,$data);
            }
        }
    }
    //礼包详情
    public function giftdetailAction(){
        $gift_id=get('gift_id');
        $user_id=get('id')?get('id'):0;
       $onedata=$this->db->action("select a.id as gift_id,a.game_id,a.game_name,a.giftbag_name,a.desribe,a.start_time,a.end_time,a.digest,b.icon,b.game_type_id
 ,c.start_date,c.end_date from
        tab_giftbag a left join tab_game b on a.game_id=b.id left join tab_gift_position c on a.id=c.gift_id where a.id={$gift_id}");
        if(empty($onedata))
        {
            $this->ajax_return(1206);
        }
        //游戏推荐
        $game=$this->db->action("select a.game_name,a.cover,a.introduction,a.features,a.game_type_name,a.icon,a.id,a.game_address,a.version,c.file_size as game_size,a.and_dow_address,a.version_num,
b.apk_pck_name from tab_game a left join tab_game_set b on a.id=b.game_id  left join tab_game_source c on a.id=c.game_id  where  a.game_type_id={$onedata[0]['game_type_id']} 
and a.game_status=1 and a.recommend_status=2 order by a.sort desc limit 8");
        foreach ($game as $key=>$value)
        {
            $game[$key]['icon']=$this->get_cover($value['icon'],'path');
            $game[$key]['cover']=$this->get_cover($value['cover'],'path');
            if(!empty($value['and_dow_address'])) {
                $game[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
            }
        }
        $onedata[0]['novice_count']=intval($this->db->zscount("gift_record","*","total","gift_id={$onedata[0]['gift_id']}"));
        $ji=$this->db->field("novice")->table("tab_giftbag")->where("id={$onedata[0]['gift_id']}")->find();
        $at=explode(",",$ji['novice']);
        $onedata[0]['novice_total']=intval($onedata[0]['novice_count']+count($at));
        $isreceive=0;
        $novice='';
        $is_order=0;//不是预约礼包
        $onedata[0]['start_time']=date("Y年m月d号",$onedata[0]['start_time']);
        $onedata[0]['end_time']=date("Y年m月d号",$onedata[0]['end_time']);
        if(!empty( $onedata[0]['start_date']))
        {
            $onedata[0]['start_time']=date("Y年m月d号",$onedata[0]['start_date']);
            $onedata[0]['end_time']=date("Y年m月d号",$onedata[0]['end_date']);
            $is_order=1;//是预约礼包
        }
        if(!empty($user_id))
        {
            $record=$this->db->field("*")->table("tab_gift_record")->where("gift_id = {$gift_id} and user_id={$user_id}")->find();
            if($record)
            {
                $isreceive=1;
                if($is_order==0)
                {
                    $novice=$record['novice'];
                }
            }
        }
        $onedata[0]['isreceive']=$isreceive;
        $onedata[0]['novice']=$novice;
        $onedata[0]['is_order']=$is_order;
        $data=$onedata[0];
        $data['icon']=$this->get_cover($data['icon'],'path');
        $data['game']=$game;
        $this->ajax_return(0,$data);
    }
    //新手任务
    public function newcomerAction(){
        $uid=get('id')?get('id'):0;
        $data['isphone']=0;
        $data['isweixin']=0;
        $data['idcard']=0;
        $data['iscircle']=0;
        $data['isqq']=0;
        if(!empty($uid))
       {
            $user=$this->db->field("*")->table("tab_user")->where("id = {$uid}")->find();
            if(!empty($user['phone']))
            {
                $data['isphone']=1;
                $phone=$this->db->field("*")->table("tab_user_bp")->where("user = {$uid} and bp_type='绑定手机'")->find();
                if(!empty($phone) and $phone['status']==1)
                {
                    $data['isphone']=2;
                }
            }
            if(!empty($user['idcard']))
            {
                $data['idcard']=1;
                $card=$this->db->field("*")->table("tab_user_bp")->where("user = {$uid} and bp_type='实名认证'")->find();
                if(!empty($card)and $card['status']==1 )
                {
                    $data['idcard']=2;
                }
            }
           if(!empty($user['wechat']))
           {
               $data['isweixin']=1;
               $wechat=$this->db->field("*")->table("tab_user_bp")->where("user = {$uid} and bp_type='绑定微信'")->find();
               if(!empty($wechat) and $wechat['status']==1)
               {
                   $data['isweixin']=2;
               }
           }

           $circle=$this->db->field("*")->table("tab_user_bp")->where("user = {$uid} and bp_type='分享多肉APP到朋友圈'")->find();
           if(!empty($circle)) {
               if ($circle['status'] ==0) {
                   $data['iscircle'] = 1;
               } else {
                   $data['iscircle'] = 2;
               }
           }
           $qq=$this->db->field("*")->table("tab_user_bp")->where("user = {$uid} and bp_type='分享多肉到QQ空间'")->find();
           if(!empty($qq)) {
               if ($qq['status'] ==0) {
                   $data['isqq'] = 1;
               } else {
                   $data['isqq'] = 2;
               }
           }
        }
        $this->ajax_return(0,$data);
    }
    //新手任务领积分
    public function getpointsAction(){
        $user=get('id')?get('id'):0;
        $status=get('status')?get('status'):0;
        if(empty($user))
        {
            $this->ajax_return(1013);
        }
        $type=get('type');//1绑定手机2绑定微信3实名认证4分享APP到朋友圈5分享到QQ空间
        if($type==1)
        {
            $bp_type="绑定手机";
            $bp='+20积分';
            $jf=20;
        }
        elseif ($type==2)
        {
            $bp_type="绑定微信";
            $bp='+20积分';
            $jf=20;
        }
        elseif ($type==3)
        {
            $bp_type="实名认证";
            $bp='+20积分';
            $jf=20;
        }
        elseif ($type==4)
        {
            $bp_type="分享多肉APP到朋友圈";
            $bp='+30积分';
            $jf=30;
        }
        else
        {
            $bp_type="分享多肉到QQ空间";
            $bp='+30积分';
            $jf=30;
        }
        $ptime = date("Y-m-d",time()); //时间戳转为日期
        $result =  $this->db->field("*")->table("tab_user_bp")->where(" user = {$user} and bp_type='{$bp_type}'")->find();
        $user_result =  $this->db->field("*")->table("tab_user")->where(" id = {$user} ")->findobj();
        if($status==0)
        {
            $zs_user_bp['user'] = $user;
            $zs_user_bp['bp'] = $bp;
            $zs_user_bp['optime'] = $ptime;
            $zs_user_bp['bp_type'] = $bp_type;
            $zs_user_bp['type'] = 1;
            $zs_user_bp['status'] = $status;
            if(empty($result)) {
                $this->db->action($this->db->insertSql("user_bp", $zs_user_bp));
            }
        }
        else{
            $zs_user['points'] = (int)$user_result->points + $jf;
            $this->db->action($this->db->updateSql("user", $zs_user, "id = {$user}"));
            if(!empty($result)) {
                $zs_user_bp['status'] = $status;
                $this->db->action($this->db->updateSql("user_bp", $zs_user_bp, "id = {$result['id']}"));
            }
            else{
                $zs_user_bp['user'] = $user;
                $zs_user_bp['bp'] = $bp;
                $zs_user_bp['optime'] = $ptime;
                $zs_user_bp['bp_type'] = $bp_type;
                $zs_user_bp['type'] = 1;
                $zs_user_bp['status'] = $status;
                $this->db->action($this->db->insertSql("user_bp", $zs_user_bp));
            }
        }
        $this->ajax_return(0);


    }
    //礼包列表
    public function giftbagAction(){
        $uid=get('id');
        $type=get('type');//1推荐2热门3特殊4豪华
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "5" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $giftbag=$this->db->action("SELECT tab_giftbag.id as gift_id,tab_giftbag.game_id,tab_giftbag.game_name,tab_giftbag.start_time,tab_giftbag.end_time,
tab_giftbag.giftbag_name,tab_giftbag.desribe,tab_game.icon ,tab_gift_position.start_date,tab_gift_position.end_date FROM `tab_giftbag` 
INNER JOIN tab_game on tab_giftbag.game_id = tab_game.id inner join tab_gift_position  on tab_giftbag.id=tab_gift_position.gift_id
WHERE `game_status` = 1 and tab_giftbag.status=1  and position like '%{$type}%'  ORDER BY tab_giftbag.id desc limit {$start},{$showPage}");
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
        $this->ajax_return(0,$giftbag);
    }
    //代金券
    public function couponsAction(){
        $user_id=get('id')?get('id'):0;
        $nowtime=time();
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "6" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
         $data=$this->db->action("select id as coupon_id,userule,amount,credit,start_time,end_time from tab_coupons where end_time >='{$nowtime}' and num>0 order by start_time asc limit {$start},{$showPage}");
        foreach ($data as $key=>$value)
        {
            $data[$key]['start_time']=date("Y.m.d",$value['start_time']);
            $data[$key]['end_time']=date("Y.m.d",$value['end_time']);
            $isreceive="0";
            if(!empty($user_id)) {
                $click = $this->db->field("*")->table("tab_user_coupon")->where("coupon_id = {$value['coupon_id']} and user_id={$user_id}")->find();
                if (!empty($click)) {
                    $isreceive = "1";
                }
            }
            $data[$key]['isreceive']=$isreceive;
        }
        $this->ajax_return(0,$data);
    }
    //兑换详情
    public function coupon_detailAction(){
        $coupon_id=get('coupon_id');
        if(empty($coupon_id))
        {
            $this->ajax_return(102);
        }
        $data = $this->db->field("id as coupon_id,pic,num,amount,apply_game,start_time,end_time,content,point")->table("tab_coupons")->where("id = {$coupon_id}")->find();
        $data['start_time']=date("Y-m-d",$data['start_time']);
        $data['end_time']=date("Y-m-d",$data['end_time']);
        $data['pic']=WZAPP.$data['pic'];
        $data['num']=intval($data['num']);
        $data['coupon_count']=intval($this->db->zscount("user_coupon","*","total","coupon_id={$coupon_id}"));
        $this->ajax_return(0,$data);
    }
    //可兑换
    public function exchange_couponAction()
    {
        $reqdata=get();
        $coupon_id= $reqdata['coupon_id'];
        $uid = $reqdata['id'];
        $point=$reqdata['point'];
        if(empty($uid))
        {
            $this->ajax_return(1013);
        }
        $add['user_id']=$uid;
        $add['coupon_id']=$coupon_id;
        $add['create_time']=time();
        if($this->db->action($this->db->insertSql("user_coupon",$add))){
            //积分系统
            $this->user_bp($uid,"-".$point."积分",time(),"兑换代金券",-$point,1);
            $this->ajax_return(0);
        }
        else{
            $this->ajax_return(500);
        }
    }
    //领积分
    public function getintegralAction(){
        $user_id=get('id')?get('id'):0;
        if(empty($user_id))
        {
            $this->ajax_return(1013);
        }
        $user=$this->db->field("vip_level")->table("tab_user")->where("id = {$user_id} ")->find();
        $vip_level=$user['vip_level'];
        $task_id=get('task_id');
        $type=get('type');
        $status=get('status');
        if($type==1)//下载
        {
           $task=$this->db->field("game_id,game_name,is_member,point,v1,v2,v3,v4,v5,v6,v7,v8")->table("tab_download_point")->where("id = {$task_id} ")->find();
            $point=$task['point'];
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
            $this->game_bp($user_id, "+".$point."积分", time(), "下载".$task['game_name'], $point, 2,$status,$task['game_id']);
        }
        elseif($type==2){//充值
            $task=$this->db->field("game_id,game_name,is_member,point,v1,v2,v3,v4,v5,v6,v7,v8")->table("tab_recharge_point")->where("id = {$task_id} ")->find();
            $point=$task['point'];
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
            $this->game_bp($user_id, "+".$point."积分", time(), "充值".$task['game_name'], $point, 3,$status,$task['game_id']);
        }
        else{//每日分享
            $task=$this->db->field("id,is_member,point,content,v1,v2,v3,v4,v5,v6,v7,v8")->table("tab_share_point")->where("id = {$task_id} ")->find();
            $point=$task['point'];
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
            $this->game_bp($user_id, "+".$point."积分", time(), $task['content'], $point, 4,$status,$task['id']);
        }

    }
    //充值游戏、下载游戏、每日分享
    public function tasklistAction(){
        $user_id=get('id')?get('id'):0;
        $type=get('type');//1下载游戏2充值游戏3每日分享
        $today=date("Y-m-d");
        $time=strtotime(date("Y-m-d 00:00:00"));
        switch ($type){
            case 1:
                $sql="select a.id,a.game_id,a.point,a.start_time,a.end_time,a.icon,a.game_name,b.game_address,b.cover,b.introduction,b.features,
     b.version,d.file_size as game_size,b.and_dow_address,b.version_num,c.apk_pck_name from tab_download_point a left join tab_game b 
     on a.game_id=b.id left join tab_game_set c on b.id=c.game_id left join tab_game_source d on b.id=d.game_id where a.start_time<=$time and a.end_time>=$time";
                break;
            case 2:
                $sql="select a.id,a.game_id,a.amount,a.point,a.start_time,a.end_time,a.icon,a.game_name from tab_recharge_point a where a.start_time<=$time and a.end_time>=$time";
                break;
            case 3:
                $sql="select a.id,a.content,a.point,a.remark,a.icon from tab_share_point a";
                break;

        }
        $result=$this->db->action($sql);
        foreach ($result as $key=>$value)
        {
            if($type==1)
            {
                $where="user = {$user_id} and game_id={$value['game_id']} and type=2";
                $result[$key]['end_time']=date("Y-m-d H:i",$value['end_time']);
                $result[$key]['cover']=$this->get_cover($value['cover'],'path');
                if(!empty($value['and_dow_address'])) {
                    $result[$key]['and_dow_address'] = ZSWH . substr($value['and_dow_address'], 1);
                }
            }
            elseif ($type==2)
            {
                $where="user = {$user_id} and game_id={$value['game_id']} and type=3";
                $result[$key]['end_time']=date("Y-m-d H:i",$value['end_time']);
            }
            else{
                $where="user = {$user_id} and share_id={$value['id']} and optime='{$today}'";
            }
            $result[$key]['is_point']=0;
            $data = $this->db->field("*")->table("tab_user_bp")->where($where)->find();
            if(!empty($data))
            {
                if($data['status']==0)
                {
                    if($type==1)
                    {
                        $game['user_id']=$user_id;
                        $game['game_id']=$value['game_id'];
                        $download = $this->db->field("*")->table("tab_download_game")->where("user_id={$user_id} and game_id={$value['game_id']}")->find();
                        if(!empty($download))
                        {
                            $result[$key]['is_point']=1;
                        }
                    }
                    else{
                        $result[$key]['is_point']=1;
                    }

                }
                else{
                    $result[$key]['is_point']=2;
                }
            }
        }
        $this->ajax_return(0,$result);

    }
    //我的积分
    public function mypointAction(){
        $user_id=get('id')?get('id'):0;
        $user=$this->db->field("points,balance")->table("tab_user")->where("id = {$user_id} ")->find();
        if(empty($user_id))
        {
            $user['points']=0;
        }
        $this->ajax_return(0,$user);
    }
    //每日签到展示
    public function checkinAction(){
        $m = date("m"); //当前年
        $y = date("Y"); //当前年
        $days = date("t",mktime(0,0,0,$m,1,$y));//获取当月的天数
        if(!isset($_GET['id'])){
            $this->ajax_return(1013);
        }
        $res=$this->db->field("status_num")->table("tab_user_make")->where("user_id = {$_GET['id']}")->find();
        if(!empty($res)){
            $num = $res['status_num'];
        }else{
            //生成补签次数
            $num = get_user_checkin_num($_GET['id']);
            $this->db->action($this->db->insertSql('user_make',["user_id"=>$_GET['id'],"status_num"=>$num]));
        }

        $field = "id as checkin_id,icon as checkin_icon,text as checkin_text,create_time as checkin_create_time,type as checkin_type,info as checkin_info,game_id as checkin_game_id,gift_id as checkin_gift_id";
        $sql = "SELECT {$field} FROM tab_checkin WHERE create_time BETWEEN '{$y}-{$m}-1' AND '{$y}-{$m}-{$days}'";
        $arrData = $this->db->action($sql);
        foreach ($arrData as $k=>$v){
            $res=$this->db->field("*")->table("tab_user_checkin")->where("u_id = {$_GET['id']} and checkin_id={$v['checkin_id']} and create_time = '{$v['checkin_create_time']}'")->find();
            $arrData[$k]['is_checkin'] = !empty($res)? 1 : 0;
        }
        if(!empty($arrData)){
            $data['checkin_list'] = $arrData;
            $data['checkin_num'] = $num;
            $this->ajax_return(0,$data);
        }else {
            $data['checkin_list'] = [];
            $data['checkin_num'] = 0;
            $this->ajax_return(0, $data);
        }
    }
    //每日签到(数据没有同步待开发。。。。。)
    public  function usercheckinAction(){
        if(!isset($_GET['id'])){
            $this->ajax_return(1013);
        }
        if(!isset($_GET['checkin_id'])){
            $this->ajax_return(1210);
        }
        $data['u_id'] = $_GET['id'];
        //每日签到数据
        $data['checkin_id'] = $_GET['checkin_id'];
        $data['create_time'] = isset($_GET['checkin_date'])?$_GET['checkin_date']:date("Y-m-d");
        //固定日签到
        $userinfo=$this->db->field("*")->table("tab_user")->where(" id = {$data['u_id']} ")->find();
        $vip_level = $userinfo['vip_level'];
        $fixed_checkin_info=$this->db->field("*")->table("tab_user_loca_level")->where(" level = {$vip_level} ")->find();
        if(!empty($fixed_checkin_info)){
            $data['fixed_checkin_id'] = $fixed_checkin_info['id'];
        }
        //连续签到
        $startday = date('Y-m-01', strtotime($data['create_time'])); //签到日对应当月1号
        $totaltime = "create_time BETWEEN '{$startday}' AND '{$data['create_time']}'"; //范围查询时间
        $count = $this->db->zscount("user_checkin","*","total"," u_id = {$data['u_id']} AND ({$totaltime}) "); //统计次数
        $lian_checkin_info = $this->db->field("*")->table("tab_checkin_continuity")->where(" enddate = {$data['create_time']} ")->find();
        if(!empty($lian_checkin_info)){
            if($count == $lian_checkin_info['num']){
                $data['link_checkin_id'] = $lian_checkin_info['id'];
            }
        }
        //生成数据
        $bool = $this->db->action($this->db->insertSql("user_checkin",$data));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    public  function makecheckinAction(){
        if(!isset($_GET['id'])){
            $this->ajax_return(1013);
        }
        if(!isset($_GET['checkin_id'])){
            $this->ajax_return(1210);
        }
        $data['u_id'] = $_GET['id'];
        $checkin_num=$this->db->field("status_num")->table("tab_user_make")->where("user_id = {$_GET['id']}")->find();
        if($checkin_num['status_num'] <= 0){
            $this->ajax_return(1211);
        }
        //每日签到数据
        $data['checkin_id'] = $_GET['checkin_id'];
        $data['create_time'] = isset($_GET['checkin_date'])?$_GET['checkin_date']:date("Y-m-d");
        //固定日签到
        $userinfo=$this->db->field("*")->table("tab_user")->where(" id = {$data['u_id']} ")->find();
        $vip_level = $userinfo['vip_level'];
        $fixed_checkin_info=$this->db->field("*")->table("tab_user_loca_level")->where(" level = {$vip_level} ")->find();
        if(!empty($fixed_checkin_info)){
            $data['fixed_checkin_id'] = $fixed_checkin_info['id'];
        }
        //连续签到
        $startday = date('Y-m-01', strtotime($data['create_time'])); //签到日对应当月1号
        $totaltime = "create_time BETWEEN '{$startday}' AND '{$data['create_time']}'"; //范围查询时间
        $count = $this->db->zscount("user_checkin","*","total"," u_id = {$data['u_id']} AND ({$totaltime}) "); //统计次数
        $lian_checkin_info = $this->db->field("*")->table("tab_checkin_continuity")->where(" enddate = {$data['create_time']} ")->find();
        if(!empty($lian_checkin_info)){
            if($count == $lian_checkin_info['num']){
                $data['link_checkin_id'] = $lian_checkin_info['id'];
            }
        }
        //生成数据
        $bool = $this->db->action($this->db->insertSql("user_checkin",$data));
        $data2['status_num'] = (int)$checkin_num['status_num'] -1;
        $this->db->action($this->db->updateSql("user_make",$data2,"user_id = {$_GET['id']}"));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //补签次数查询
    public function makenumAction(){
        if(!isset($_GET['level'])){
            $this->ajax_return(102);
        }else{
            $arrData = $this->db->field("level,num")->table("tab_user_sign_num")->where("level = {$_GET['level']}")->find();
            $this->ajax_return(0,$arrData);
        }
    }
    //月自动发放
    public function monthAction(){
        $id = $this->input['id'];
        $month_time = $this->input['month_time'];
        $arrData = $this->db->field("*")->table("tab_ios_user")->where("id = {$id}")->find();
        if(empty($arrData)){
            $this->ajax_return(1013);
        }else{
            $build_time = $this->getMonthMyActionAndEnd($month_time);
            $start_time = $build_time['week_start'];
            $end_time = $build_time['week_end'];
            $totaltime = "create_time BETWEEN '{$start_time}' AND '{$end_time}'"; //范围查询时间
            $checkin_info = $this->db->field("*")->table("tab_user_checkin")->where(" u_id = {$id} AND ({$totaltime}) ADN member_week_id <> 0")->find();
            if(!empty($checkin_info)){
                $this->ajax_return(1202);
            }else{
                $level_week = $this->db->field("*")->table("tab_user_level")->where(" level = {$arrData['vip_level']} ")->find();
                if(!empty($level_week)){
                    $result = $this->db->field("*")->table("tab_user_checkin")->where(" u_id = {$id} AND create_time = '{$month_time}' ADN member_week_id = 0")->find();
                    $data['u_id'] = $id;
                    $data['create_time'] = $month_time;
                    $data['member_month_id'] = $level_week['id'];
                    if(!empty($result)){
                        $bool = $this->db->action($this->db->updateSql("user_checkin",$data," u_id = {$id} AND create_time = '{$month_time}' ADN member_week_id = 0 "));
                    }else{
                        $bool = $this->db->action($this->db->insertSql("user_checkin",$data));
                    }
                    if($bool){
                        $this->ajax_return(0);
                    }else{
                        $this->ajax_return(500);
                    }
                }else{
                    $this->ajax_return(1206);
                }
            }
        }
    }
    //周自动发放
    public function weekAction(){
        $id = $this->input['id'];
        $week_time = $this->input['week_time'];
        $arrData = $this->db->field("*")->table("tab_ios_user")->where("id = {$id}")->find();
        if(empty($arrData)){
            $this->ajax_return(1013);
        }else{
            $build_time = $this->getWeekMyActionAndEnd($week_time);
            $start_time = $build_time['week_start'];
            $end_time = $build_time['week_end'];
            $totaltime = "create_time BETWEEN '{$start_time}' AND '{$end_time}'"; //范围查询时间
            $checkin_info = $this->db->field("*")->table("tab_user_checkin")->where(" u_id = {$id} AND ({$totaltime}) ADN member_week_id <> 0")->find();
            if(!empty($checkin_info)){
                $this->ajax_return(1202);
            }else{
                $level_week = $this->db->field("*")->table("tab_user_level_week")->where(" level = {$arrData['vip_level']} ")->find();
                if(!empty($level_week)){
                    $result = $this->db->field("*")->table("tab_user_checkin")->where(" u_id = {$id} AND create_time = '{$week_time}' ADN member_week_id = 0")->find();
                    $data['u_id'] = $id;
                    $data['create_time'] = $week_time;
                    $data['member_week_id'] = $level_week['id'];
                    if(!empty($result)){
                        $bool = $this->db->action($this->db->updateSql("user_checkin",$data," u_id = {$id} AND create_time = '{$week_time}' ADN member_week_id = 0 "));
                    }else{
                        $bool = $this->db->action($this->db->insertSql("user_checkin",$data));
                    }
                    if($bool){
                        $this->ajax_return(0);
                    }else{
                        $this->ajax_return(500);
                    }
                }else{
                    $this->ajax_return(1206);
                }
            }
        }

    }

    public function getWeekMyActionAndEnd($time = '', $first = 1)
    {
        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('N', strtotime($time));
        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start = date('Y-m-d', strtotime("$time -" . ($w ? $w - $first : 6) . ' days'));
        //本周结束日期
        $week_end = date('Y-m-d', strtotime("$week_start +6 days"));
        return array("week_start" => $week_start, "week_end" => $week_end);
    }
    public function getMonthMyActionAndEnd($time = '')
    {
        //获取当前月天数
        $t = date('t', strtotime($time));
        //对应当月1号
        $week_start = date('Y-m-01', strtotime($time));
        //本月结束日期
        $week_end = date("Y-m-$t", strtotime($time));
        return array("month_start" => $week_start, "month_end" => $week_end);
    }
}
