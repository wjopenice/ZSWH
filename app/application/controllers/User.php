<?php
use Helper\Page;
use Helper\Idcard;
class UserController extends BaseController{
    public $u_id;
    public function init(){
        parent::init();
       if(empty($this->input['id'])){
            $this->ajax_return(1013);
        }else{
            $this->u_id = $this->input['id'];
        }
    }
    //app我的
    public function indexAction(){
        $userdata = $this->db->field("*")->table("tab_user")->where("id = {$this->u_id}")->find();
        if(!empty($userdata)){
            $userdata['follow_num'] = $this->db->zscount("user_follow","*","total","user_id = {$this->u_id} ");
            $userdata['fans_num'] = $this->db->zscount("user_fans","*","total","user_id = {$this->u_id} ");
            $userdata['is_wechat'] = !empty($userdata['wechat'])?1:0;
            $userdata['is_qq'] = !empty($userdata['qq'])?1:0;
            $userdata['sex'] = ($userdata['sex'] == 0)?"男":"女";
            if( !is_null($userdata['idcard']) && !is_null($userdata['real_name']) ){
               $userdata['is_id_card']  = 1;
            }else{
               $userdata['is_id_card']  = 0;
            }
            $this->ajax_return(0,$userdata);
        }else{
            $this->ajax_return(1013);
        }
    }
    //app编辑资料显示
    public function editAction(){
        $userdata = $this->db->field("id,account,nickname,avatar,sex,phone,u_sign")->table("tab_user")->where("id = {$this->u_id}")->find();
        if(!empty($userdata)){
            $userdata['is_wechat'] = 0;
            $userdata['is_qq'] = 0;
            $userdata['is_id_card'] = 0;
            $this->ajax_return(0,$userdata);
        }else{
            $this->ajax_return(1013);
        }
    }
    //app用户积分
    public function bpAction(){
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $result = $this->db->action("SELECT bp,optime,bp_type,game_id,share_id,status FROM tab_user_bp WHERE user = {$this->u_id} ORDER BY id DESC LIMIT {$start},{$showPage} ");
        $data=[];
        if(!empty($result)){
            foreach ($result as $key=>$value)
            {
                if($value['game_id']!=0 || $value['share_id']!=0)
                {
                    if($value['status']==1)
                    {
                        $data[]=$value;
                    }
                }
                else{
                    $data[]=$value;
                }
            }
            $this->ajax_return(0,$data);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //实名认证
    public function idcardAction(){
        $idcard= new Idcard();
        $arr['real_name']=$this->input['real_name'];
        $arr['idcard']=$this->input['idcard'];
        $userdata = $this->db->field("*")->table("tab_user")->where("id = {$this->u_id}")->find();
        if(!$idcard->isIdCardValid($arr['idcard'])){
            $this->ajax_return(1014);
        }
        if(!empty($userdata)){
            $bool = $this->db->action($this->db->updateSql("user",$arr," id = {$this->u_id}"));
            if($bool){
                $this->ajax_return(0,['real_name'=>$arr['real_name'],'idcard'=>$arr['idcard']]);
            }else{
                $this->ajax_return(500);
            }
        }else{
            $this->ajax_return(1013);
        }

    }
    //app积分兑换
    public function redeemAction(){
        $amount = (int)$this->input['amount'];
        $result = $this->db->field("id,balance,points")->table("tab_user")->where("id = {$this->u_id}")->find();
        $bp = (int)$result['points'];
        $am = $result['balance'];
        $rbp = 0;
        switch ($amount){
            case 10: $rbp = 300; break;
            case 30: $rbp = 500; break;
            case 60: $rbp = 800; break;
            case 100:$rbp = 1000; break;
            case 150:$rbp = 1200; break;
            case 200:$rbp = 1400; break;
            case 250:$rbp = 1600; break;
            case 350:$rbp = 1800; break;
            case 500:$rbp = 2000; break;
        }
        $data = $bp - $rbp;
        $balance = $amount + $am;
        $vip_level=$this->switch_user_level($balance);
        if( $data >= 0 ){
            $this->db->action($this->db->updateSql("user",['balance'=>$balance,'vip_level'=>$vip_level],"id = {$this->u_id}"));
            $zs_user_bp['user_id'] = $result['id'];
            $zs_user_bp['points'] = "-".$rbp;
            $zs_user_bp['create_time'] = time();
            $zs_user_bp['pay_amount'] = $amount;
            $this->db->action($this->db->insertSql("user_balance", $zs_user_bp));
            $this->user_bp($result['id'], "-".$rbp."积分", time(), "积分兑换", -$rbp, 1);
            $this->ajax_return(0);

        }else{
            $this->ajax_return(1208);
        }
    }
    public function test($person){

        $newArr = array();

        foreach($person as $key=>$v){
            $newArr[$key]['pay_time'] = $v['pay_time'];
        }
        array_multisort($newArr,SORT_DESC,$person);//SORT_DESC为降序，SORT_ASC为升序
       return $person;

    }

    //余额明细
    public function spendAction(){
        //充值,pay_amount充值金额,create_time支付时间
        $data=[];
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $current=ceil($showPage/2);
        $start = ($currentPage - 1) * $showPage;
        $deposit=$this->db->action("select a.pay_amount,a.create_time as pay_time,b.game_name from tab_deposit a left join tab_deposit_game b on a.pay_order_number=b.pay_order_number
where a.user_id={$this->u_id } and a.pay_status=1 order by a.create_time desc LIMIT {$start},{$showPage}");
        foreach ($deposit as $key=>$value)
        {
            $value['game_name'] = "充值平台币";
            $value['pay_time']=date("Y-m-d H:i",$value['pay_time']);
            $value['pay_amount']="+".$value['pay_amount'];

            $data[]=$value;
        }
        //消费
        $spend=$this->db->action("select pay_amount,pay_time,game_name  from tab_spend where user_id={$this->u_id } and pay_status=1  order by pay_time desc LIMIT {$start},{$showPage}");
        foreach ($spend as $key=>$value)
        {
            $value['game_name']="充值".$value['game_name'];
            $value['pay_amount']="-".$value['pay_amount'];
            $value['pay_time']=date("Y-m-d h:i",$value['pay_time']);
            $data[]=$value;
        }
        //积分兑换
        $points=$this->db->action("select pay_amount,create_time as pay_time  from tab_user_balance where user_id={$this->u_id }   order by create_time desc LIMIT {$start},{$showPage}");
        foreach ($points as $key=>$value)
        {
            $value['game_name']="积分兑换平台币";
            $value['pay_amount']="+".$value['pay_amount'];
            $value['pay_time']=date("Y-m-d H:i",$value['pay_time']);
            $data[]=$value;
        }
        $data=$this->test($data);
        $this->ajax_return(0,$data);

    }
    //我的礼包
    public function mygiftbagAction(){
        $type=$this->input['type'];
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $nowtime=time();
        if($type==1)
        {
            $result=$this->db->action("select a.game_id,a.game_name,a.gift_name as giftbag_name,a.status,a.novice,a.create_time,a.gift_id,b.desribe,b.start_time,b.end_time,c.icon from tab_gift_record a left join tab_giftbag b on a.gift_id=b.id  left join tab_game c on b.game_id=c.id
        where a.user_id={$this->u_id} and a.status=0 and b.start_time<={$nowtime} and b.end_time>={$nowtime} order by a.create_time desc LIMIT {$start},{$showPage}");
        }
        else{
            $result=$this->db->action(" select a.game_id,a.game_name,a.gift_name as giftbag_name,a.status,a.novice,a.create_time,a.gift_id,b.desribe,b.start_time,b.end_time,c.icon from tab_gift_record a left join tab_giftbag b on a.gift_id=b.id  left join tab_game c on b.game_id=c.id
        where a.user_id={$this->u_id} and a.status=0 and  b.end_time<{$nowtime} and ($nowtime-b.end_time)<7*24*3600
        order by a.create_time desc LIMIT {$start},{$showPage}");
        }
        foreach ($result as $key=>$value)
        {
            $result[$key]['novice_count']=$this->db->zscount("gift_record","*","total","gift_id={$value['gift_id']}");
            $result[$key]['icon']=$this->get_cover($value['icon'],'path');
            $ji=$this->db->field("novice")->table("tab_giftbag")->where("id={$value['gift_id']}")->find();
            $at=explode(",",$ji['novice']);
            $result[$key]['novice_total']=$result[$key]['novice_count']+count($at);
        }
        $this->ajax_return(0,$result);
    }
    //我的卡券
    public function mycardAction()
    {
        $type = $this->input['type'] ? $this->input['type'] : 1;
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "6" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $nowtime = time();
        if ($type == 1) {
            $result = $this->db->action("select b.id,b.userule,b.amount,b.credit,b.start_time,b.end_time from tab_user_coupon a left 
 join tab_coupons b on a.coupon_id=b.id   where b.end_time >='{$nowtime}' and a.status=0 and a.user_id='{$this->u_id}' order by a.create_time desc LIMIT {$start},{$showPage}");
        } else {
            $result = $this->db->action("select b.id,b.userule,b.amount,b.credit,b.start_time,b.end_time from tab_user_coupon a left 
 join tab_coupons b on a.coupon_id=b.id   where b.end_time <'{$nowtime}' and ($nowtime-b.end_time)<7*24*3600 and a.status=0 and a.user_id='{$this->u_id}' order by a.create_time desc LIMIT {$start},{$showPage}");
        }
        foreach ($result as $key=>$value)
        {
            $result[$key]['start_time']=date("Y.m.d",$value['start_time']);
            $result[$key]['end_time']=date("Y.m.d",$value['end_time']);
        }
        $this->ajax_return(0,$result);
    }

    //我的公共头部
    public function mytopAction(){
        $data=$this->db->field("id,avatar,nickname,u_sign")->table("tab_user")->where("id = {$this->u_id}")->find();
        $data['user_num'] = $this->db->zscount("user_follow","*","total","pf_id = {$this->u_id} ");
        $data['group_type_num'] = $this->db->zscount("group","*","total","user_id = {$this->u_id} ");
        $this->ajax_return(0,$data);
    }
    //我的主页
    public function mygroupAction(){
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $res = $this->db->field("*")->table("tab_group")->where("user_id = {$this->u_id}")->select();
        $indata = [];
        foreach ($res as $key=>$value){
            $indata[] = $value['group_type_id'];
        }
        $str = implode(",",$indata);
        $sql = "
        SELECT A.id as a_id,U.id,U.avatar,U.nickname,A.create_time,A.content,A.pic,A.game_id,A.share_num
        FROM tab_user_group as A 
        INNER JOIN tab_user as U ON A.u_id = U.id
        WHERE A.game_id IN ($str);
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
            $result[$k]['click_num'] = $this->db->zscount("group_click", "*", "total", "group_log_id = {$v['a_id']} ");
            $result[$k]['feedback_num'] = $this->db->zscount("group_feedback", "*", "total", "group_log_id = {$v['a_id']} ");
           // $result[$k]['share_num'] = $this->db->zscount("group_share", "*", "total", "group_log_id = {$v['a_id']} ");
        }
        if(!empty($result)){
            $this->ajax_return(0,$result);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //我的游戏
    public function mygameAction(){
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $res=$this->db->action("select a.game_id from tab_group_type a inner join tab_group b on a.id=b.group_type_id where b.user_id={$this->u_id}");
        $indata = [];
        foreach ($res as $key=>$value){
            $indata[] = $value['game_id'];
        }
        $str = implode(",",$indata);
       // $sql = "SELECT id as game_id,game_name,game_type_name,icon,game_address,and_dow_address,features FROM tab_game WHERE id IN ({$str}) LIMIT {$start},{$showPage}";
        $sql = "SELECT a.id as game_id,a.game_name,a.game_type_name,a.icon,a.game_address,a.and_dow_address,a.features,a.introduction,a.version,
 a.version_num,b.apk_pck_name FROM tab_game a left join tab_game_set b on a.id=b.game_id WHERE a.id IN ({$str}) and a.game_status=1 LIMIT {$start},{$showPage}";
        $result = $this->db->action($sql);
        foreach ($result as $k=>$v){
            $result[$k]['icon']=$this->get_cover($result[$k]['icon'],'path');
            $result[$k]['apk_pck_name'] = apk_pck_name($v['game_id']);
            if(!empty( $result[$k]['and_dow_address'])) {
                $result[$k]['and_dow_address'] = ZSWH . substr( $result[$k]['and_dow_address'], 1);
            }
        }
        if(!empty($result)){
            $this->ajax_return(0,$result);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //我的动态
    public function mydynamicAction(){
        //$this->u_id=187492;
        $currentPage = empty($this->input["page"])?"1":$this->input["page"];
         $showPage = empty($this->input["showpage"])?"10":$this->input["showpage"];
         $start =  ($currentPage-1)*$showPage;
         $sql ="  SELECT A.id as a_id,A.game_id as group_type_id,A.create_time,A.content,A.pic,T.icon,T.game_name  FROM tab_user_group as A 
        INNER JOIN tab_group_type as T ON A.game_id = T.id
    WHERE A.u_id = {$this->u_id}  order by A.create_time asc 
    LIMIT {$start},{$showPage}";
            $group=$this->db->action($sql);
            foreach ($group as $key=>$value)
            {
                $group[$key]['create_time']=date("Y-m-d H:i",$value['create_time']);
                $feedback=$this->db->action("select a.id as feedback_id,a.content ,b.nickname,b.id from tab_group_feedback a left join tab_user b on a.u_id=b.id 
where a.group_log_id={$value['a_id']} order by a.create_time desc limit 3");
                $group[$key]['feedback']=$feedback;
            }

        $this->ajax_return(0,$group);
    }
    //关注
    public function followAction(){
        $currentPage = empty($this->input["page"])?"1":$this->input["page"];
        $showPage = empty($this->input["showpage"])?"10":$this->input["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql = "
        SELECT f.pf_id as fans_id,u.nickname as fans_nickname,u.avatar as fans_avatar,u.u_sign as fans_u_sign 
        FROM tab_user_follow as f
        LEFT JOIN tab_user as u ON u.id = f.pf_id
        WHERE f.user_id = {$this->u_id}
        LIMIT {$start},{$showPage}
        ";
        $data = $this->db->action($sql);
        if(!empty($data)){
            $this->ajax_return(0,$data);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //粉丝
    public function fansAction(){
        $currentPage = empty($this->input["page"])?"1":$this->input["page"];
        $showPage = empty($this->input["showpage"])?"10":$this->input["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $sql = "
        SELECT f.pf_id  as fans_id,u.nickname as fans_nickname,u.avatar as fans_avatar,u.u_sign as fans_u_sign 
        FROM tab_user_fans as f
        INNER JOIN tab_user as u ON u.id = f.pf_id
        WHERE f.user_id={$this->u_id}
        LIMIT {$start},{$showPage}
        ";
        $data = $this->db->action($sql);
        if(!empty($data)){
            $this->ajax_return(0,$data);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //圈友互相关注
    public function addfansAction(){
        $news['user_id'] = $this->u_id;
        $news['pf_id'] = $this->input['pf_id'];
        $arrData = $this->db->field("*")->table("tab_user")->where("id = {$this->u_id}")->find();
        if(!empty($arrData)){
            $result=$this->db->field("*")->table("tab_user_follow")->where("user_id = {$this->u_id} and pf_id = {$this->input['pf_id']}")->find();
            if(!empty($result))
            {
                $bool = $this->db->action($this->db->deleteSql("user_follow","user_id = {$this->u_id} and pf_id = {$this->input['pf_id']}"));
                if($bool){
                    $this->db->action($this->db->deleteSql("user_fans","pf_id = {$this->u_id} and user_id = {$this->input['pf_id']}"));
                    $this->ajax_return(0);
                }else{
                    $this->ajax_return(500);
                }
            }
           else {
               $bool = $this->db->action($this->db->insertSql("user_follow", $news));
               if ($bool) {
                   $fans['user_id'] = $this->input['pf_id'];
                   $fans['pf_id'] = $this->u_id;
                   $this->db->action($this->db->insertSql("user_fans", $fans));
                   $this->ajax_return(0);
               } else {
                   $this->ajax_return(500);
               }
           }
        }else{
            $this->ajax_return(1013);
        }
    }

    //我的订单
    public function myorderAction(){
        $type=$this->input['type'];//0全部1待支付2已支付
        if($type==1)
        {
            $where=" and a.pay_status=0 ";
        }
        elseif ($type==2)
        {
            $where=" and a.pay_status=1 ";
        }
        else{
            $where=" and 1=1 ";
        }
        $data=[];
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $current=ceil($showPage/2);
        $start = ($currentPage - 1) * $showPage;
        $deposit=$this->db->action("select a.id as order_id,a.pay_amount,a.create_time as pay_time,a.pay_status,a.pay_way,b.game_name from tab_deposit a left join tab_deposit_game b on a.pay_order_number=b.pay_order_number
where a.user_id={$this->u_id}".$where."  order by a.create_time desc LIMIT {$start},{$showPage}");
        foreach ($deposit as $key=>$value)
        {
            $value['game_name'] = "充值平台币";
            $value['pay_time']=date("Y-m-d H:i",$value['pay_time']);
            $value['pay_amount']="+".$value['pay_amount'];
            $value['recharge_type']=1;
            $data[]=$value;
        }
        //消费
        $spend=$this->db->action("select a.id as order_id,a.pay_amount,a.pay_time,a.game_name ,a.pay_status,a.pay_way from tab_spend  a where a.user_id={$this->u_id } ".$where."  order by a.pay_time desc LIMIT {$start},{$showPage}");
        foreach ($spend as $key=>$value)
        {
            $value['game_name']="充值".$value['game_name'];
            $value['pay_amount']="-".$value['pay_amount'];
            $value['pay_time']=date("Y-m-d h:i",$value['pay_time']);
            $value['recharge_type']=2;
            $data[]=$value;
        }
        $data=$this->test($data);
        $this->ajax_return(0,$data);
    }
    //订单详情
    public function orderdetailAction(){
        $order_id=$this->input['order_id'];
        $recharge_type=$this->input['type'];
        if($recharge_type==1)
        {
            $result=$this->db->field("pay_order_number,pay_amount,pay_status,pay_way,create_time as pay_time")->table("tab_deposit")->where("id = {$order_id}")->find();
            $result['game_name']="充值平台币";
            $result['pay_amount']="+".$result['pay_amount'];
        }
        else{
            $result=$this->db->field("pay_order_number,pay_amount,pay_status,pay_way,pay_time,game_name")->table("tab_spend")->where("id = {$order_id}")->find();
            $result['game_name']="充值".$result['game_name'];
            $result['pay_amount']="-".$result['pay_amount'];
        }
        $result['pay_time']=date("Y-m-d H:i",$result['pay_time']);
        $this->ajax_return(0,$result);
    }
    //动态
    public function dynamicAction(){
       $pf_id = $this->input['pf_id']?$this->input['pf_id']:0;
        $currentPage = empty($this->input["page"])?"1":$this->input["page"];
        $showPage = empty($this->input["showpage"])?"10":$this->input["showpage"];
        $start =  ($currentPage-1)*$showPage;
        if(!empty($pf_id))
        {
            $is_user_follow = is_user_follows($pf_id,$this->u_id);
            if($is_user_follow==0)
            {
                $showPage=3;
                $start=0;
            }
            $sql ="select a.id as feedback_id,a.group_log_id,a.content,a.create_time,b.nickname ,b.id from tab_group_feedback 
 a left join tab_user b on a.u_id=b.id where a.u_id={$pf_id} order by a.create_time desc limit {$start},{$showPage}";
        }
        else{
            $sql ="select a.id as feedback_id,a.group_log_id,a.content,a.create_time,b.nickname ,b.id from tab_group_feedback 
 a left join tab_user b on a.u_id=b.id where a.u_id={$this->u_id} order by a.create_time desc limit {$start},{$showPage}";
        }
        $feedback=$this->db->action($sql);
        foreach ($feedback as $key=>$value)
        {
            //$feedback[$key]['create_time']=date("Y-m-d",$value['create_time']);
            $sql = "select a.id as reply_id,a.floor_id,a.content,a.create_time as reply_time,b.nickname,b.id from tab_group_reply a 
left join tab_user b on a.u_id=b.id  WHERE a.feedback_id = {$value['feedback_id']}  ORDER BY a.id DESC limit 0,3";
            $replydata = $this->db->action($sql);
            foreach ($replydata as $k => $v) {
                if ($v['floor_id'] == 0) {
                    $replydata[$k]['reply_user'] = $feedback[$key]['nickname'] ? $feedback[$key]['nickname'] : "用户".$feedback[$key]['id'];
                } else {
                    $user = $this->db->action("select a.nickname,a.id from tab_user a left join tab_group_reply b on a.id=b.u_id where b.id={$v['floor_id']}");
                    if (!empty($user)) {
                        $replydata[$k]['reply_user'] = $user[0]['nickname'] ? $user[0]['nickname'] : "用户".$user[0]['id'];
                    }
                }
            }
            $feedback[$key]['replydata'] = $replydata;
        }

        $this->ajax_return(0,$feedback);
    }

    //我的礼包
    public function mygiftAction(){
        $user_id=$this->u_id;
        $currentPage = empty($this->input['page']) ? "1" : $this->input['page'];
        $showPage = empty($this->input["showpage"]) ? "10" : $this->input["showpage"];
        $start =  ($currentPage-1)*$showPage;
        $data=[];
        $gift=$this->db->action("select a.game_name,a.gift_id,a.gift_name,b.desribe,c.icon from tab_gift_record a left join tab_giftbag b
 on a.gift_id=b.id left join tab_game c on b.game_id=c.id where a.user_id={$user_id}");
        foreach ($gift as $key=>$value)
        {
            $icon=$this->get_cover($value['icon'],'path')?$this->get_cover($value['icon'],'path'):'';
            $gift[$key]['title']=$value['gift_name']."-".$value['game_name'];
            $array=array('gift_id'=>$value['gift_id'],'icon'=>$icon,'title'=>$gift[$key]['title'],'desribe'=>$value['desribe']?$value['desribe']:'',"type"=>1);
            $data[]=$array;
        }
        $check=$this->db->action("select checkin_id,link_checkin_id,fixed_checkin_id,member_month_id,member_week_id from tab_user_checkin where u_id={$user_id}");
        foreach($check as $key=>$value)
        {
            //每日签到
            if($value['checkin_id']!=0)
            {
                $checkindata=$this->db->field("*")->table("tab_checkin")->where("id = {$value['checkin_id']}")->find();
                if($checkindata['type']=="积分"){
                    $desribe="积分*".$checkindata['text'];
                }
                elseif ($checkindata['type']=="优惠券")
                {
                    $coupon=$this->db->field("amount")->table("tab_coupons")->where("id = {$checkindata['text']}")->find();
                    $desribe="代金券*".$coupon['amount'];
                }
                elseif ($checkindata['type']=="礼包")
                {
                    $giftbag=$this->db->field("*")->table("tab_giftbag")->where("id = {$checkindata['gift_id']}")->find();
                    $desribe=$giftbag['desribe'];
                }
                $array=array('gift_id'=>$value['checkin_id'],'icon'=>'http://app.zhishengwh.com/public/img/gift/check_day.png',
                    "type"=>2,"desribe"=>$desribe,"title"=>"每日签到礼包");
                $data[]=$array;
            }
            //连续签到礼包
            if($value['link_checkin_id']!=0)
            {
                $content="";
                $link=$this->db->field("*")->table("tab_checkin_continuity")->where("id = {$value['link_checkin_id']}")->find();
                if(!empty($link['gift_id']))
                {
                    $giftbag=$this->db->field("*")->table("tab_giftbag")->where("id = {$link['gift_id']}")->find();
                    $content=$content.$giftbag['desribe'];
                }
                if(!empty($link['coupon_id']))
                {
                    $giftbag=$this->db->field("amount")->table("tab_coupons")->where("id = {$link['coupon_id']}")->find();
                    $content=$content."、代金券*".$giftbag['amount'];;
                }
                if(!empty($link['point']))
                {
                    $content=$content."、"."积分*".$link['point'];
                }
                if(!empty($link['balance']))
                {
                    $content=$content."、"."平台币*".$link['balance'];
                }
                if (strpos($content, '、') == 0)
                {
                    $desribe=mb_substr($content,1);
                }
                else{
                    $desribe=$content;
                }
                $array=array('gift_id'=>$value['link_checkin_id'],'icon'=>'http://app.zhishengwh.com/public/img/gift/checkin.png',
                    "type"=>3,"desribe"=>$desribe,"title"=>"连续签到礼包");
                $data[]=$array;
            }
            //固定日会员礼包
            if($value['fixed_checkin_id']!=0)
            {
                $content="";
                $link=$this->db->field("*")->table("tab_user_loca_level")->where("id = {$value['fixed_checkin_id']}")->find();
                if(!empty($link['gift_id']))
                {
                    $giftbag=$this->db->field("*")->table("tab_giftbag")->where("id = {$link['gift_id']}")->find();
                    $content=$content.$giftbag['desribe'];
                }
                if(!empty($link['coupon_id']))
                {
                    $giftbag=$this->db->field("amount")->table("tab_coupons")->where("id = {$link['coupon_id']}")->find();
                    $content=$content."、代金券*".$giftbag['amount'];;
                }
                if(!empty($link['point']))
                {
                    $content=$content."、"."积分*".$link['point'];
                }
                if(!empty($link['balance']))
                {
                    $content=$content."、"."平台币*".$link['balance'];
                }
                if (strpos($content, '、') == 0)
                {
                    $desribe=mb_substr($content,1);
                }
                else{
                    $desribe=$content;
                }
                $array=array('gift_id'=>$value['fixed_checkin_id'],'icon'=>'http://app.zhishengwh.com/public/img/gift/member_day.png',
                    "type"=>4,"desribe"=>$desribe,"title"=>"固定日会员礼包");
                $data[]=$array;
            }

            //VIP月礼包
            if($value['member_month_id']!=0)
            {
                $content="";
                $link=$this->db->field("*")->table("tab_user_level")->where("id = {$value['member_month_id']}")->find();
                if(!empty($link['gift_id']))
                {
                    $giftbag=$this->db->field("*")->table("tab_giftbag")->where("id = {$link['gift_id']}")->find();
                    $content=$content.$giftbag['desribe'];
                }
                if(!empty($link['coupon_id']))
                {
                    $giftbag=$this->db->field("amount")->table("tab_coupons")->where("id = {$link['coupon_id']}")->find();
                    $content=$content."、代金券*".$giftbag['amount'];;
                }
                if(!empty($link['point']))
                {
                    $content=$content."、"."积分*".$link['point'];
                }
                if(!empty($link['balance']))
                {
                    $content=$content."、"."平台币*".$link['balance'];
                }
                if (strpos($content, '、') == 0)
                {
                    $desribe=mb_substr($content,1);
                }
                else{
                    $desribe=$content;
                }
                $array=array('gift_id'=>$value['member_month_id'],'icon'=>'http://app.zhishengwh.com/public/img/gift/vip_month.png',
                    "type"=>5,"desribe"=>$desribe,"title"=>"VIP月礼包");
                $data[]=$array;
            }
            //VIP周礼包
            if($value['member_week_id']!=0)
            {
                $content="";
                $link=$this->db->field("*")->table("tab_user_level_week")->where("id = {$value['member_week_id']}")->find();
                if(!empty($link['gift_id']))
                {
                    $giftbag=$this->db->field("*")->table("tab_giftbag")->where("id = {$link['gift_id']}")->find();
                    $content=$content.$giftbag['desribe'];
                }
                if(!empty($link['coupon_id']))
                {
                    $giftbag=$this->db->field("amount")->table("tab_coupons")->where("id = {$link['coupon_id']}")->find();
                    $content=$content."、代金券*".$giftbag['amount'];;
                }
                if(!empty($link['point']))
                {
                    $content=$content."、"."积分*".$link['point'];
                }
                if(!empty($link['balance']))
                {
                    $content=$content."、"."平台币*".$link['balance'];
                }
                if (strpos($content, '、') == 0)
                {
                    $desribe=mb_substr($content,1);
                }
                else{
                    $desribe=$content;
                }
                $array=array('gift_id'=>$value['member_week_id'],'icon'=>'http://app.zhishengwh.com/public/img/gift/vip_week.png',
                    "type"=>6,"desribe"=>$desribe,"title"=>"VIP周礼包");
                $data[]=$array;
            }
        }
        $data = array_slice($data,$start,$showPage);
        $this->ajax_return(0,$data);
    }
    public function giftdetailAction(){
        $type=$this->input['type'];
        $gift_id=$this->input['gift_id'];
        $user_id=$this->u_id;
        $data['gift_id']=$gift_id;
        $data['desribe']="";
        $data['novice']="";
        $data['balance']="平台币*0";
        $data['coupon_name']="代金券*0";
        $data['coupon_id']=0;
        $data['point']="积分*0";
        if($type==1)
        {
            $result=$this->db->action("select a.desribe,b.novice from tab_giftbag a left join tab_gift_record b on a.id=b.gift_id where a.id={$gift_id} and b.user_id={$user_id}");
           /* $data['desribe']=$result[0]['desribe'];
            $data['novice']=$result[0]['novice'];*/
            if($result)
            {
                $data['desribe'] = $result[0]['desribe']?$result[0]['desribe']:"";
                $data['novice'] = $result[0]['novice']?$result[0]['novice']:"";
            }
            else{
                $data['desribe']="";
                $data['novice']="";
            }
        }
        elseif ($type==2) {
            $checkindata = $this->db->field("*")->table("tab_checkin")->where("id = {$gift_id}")->find();
            if ($checkindata['type'] == "积分") {
                $data['point'] = "积分*" . $checkindata['text'];
            } elseif ($checkindata['type'] == "优惠券") {
                $coupon = $this->db->field("amount,id")->table("tab_coupons")->where("id = {$checkindata['text']}")->find();
                $data['coupon_name'] = "代金券*" . $coupon['amount'];
                $data['coupon_id'] = $coupon['id'];
            } elseif ($checkindata['type'] == "礼包") {
                $result = $this->db->action("select a.desribe,b.novice from tab_giftbag a left join tab_gift_record b on a.id=b.gift_id where a.id={$checkindata['gift_id']} and b.user_id={$user_id}");
                if($result)
                {
                    $data['desribe'] = $result[0]['desribe']?$result[0]['desribe']:"";
                    $data['novice'] = $result[0]['novice']?$result[0]['novice']:"";
                }
                else{
                    $data['desribe']="";
                    $data['novice']="";
                }
            }
        }
        elseif($type!=1 && $type!=2)
        {
            if($type==3) {
                $link = $this->db->field("*")->table("tab_checkin_continuity")->where("id = {$gift_id}")->find();
            }
            elseif($type==4)
            {
                $link = $this->db->field("*")->table("tab_user_loca_level")->where("id = {$gift_id}")->find();
            }
            elseif($type==5)
            {
                $link = $this->db->field("*")->table("tab_user_level")->where("id = {$gift_id}")->find();
            }
            elseif($type==6)
            {
                $link = $this->db->field("*")->table("tab_user_level_week")->where("id = {$gift_id}")->find();
            }
            if(!empty($link['gift_id']))
            {
                $result = $this->db->action("select a.desribe,b.novice from tab_giftbag a left join tab_gift_record b on a.id=b.gift_id where a.id={$link['gift_id']} and b.user_id={$user_id}");
                if($result)
                {
                    $data['desribe'] = $result[0]['desribe']?$result[0]['desribe']:"";
                    $data['novice'] = $result[0]['novice']?$result[0]['novice']:"";
                }
                else{
                    $data['desribe']="";
                    $data['novice']="";
                }

            }
            if(!empty($link['coupon_id']))
            {
                $coupon=$this->db->field("amount,id")->table("tab_coupons")->where("id = {$link['coupon_id']}")->find();
                $data['coupon_name'] = "代金券*" . $coupon['amount'];
                $data['coupon_id'] = $coupon['id'];
            }
            if(!empty($link['point']))
            {
                $data['point'] = "积分*" . $link['point'];
            }
            if(!empty($link['balance']))
            {
                $data['balance'] = "平台币*" . $link['balance'];
            }
        }
        $this->ajax_return(0,$data);
    }


}