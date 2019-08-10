<?php
namespace Mobile\Controller;
use Think\Controller;
use OT\DataDictionary;
use User\Api\PromoteApi;
use User\Api\MemberApi;
use Org\Util\Memcache as Memcache;
use Admin\Model\ApplyModel;
class ApplyController extends Controller{

    public function game_verify(){
        if(IS_POST){
            $map['account']=array('like','%'.$_REQUEST['account'].'%');
            unset($_REQUEST['account']);
        }
        $username = $_SESSION['username'];
        $userdata = M("promote","tab_")->where(["account"=>$username])->find();
        $map['parent_id'] = $userdata['id'];
        $data =  M("Promote","tab_")->where($map)->select();
        $this->assign('data', $data);
        $this->display();
    }

    public function game_verify_edit(){
        $uc_auth_sign = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';
        if(IS_POST){
            $promote = M('Promote','tab_');
            $promote->account = I("post.account");
            $promote->password = $this->think_ucenter_md5(I("post.password"),$uc_auth_sign);
            $promote->real_name = I("post.real_name");
            $promote->email = I("post.email");
            $promote->mobile_phone = I("post.mobile_phone");
            $promote->bank_name = I("post.bank_name");
            $promote->bank_card = I("post.bank_card");
            $promote->alipayway_sign = I("post.alipayway_sign");
            $promote->balance_coin = I("post.balance_coin");
            $promote->status = I("post.status");
            $map['id'] = I("post.id");
            $bool=$promote->where($map)->save();
            if($bool){
                echo "<meta charset='UTF-8'><script>alert('审核成功');location.href='/index.php?s=/Home/Apply/game_verify.html';</script>";
            }else{
                echo "<meta charset='UTF-8'><script>alert('审核失败');window.history.back();</script>";
            }
        }else{
            $map['id'] = $_GET['id'];
            $data =  M("Promote","tab_")->where($map)->find();
            $this->assign('list_data', $data);
            $this->display();
        }
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