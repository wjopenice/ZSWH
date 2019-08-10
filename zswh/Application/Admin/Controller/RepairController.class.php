<?php

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use Common\Api\GameApi;
/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class RepairController extends ThinkController {
    
    /**
    *补单列表
    */
    public function repairList($value='')
    {
        if(isset($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['pay_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['pay_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['game_id'])){
            if($_REQUEST['game_id']=='全部'){
                unset($_REQUEST['game_id']);
            }else{
                $map['game_id']=$_REQUEST['game_id'];
                unset($_REQUEST['game_id']);
            }
        }
        if(isset($_REQUEST['pay_way'])){
            $map['pay_way']=$_REQUEST['pay_way'];
            unset($_REQUEST['pay_status']);
        }
        if(isset($_REQUEST['pay_order_number'])){
            $map['pay_order_number']=array('like','%'.$_REQUEST['pay_order_number'].'%');
            unset($_REQUEST['pay_order_number']);
        }
        $map["pay_status"] = 1;
        $map["pay_game_status"] = 0;
        $row=15;
        $data = M('spend','tab_')
                /* 查询指定字段，不指定则查询所有字段 */
                ->field(empty($fields) ? true : $fields)
                // 查询条件
                ->where($map)
                /* 默认通过id逆序排列 */
                ->order(empty($map['order'])?"id desc":$map['order'])
                /* 数据分页 */
                ->page($_GET["p"], $row)
                /* 执行查询 */
                ->select();

            /* 查询记录总数 */

        $count =M('spend','tab_')->where($map)->count();
        //print_r($count);exit;
         //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        //$list = M("spend","tab_")->where($map)->select();
        $this->assign("list_data",$data);
        $this->display();
    }
    public function repairBindlist($value='')
    {
        if(isset($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['pay_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['pay_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['game_id'])){
            if($_REQUEST['game_id']=='全部'){
                unset($_REQUEST['game_id']);
            }else{
                $map['game_id']=$_REQUEST['game_id'];
                unset($_REQUEST['game_id']);
            }
        }
        
        if(isset($_REQUEST['pay_order_number'])){
            $map['pay_order_number']=array('like','%'.$_REQUEST['pay_order_number'].'%');
            unset($_REQUEST['pay_order_number']);
        }
        $map["pay_status"] = 1;
        $map["pay_game_status"] = 0;
        $row=15;
        $data = M('bind_spend','tab_')
                /* 查询指定字段，不指定则查询所有字段 */
                ->field(empty($fields) ? true : $fields)
                // 查询条件
                ->where($map)
                /* 默认通过id逆序排列 */
                ->order(empty($map['order'])?"id desc":$map['order'])
                /* 数据分页 */
                ->page($_GET["p"], $row)
                /* 执行查询 */
                ->select();

            /* 查询记录总数 */

        $count =M('bind_spend','tab_')->where($map)->count();
        //print_r($count);exit;
         //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }

        //$list = M("bind_spend","tab_")->where($map)->select();
        $this->assign("list_data",$data);
        $this->display();
    }
    /**
    *编辑补单
    */
    public function repairEdit($orderNo=null){
        $param['out_trade_no'] = $orderNo;
        $game = new GameApi();
        $game->game_pay_notify($param,1);
        $this->repairAdd($orderNo);
        $this->success('补单成功',U('repairList'));
        //$this->display();
    }
    /**
    *编辑绑币补单
    */
    public function repairBindEdit($orderNo=null){
        $param['out_trade_no'] = $orderNo;
        $param['distinction'] = 'bind';
        $game = new GameApi();
        $game->game_pay_notify($param,2);
        $this->repairAdd($param);
        $this->success('补单成功',U('repairBindlist'));
        //$this->display();
    }
    /***
    *添加补单记录
    */
    protected function repairAdd($orderNo=null){
        if(is_array($orderNo)){
            $map['pay_order_number'] = $orderNo['out_trade_no'];
            $dis_pf='bind_PF';
            $pay_data = M("Bind_spend","tab_")->where($map)->find();
        }else{
            $map['pay_order_number'] = $orderNo;
            $pay_data = M("Spend","tab_")->where($map)->find();
        }
        if(!empty($pay_data)){
            M("RepairRecord","tab_")->add(
                array(
                    "pay_order_number"=>$pay_data['pay_order_number'],
                    "user_id"=>$pay_data['user_id'],
                    "user_account"=>$pay_data['user_account'],
                    "user_nickname"=>$pay_data['user_nickname'],
                    "game_id"=>$pay_data['game_id'],
                    "game_appid"=>$pay_data['game_appid'],
                    "game_name"=>$pay_data['game_name'],
                    "op_id"=>session("user_auth.uid"),
                    "op_nickname"=>session("user_auth.username"),
                    "create_time"=>NOW_TIME,
                    'dis_pf'=>$dis_pf,
                )
            );
        }
    }

    /**
    *补单记录列表
    */
    public function repairRecordList(){
        if(isset($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['game_id'])){
            if($_REQUEST['game_id']=='全部'){
                unset($_REQUEST['game_id']);
            }else{
                $map['game_id']=$_REQUEST['game_id'];
                unset($_REQUEST['game_id']);
            }
        }
        
        if(isset($_REQUEST['pay_order_number'])){
            $map['pay_order_number']=array('like','%'.$_REQUEST['pay_order_number'].'%');
            unset($_REQUEST['pay_order_number']);
        }
        $row=15;
        $map['dis_pf']=0;
        $data = M('repair_record','tab_')
                /* 查询指定字段，不指定则查询所有字段 */
                ->field(empty($fields) ? true : $fields)
                // 查询条件
                ->where($map)
                /* 默认通过id逆序排列 */
                ->order(empty($map['order'])?"id desc":$map['order'])
                /* 数据分页 */
                ->page($_GET["p"], $row)
                /* 执行查询 */
                ->select();

            /* 查询记录总数 */

        $count =M('repair_record','tab_')->where($map)->count();
        //print_r($count);exit;
         //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        //$list = M("repair_record","tab_")->where($map)->select();
        $this->assign("list_data",$data);
        $this->display();
    }
    /**
    *补单记录列表
    */
    public function repairBindRecordList(){
        if(isset($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['time-start'])&&isset($_REQUEST['time-end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(isset($_REQUEST['start'])&&isset($_REQUEST['end'])){
            $map['create_time'] =array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
            unset($_REQUEST['start']);unset($_REQUEST['end']);
        }
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']=='全部'){
                unset($_REQUEST['game_name']);
            }else{
                $map['game_name']=$_REQUEST['game_name'];
                unset($_REQUEST['game_name']);
            }
        }
        
        if(isset($_REQUEST['pay_order_number'])){
            $map['pay_order_number']=array('like','%'.$_REQUEST['pay_order_number'].'%');
            unset($_REQUEST['pay_order_number']);
        }
        $map['dis_pf']=1;
        $list = M("repair_record","tab_")->where($map)->select();
        $this->assign("list_data",$list);
        $this->display();
    }
    /**
     *绑定平台币消费补单
     */
    public function repair_bind_cp(){
        $this->display();
    }
    public function repair_ajax_data(){
        $order = I("post.order");
        $type = I("post.type");
        $user = I("post.user");
        $swtichmodel = "";
        switch ($type){
            case "bind": $swtichmodel = "Bind_spend";break;
            case "spend":$swtichmodel = "Spend";break;
        }
        $map['_string'] = "pay_order_number='".$order."' or extend='".$order."'";
        //获取单笔订单数据
        $cpdata = M($swtichmodel,"tab_")->where($map)->find();
        //获取cp回掉地址
        $cpnoturl = M('Game_set',"tab_")->field("pay_notify_url")->where(['game_id'=>$cpdata['game_id']])->find();
        if(!empty($cpdata) && !empty($cpnoturl['pay_notify_url'])){
            //$repairData['pay_order_number'] = $order;
            $repairData['pay_order_number'] = $cpdata['pay_order_number'];
            $repairData['user_id'] = $cpdata['user_id'];
            $repairData['user_account'] = $cpdata['user_account'];
            $repairData['links'] = $cpnoturl['pay_notify_url'];
            $repairData['game_id'] = $cpdata['game_id'];
            $repairData['game_appid'] = $cpdata['game_appid'];
            $repairData['game_name'] = $cpdata['game_name'];
            $repairData['op_nickname'] = $user;
            $repairData['create_time'] = NOW_TIME;
            $repairData['type'] = $type;
            $repairData['cp_order'] = $cpdata['extend'];
            $repairData['body'] = $cpdata['props_name'];
            $repairData['pay_amount'] = $cpdata['pay_amount'];
            $repairData['pay_time'] = $cpdata['pay_time'];
            $repairData['promote_account'] = $cpdata['promote_account'];
            return $this->ajaxReturn($repairData);
        }else{
            $repairData = [];
            return $this->ajaxReturn($repairData);
        }
    }
    public function repair_ajax(){
        set_time_limit(0);
        $data = I("post.");
        $resdata['gameid'] = $data['game_id'];
        $resdata['extend'] = $data['cp_order'];
        $resdata['total_amount'] = $data['pay_amount'];
        $resdata['props_name'] = $data['body'];
        $cpsignstr = json_encode($resdata);
        $resdata['pay_status'] = 1;
        $resdata['sign'] = hash_hmac("sha256",$cpsignstr,$data['game_appid']);
        $resdata['uid'] = $data['user_id'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $data['links']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($resdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);//设置等待时间
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpCode == 200 || $httpCode == 302){
            //补单成功
            $bool = M("Repair_list","tab_")->add($data);
            echo json_encode(["code"=>"ok"]);
        }else{
            //补单失败
            echo json_encode(["code"=>"no"]);
        }
    }
    /**
     *平台币消费补单
     */
    public function repair_cp(){
        $row=15;
        $arrData = M("Repair_list","tab_")->page($_GET["p"], $row)->order("id desc")->select();
        /* 查询记录总数 */
        $count =M('Repair_list','tab_')->count();
        //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }
        $this->assign("arrData",$arrData);
        $this->display();
    }
    public function repair_search(){
        $order = I("post.order");
        $arrData = M("Repair_list","tab_")->where(['pay_order_number'=>$order])->find();
        return $this->ajaxReturn($arrData);
    }
}
