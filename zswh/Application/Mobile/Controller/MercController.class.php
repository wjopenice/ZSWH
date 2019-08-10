<?php
namespace Mobile\Controller;
use Think\Controller;
use OT\DataDictionary;
use User\Api\PromoteApi;
use User\Api\MemberApi;
use Org\Util\Memcache as Memcache;
use Admin\Model\ApplyModel;
class MercController extends Controller{

    public function wapmerc(){
        $this->display();
    }
    /**
    申请游戏
     */
    public function apply(){
        if(isset($_POST['game_id'])){
            $model = new ApplyModel(); //D('Apply');
            $data['game_id'] = $_POST['game_id'];
            $data['game_name'] = get_game_name($_POST['game_id']);
            $data['promote_id'] = session("promote_auth.pid");
            $data['promote_account'] = session("promote_auth.account");
            $data['apply_time'] = NOW_TIME;
            $data['status'] = 0;
            $data['enable_status'] = 1;
            $wherein['game_id']=$_POST['game_id'];
            $wherein['promote_id']=session("promote_auth.pid");
            $is=M('apply','tab_')->where($wherein)->select();
            if ($is) {
                $this->ajaxReturn(array("status"=>"-1","msg"=>"已申请"));
            } else {
                $res = $model->add($data);
                if($res){
                    $this->ajaxReturn(array("status"=>"1","msg"=>"申请成功"));
                }
                else{
                    $this->ajaxReturn(array("status"=>"0","msg"=>"申请失败"));
                }
            }

        }
        else{
            $this->ajaxReturn(array("status"=>"0","msg"=>"操作失败"));
        }


    }
    public function wapmygame(){
        $type = $_GET['type'];
        $username = $_SESSION['username'];
        $userdata = M("Promote","tab_")->field("id")->where(["account"=>$username])->find();
        if(isset($_REQUEST['game_name'])){
            $map['tab_game.game_name']=array('like','%'.trim($_REQUEST['game_name']).'%');
            unset($_REQUEST['game_name']);
        }
        $map['promote_id'] = $userdata['id'];
        if($type==-1){
            unset($map['status']);
        }elseif($type == 1){
            $map['status'] =  1;
        }else{
            $map['status'] =  0;
        }
        $data = M("game","tab_")
            /* 查询指定字段，不指定则查询所有字段 */
            ->field("tab_game.*,tab_apply.promote_id,tab_apply.status,tab_apply.pack_url,features")
            ->join("tab_apply ON tab_game.id = tab_apply.game_id and tab_apply.promote_id = {$userdata['id']}")
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order("sort asc")
            /* 执行查询 */
            ->select();
        $url="http://".$_SERVER['HTTP_HOST'].__ROOT__."/media.php/member/preg/pid/{$userdata['id']}";
        foreach ($data as $key=>$value){
            $data[$key]['pack_url'] = substr($value['pack_url'],1);
        }
        $this->assign("url",$url);
        $this->assign('list_data', $data);
        $this->display();
    }

