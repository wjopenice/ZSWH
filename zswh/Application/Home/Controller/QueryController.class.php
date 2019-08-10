<?php

namespace Home\Controller;
use OT\DataDictionary;
use User\Api\PromoteApi;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class QueryController extends BaseController {

    public function recharge($p=0)
    {
       //print_r($_REQUEST);exit;
       // echo get_pro_parent(session('promote_auth.pid'));exit;
       // print_r(session());exit;
       // echo session('promote_auth.pid');exit;
        $pro_id=get_prmoote_chlid_account(session('promote_auth.pid'));
       // print_r($pro_id);exit;
        foreach ($pro_id as $key => $value) {
            $pro_id1[]=$value['id'];
        }
        if(!empty($pro_id1)){
            $pro_id2=array_merge($pro_id1,array(get_pid()));
        }else{
            $pro_id2=array(get_pid());
        }
        //print_r($pro_id2);exit;
        $map['promote_id'] = array('in',$pro_id2);
        //$map['promote_id'] =session('promote_auth.pid');
        //print_r($map);exit;
        //$_REQUEST['user_account']=$_REQUEST['account'];
        if(isset($_REQUEST['user_account'])&&trim($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']==''){
                unset($_REQUEST['game_name']);
            }else{
                $map['game_name'] = array('like','%'.$_REQUEST['game_name'].'%');
                unset($_REQUEST['game_name']);
            }
        }
        if($_REQUEST['promote_id']>0){
            $map['promote_id']=$_REQUEST['promote_id'];
        }
        if(!empty($_REQUEST['time-start'])&&!empty($_REQUEST['time-end'])){
            $map['pay_time']  =  array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(!empty($_REQUEST['start'])&&!empty($_REQUEST['end'])){

            $map['pay_time']  =  array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));

            unset($_REQUEST['start']);unset($_REQUEST['end']);

        }
        $map['pay_status'] = 1;
        $res = M('promote','tab_')->field('alipayway_sign')->find(session('promote_auth.pid'));
        
        if($res['alipayway_sign']!=1){
           // $map['pay_way'] = 0;
        }
        
        $total = M('spend',"tab_")->where($map)->sum('pay_amount');
        $this->assign("total_amount",$total); 
        $this->meta_title = "用户充值";
        //print_r($map);exit;
        $this->lists("Spend",$p,$map);
    }

    /**
     * 用户绑定平台币 游戏内消费记录
     * @author whh
     */
    public function bindrecharge($p=0)
    {
       //print_r($_REQUEST);exit;
        //print_r(session());exit;
        $pro_id=get_prmoote_chlid_account(session('promote_auth.pid'));
        //print_r($pro_id);exit;
        foreach ($pro_id as $key => $value) {
            $pro_id1[]=$value['id'];
        }
        if(!empty($pro_id1)){
            $pro_id2=array_merge($pro_id1,array(get_pid()));
        }else{
            $pro_id2=array(get_pid());
        }
        //print_r($pro_id2);exit;
        $map['promote_id'] = array('in',$pro_id2);
        //$map['promote_id'] =session('promote_auth.pid');
        //print_r($map);exit;
        //$_REQUEST['user_account']=$_REQUEST['account'];
        if(isset($_REQUEST['user_account'])&&trim($_REQUEST['user_account'])){
            $map['user_account']=array('like','%'.$_REQUEST['user_account'].'%');
            unset($_REQUEST['user_account']);
        }
        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']==''){
                unset($_REQUEST['game_name']);
            }else{
                $map['game_name'] =array('like','%'.$_REQUEST['game_name'].'%');
                unset($_REQUEST['game_name']);
            }
        }
        if($_REQUEST['promote_id']>0){
            $map['promote_id']=$_REQUEST['promote_id'];
        }
        if(!empty($_REQUEST['time-start'])&&!empty($_REQUEST['time-end'])){
            $map['pay_time']  =  array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }
        if(!empty($_REQUEST['start'])&&!empty($_REQUEST['end'])){

            $map['pay_time']  =  array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));

            unset($_REQUEST['start']);unset($_REQUEST['end']);

        }
        $map['pay_status'] = 1;
        /*$res = M('promote','tab_')->field('alipayway_sign')->find(session('promote_auth.pid'));
        
        if($res['alipayway_sign']!=1){
            $map['pay_way'] = 0;
        }
        */
        $total = M('bind_spend',"tab_")->where($map)->sum('pay_amount');
        //print_r($total);exit;
        $this->assign("total_amount",$total); 
        $this->meta_title = "用户充值";
        $this->lists("BindSpend",$p,$map);
    }

    public function register($p=0){
        $pro_id=get_prmoote_chlid_account(session('promote_auth.pid'));

        foreach ($pro_id as $key => $value) {

            $pro_id1[]=$value['id'];

        }

        if(!empty($pro_id1)){

            $pro_id2=array_merge($pro_id1,array(get_pid()));

        }else{

            $pro_id2=array(get_pid());

        }

        $map['promote_id'] = array('in',$pro_id2);

        if(isset($_REQUEST['account'])&&trim($_REQUEST['account'])){

            $map['account']=array('like','%'.$_REQUEST['account'].'%');

            unset($_REQUEST['user_account']);

        }

        if(isset($_REQUEST['game_appid'])&&$_REQUEST['game_appid']!=0){

            $map['game_appid']=$_REQUEST['game_appid'];

        }

        if($_REQUEST['promote_id']>0){

            $map['promote_id']=$_REQUEST['promote_id'];

        }

        if(!empty($_REQUEST['time-start'])&&!empty($_REQUEST['time-end'])){

            $map['register_time']  =  array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));

            // unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);

        }

        if(!empty($_REQUEST['start'])&&!empty($_REQUEST['end'])){

            $map['register_time']  =  array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));

            // unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);

        }
        // var_dump($map);exit;
        $map['is_check']=array('neq',2);

        $this->lists("User",$p,$map);

    }

    /**
    *我的收益
    */
    public function my_earning($p=1){
        $model=array(
            'm_name'=>'settlement',
            'map'   =>array('promote_id'=>array('in',get_pid())),
            'order' =>'spend_time',
            'template_list'=>'my_earning'
        );
        $user = A('User','Event');
        $user->shou_list($model,$p);
    }

    /**
    *账单查询
    */
    public function bill(){
          $pid=M("promote","tab_")->where(array('parent_id'=>get_pid()))->select();
            for ($i=0; $i <count($pid) ; $i++) { 
                $parent_id[]=$pid[$i]['id'];
            }
            $ppid=implode(',',$parent_id);
        $map['promote_id']=array('in',array(get_pid(),$ppid));
        if(isset($_REQUEST['game_name'])&&!empty($_REQUEST['game_name'])){
            $map['game_id']=$_REQUEST['game_name'];
        }
            if(isset($_REQUEST['time-start']) && isset($_REQUEST['time-end']) && !empty($_REQUEST['time-start']) && !empty($_REQUEST['time-end'])){
            $map['spend_time']  =  array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])));
            unset($_REQUEST['time-start']);unset($_REQUEST['time-end']);
        }

        $model=array(
            'm_name'=>'settlement',
            'map'   =>$map,
            'order' =>'spend_time',
            'template_list'=>'bill'
        );

        $user = A('User','Event');
        $user->shou_list($model,$p);
    }

    //登录记录
    public function login_record($p=0){
      $user=M("user","tab_")->where('promote_id='.session('promote_auth.pid'))->select();
     foreach ($user as $value) {
              $user_id[] = $value['id'];
          }

          $map['user_id'] = array('in', $user_id);;

        if(isset($_REQUEST['game_name'])){
            if($_REQUEST['game_name']==''){
                unset($_REQUEST['game_name']);
            }else{
                $map['game_name'] =array('like','%'.$_REQUEST['game_name'].'%');
                unset($_REQUEST['game_name']);
            }
        }
        if($_REQUEST['promote_id']>0){
            $map['promote_id']=$_REQUEST['promote_id'];
        }

        if(!empty($_REQUEST['time-start'])&&!empty($_REQUEST['time-end'])){

            $map['login_time']  =  array('BETWEEN',array(strtotime($_REQUEST['time-start']),strtotime($_REQUEST['time-end'])+24*60*60-1));

        }

        if(!empty($_REQUEST['start'])&&!empty($_REQUEST['end'])){

            $map['login_time']  =  array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));

         }
        $this->meta_title = "登录记录";
        $row    = 15;
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据
        if(empty($user_id)){
            $this->assign('count',0);
            $this->assign('list_data', []);
        }
        else{
            $data = M("user_login_record","tab_")
                /* 查询指定字段，不指定则查询所有字段 */
                ->field('*')
                // 查询条件
                ->where($map)
                /* 默认通过id逆序排列 */
                ->order('id desc')
                /* 数据分页 */
                ->page($page, $row)
                /* 执行查询 */
                ->select();

            /* 查询记录总数 */
            $count = M('user_login_record',"tab_")->where($map)->count();
            //分页
            if($count > $row){
                $page = new \Think\Page($count, $row);
                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                $this->assign('_page', $page->show());
            }
            $this->assign('count',$count);
            $this->assign('list_data', $data);
        }


        $this->display();

    }
}