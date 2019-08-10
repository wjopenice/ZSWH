<?php
use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;

class CallbackController extends Controller
{
    public $pdb;
    public function initialize() {
        include APP_PATH."/core/Pdb.php";
        $this->pdb = new \app\core\Pdb();
    }
    //商城支付宝回调
    public function shopnotifyAction(){
        $data = $_POST;
        file_put_contents(BASE_PATH."/logpay.txt",json_encode($data).PHP_EOL."\r\n",FILE_APPEND);//确认是支付宝回调的
        //商户订单号
        $order_id     = $data['out_trade_no'];
        //交易状态
        $trade_status = $data['trade_status'];
        //查看是否存在该未支付订单，下面可以根据自己业务逻辑编写
        $result= $this->pdb->field("*")->table("zsshoporder")->where("out_trade_no = '{$order_id}' and pay_status=0")->find();
        $shop=$this->pdb->field("inventory")->table("zsshop")->where("shop_name='{$result['shop_name']}'")->find();
        $editshop['inventory']=$shop['inventory']-1;
        //存在该订单并支付成功，进行修改
        if ($result && $trade_status=="TRADE_SUCCESS") {
            $sqlData = array(
                "pay_status" => 1,
                "pay_time"=>time(),
                "expire_time"=>time()+7*24*3600,
                'trade_no'=>$data['trade_no']
            );
            $res = $this->pdb->action($this->pdb->updateSql("zsshoporder",$sqlData,"out_trade_no = '{$order_id}'"));
            $this->pdb->action($this->pdb->updateSql("zsshop",$editshop,"shop_name = '{$result['shop_name']}'"));
            //修改成功
            if ($res) {
                file_put_contents(BASE_PATH."/logpay.txt",$order_id.":修改商城订单数据成功"."\r\n",FILE_APPEND);//确认是支付宝回调的
            }
        }
        elseif ($trade_status=="TRADE_CLOSED")
        {
            file_put_contents(BASE_PATH."/logpay.txt",$order_id.":支付交易超时"."\r\n",FILE_APPEND);//确认是支付宝回调的
        }
        else{
            file_put_contents(BASE_PATH."/logpay.txt",$order_id.":支付失败"."\r\n",FILE_APPEND);//确认是支付宝回调的
        }

    }
    //捐助支付宝app回调
    public function rescuenotifyAction(){
        $data = $_POST;
        //file_put_contents(BASE_PATH."/logpay.txt",json_encode($data).PHP_EOL."\r\n",FILE_APPEND);//确认是支付宝回调的
        //商户订单号
        $order_id     = $data['out_trade_no'];
        //交易状态
        $trade_status = $data['trade_status'];
        //查看是否存在该未支付订单，下面可以根据自己业务逻辑编写
        $result= $this->pdb->field("*")->table("zswealorder")->where("order_number = '{$order_id}' and pay_status=0")->find();
        //存在该订单并支付成功，进行修改
        if ($result && $trade_status=="TRADE_SUCCESS") {
            $sqlData = array(
                "pay_status" => 1,
                "pay_time"=>time(),
                "pay_weal_status"   => 1,
                "certno"=>build_cert_no(),
                'pay_order_number'=>$data['trade_no']
            );
            $res = $this->pdb->action($this->pdb->updateSql("zswealorder",$sqlData,"order_number = '{$order_id}'"));
            //修改成功
            if ($res) {
                file_put_contents(BASE_PATH."/logpay.txt",$order_id.":修改数据成功"."\r\n",FILE_APPEND);//确认是支付宝回调的
            }
        }
        elseif ($trade_status=="TRADE_CLOSED")
        {
            file_put_contents(BASE_PATH."/logpay.txt",$order_id.":支付交易超时"."\r\n",FILE_APPEND);//确认是支付宝回调的
        }
        else{

            file_put_contents(BASE_PATH."/logpay.txt",$order_id.":支付失败"."\r\n",FILE_APPEND);//确认是支付宝回调的
        }

    }

}