    public function wapreqgame(){
        $username = $_SESSION['username'];
        $userdata = M("Promote","tab_")->field("id")->where(["account"=>$username])->find();
        if(isset($_REQUEST['game_name'])){
            $map['tab_game.game_name'] = array('like','%'.$_REQUEST['game_name'].'%') ;
        }
        $map['game_status']=1;
        $data = M("game","tab_")
            /* 查询指定字段，不指定则查询所有字段 */
            ->field("tab_game.id,tab_game.game_name,icon,game_type_name,game_size,version,recommend_status,game_address,promote_id,status,dow_status,features")
            ->join("tab_apply ON tab_game.id = tab_apply.game_id and tab_apply.promote_id = {$userdata['id']}","LEFT")
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order("sort asc")
            /* 执行查询 */
            ->select();
        $this->assign('list_data', $data);
        $this->display();
    }
    //登录记录
    public function waploginlog(){
        $username = $_SESSION['username'];
        $userdata = M("promote","tab_")->where(["account"=>$username])->find();

        $user=M("user","tab_")->where('promote_id='.$userdata['id'])->select();
        foreach ($user as $value) {
            $user_id[] = $value['id'];
        }
        $map['user_id'] = array('in',$user_id);
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
        if(empty($user_id)){
            $this->assign('list_data', []);
        }else{
            $data = M("user_login_record","tab_")
                ->field('*')
                ->where($map)
                ->order('id desc')
                ->select();
            $this->assign('list_data', $data);
        }
        $game = M("game","tab_")->field("id,game_name")->where(["game_status"=>1])->select();
        $this->assign("game",$game);
        $this->display();
    }
    //注册明细
    public function wapregisterlog(){
        $username = $_SESSION['username'];
        $userdata = M("promote","tab_")->where(["account"=>$username])->find();
        $pro_id=get_prmoote_chlid_account($userdata['id']);
        foreach ($pro_id as $key => $value) {
            $pro_id1[]=$value['id'];
        }
        if(!empty($pro_id1)){
            $pro_id2=array_merge($pro_id1,array($userdata['id']));
        }else{
            $pro_id2=array($userdata['id']);
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
        }
        if(!empty($_REQUEST['start'])&&!empty($_REQUEST['end'])){
            $map['register_time']  =  array('BETWEEN',array(strtotime($_REQUEST['start']),strtotime($_REQUEST['end'])+24*60*60-1));
        }
        $map['is_check']=array('neq',2);
        //$this->lists("User",$p,$map);
        $data = M("user","tab_")
            /* 查询指定字段，不指定则查询所有字段 */
            ->field("*")
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order('id DESC')
            /* 执行查询 */
            ->select();
        $user = M("user","tab_")->where('promote_id='.$userdata['id'])->select();
        $this->assign('list_data', $data);
        $this->assign("user",$user);
        $this->display();
    }
//充值明细
    public function waprecharge(){
        $username = $_SESSION['username'];
        $userdata = M("promote","tab_")->where(["account"=>$username])->find();
        $pid = $userdata['id'];
        $pro_id=get_prmoote_chlid_account($pid);
        foreach ($pro_id as $key => $value) {
            $pro_id1[]=$value['id'];
        }
        if(!empty($pro_id1)){
            $pro_id2=array_merge($pro_id1,array($pid));
        }else{
            $pro_id2=array($pid);
        }
       $map['promote_id'] = array('in',$pro_id2);
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
       // $res = M('promote','tab_')->field('alipayway_sign')->find();
        if($userdata['alipayway_sign']!=1){
            $map['pay_way'] = 0;
        }
        $listdata=M('spend','tab_')
            ->field('user_account,pay_order_number,game_name,pay_amount,pay_way,pay_time,promote_account,promote_id,id')
            ->where($map)
            ->order('id desc')
           ->select();
        $this->assign("data",$listdata);
         $this->display();
    }
    public function rechargedetail(){
        $data = M("spend","tab_")->where(["id"=>$_REQUEST['id']])->find();
        $this->assign("s",$data);
        $this->display();
    }
    //绑币充值明细
    public function bindrecharge(){
        $username = $_SESSION['username'];
        $userdata = M("promote","tab_")->where(["account"=>$username])->find();
        $pid = $userdata['id'];
        $pro_id=get_prmoote_chlid_account($pid);
        foreach ($pro_id as $key => $value) {
            $pro_id1[]=$value['id'];
        }
        if(!empty($pro_id1)){
            $pro_id2=array_merge($pro_id1,array($pid));
        }else{
            $pro_id2=array($pid);
        }
        $map['promote_id'] = array('in',$pro_id2);
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
        // $res = M('promote','tab_')->field('alipayway_sign')->find();
        if($userdata['alipayway_sign']!=1){
            $map['pay_way'] = 0;
        }
        $listdata=M('bind_spend','tab_')
            ->field('user_account,pay_order_number,game_name,pay_amount,pay_way,pay_time,promote_account,promote_id,id')
            ->where($map)
            ->order('id desc')
            ->select();
        $this->assign("data",$listdata);
        $this->display();
    }
    public function bindrechargedetail(){
        $data = M("bind_spend","tab_")->where(["id"=>$_REQUEST['id']])->find();
        $this->assign("s",$data);
        $this->display();
    }
    public function wapordinarylogin(){
       $username = $_SESSION['username'];
       $userdata = M("user","tab_")->where(["account"=>$username])->find();
       $this->assign("userdata",$userdata);
       $this->display();
    }

    public function points(){
        $username = $_SESSION['username'];
        $userdata = M("user","tab_")->where(["account"=>$username])->find();
        $data  = M('points_record','tab_')->where(['user_id'=>$userdata['id']])->order('create_time desc')->limit('10')->select();
        $this->assign('list_data', $data);
        $this->display();
    }

    public function pctradexf(){
        $type = empty($_GET['mytype'])?0:1;
        $username = $_SESSION['username'];
        $userdata = M("user","tab_")->where(["account"=>$username])->find();
        if($type == 0){
            $tabname = 'spend';
        }else{
            $tabname = 'bind_spend';
        }
        $data = M($tabname,"tab_")
            ->field('pay_order_number,pay_amount,game_name,pay_way,pay_time')
            ->where(['user_id'=>$userdata['id']])
            ->order('pay_time desc')
            ->select();
        $this->assign('list_data', $data);
        $this->display();
    }

    public function pctrade(){
        $type = empty($_GET['mytype'])?0:1;
        $username = $_SESSION['username'];
        $userdata = M("user","tab_")->where(["account"=>$username])->find();
        $map = ['user_id'=>$userdata['id']];
        if($type == 0){
            $data_putp = $this->platform_user_to_pay($map);
            $data_pptu = $this->platform_promote_to_user($map);
        }else{
            $data_putp = $this->platform_bind_promote_to_user($map);
            $data_pptu = $this->platform_admin_to_user($map);
        }
        if(empty($data_putp) ){
            $data_putp = array();
        }
        if(empty($data_pptu) ){
            $data_pptu = array();
        }
        $data = array_merge($data_putp,$data_pptu);
        $this->sort_array($data,'create_time','desc');
        $this->assign('list_data', $data);
        $this->display();
    }

    public function wapalreadlogin(){
        $username = $_SESSION['username'];
        $userdata = M("promote","tab_")->where(["account"=>$username])->find();
        $parent=get_pro_parent($userdata['id']);
        $this->assign('parent', $parent);
        $this->display();
    }

    public function wapmerclist(){
        $this->meta_title = '渠道列表';
        $this->display();
    }

    public function wapmercdetail(){
        $this->meta_title = '渠道详情';
        $this->display();
    }

    public function sort_array(&$array, $keyid, $order = 'asc', $type = 'number') {
        if (is_array($array)) {
            foreach ($array as $val) {
                $order_arr[] = $val[$keyid];
            }
            $order = ($order == 'asc') ? SORT_ASC : SORT_DESC;
            $type = ($type == 'number') ? SORT_NUMERIC : SORT_STRING;
            array_multisort($order_arr, $order, $type, $array);
        }

    }
    public function platform_user_to_pay($map){
        $where['user_id'] = $map['user_id'];
        $where['pay_way'] = 1;
        $where['pay_status'] = 1;
        $data=M('deposit','tab_')
            ->where($where)
            ->order('create_time desc')
            ->field('pay_order_number,pay_amount,pay_way,create_time')
            ->select();
        return $data;
    }
    public function platform_promote_to_user($map){
        if(!empty($map['order_number'] )){
            $map['order_number'] =$map['pay_order_number'];
            unset($map['pay_order_number']);
        }
        $map['agents_id'] = $map['user_id'];
        unset($map['user_id']);
        $map['pay_status'] = 1;
        $data=M('pay_agents','tab_')
            ->where($map)
            ->order('create_time desc')
            ->field('order_number pay_order_number,amount pay_amount,create_time')
            ->select();
        foreach ($data as $key => &$value) {
            $value['pay_way'] =4;
        }
        return $data;
    }
    public function platform_bind_promote_to_user($map){
        //渠道转移给用户绑币
        $where['agents_id']=$map['user_id'];
        $where['type']=1;
        $data=M('movebang','tab_')
            ->where($where)
            ->order('create_time desc')
            ->field('game_name,amount pay_amount,create_time')
            ->select();
        foreach ($data as $key => &$value) {
            $value['pay_way'] =4;
        }
        return $data;
    }
    public function platform_admin_to_user($map){
        $where['user_id'] = $map['user_id'];
        $where['status'] = 1;
        $data=M('provide','tab_')
            ->where($where)
            ->order('create_time desc')
            ->field('order_number pay_order_number,amount pay_amount,create_time,game_name')
            ->select();
        foreach ($data as $key => &$value) {
            $value['pay_way'] = 5;
        }
        return $data;
    }

}