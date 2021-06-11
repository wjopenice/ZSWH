<?php
require_once APP_PATH.'/alipay_app/aop/AopClient.php';
require_once APP_PATH.'/alipay_app/aop/request/AlipayTradeAppPayRequest.php';
class PetserController extends IosbaseController{
    //支付宝公钥
    const alipay_public_key = "";
    //商户私钥
    const merchant_private_key = "";
    //支付宝网关
    const gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //应用ID
    const app_id = "";
    const signkey="MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    //app调起支付宝
    public function apppay($body = "", $subject = "", $out_trade_no, $total_amount)
    {

        $jsonData = str_replace("\\/", "/", json_encode([
            'timeout_express'=>'30m',
            'product_code'=>"QUICK_MSECURITY_PAY",
            'total_amount'=>$total_amount,
            'subject'=>$subject,
            'body'=>$body,
            'out_trade_no'=>$out_trade_no],JSON_UNESCAPED_UNICODE));
        $payData['charset'] = "utf-8";
        $payData['biz_content'] = $jsonData;
        $payData['method'] = "alipay.trade.app.pay";
        $payData['app_id'] = self::app_id;
        $payData['sign_type'] = "RSA2";
        $payData['version'] = '1.0';
        $payData['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/petser/alipaynotify";
        $payData['timestamp'] = date("Y-m-d H:i:s",time());
        #对数组进行排序
        $param = $this->argSort($payData);
        $arg = $this->createLinkstring($param);
        $payData['sign'] = urlencode($this->sign2($arg));
        $datax = $this->createLinkstring($payData);
        //返回给SDK控制器
        $sHtml['arg']  = $datax;
        $sHtml['sign'] = $this->sign2($arg);
        $sHtml['out_trade_no'] = $out_trade_no;
        return $sHtml;
    }
    //宠服
    public function indexAction(){
        $data = $this->db->action("SELECT * FROM zs_shop_banner");
        $this->ajax_return(0,$data);
    }
    //搜索接口
    public function searchAction(){
        $search = $_POST['search'];
        $currentPage = empty($_POST['page']) ? "1" : $_POST['page'];
        $showPage = empty($_POST["showpage"]) ? "10" : $_POST["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $data = preg_replace("/[%_\s]+/","",ltrim(addslashes($search)));
        $arrData = $this->db->action("SELECT id as shop_id,shop_name,shop_banner,type FROM zs_shop WHERE shop_name LIKE '%{$data}%' LIMIT {$start},{$showPage}");
        if(!empty($arrData)){
            $arr = [];
            foreach ($arrData as $k=>$v){
                $arr[$k]['shop_id'] = $v['shop_id'];
                $arr[$k]['shop_name'] = $v['shop_name'];
                $banner = json_decode($v['shop_banner'],true);
                $arr[$k]['shop_banner'] = $banner['shop_banner1'];
                $arr[$k]['type'] = $v['type'];
            }
            $this->ajax_return(0,$arr);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //优品详情
    public function detailAction(){
        $id = $_POST['shop_id'];
        $shop = $this->db->field("*")->table("zs_shop")->where(" id = {$id} ")->find();
        if(!empty($shop)){
            $this->ajax_return(0,$shop);
        }else{
            $this->ajax_return(0,(object)[]);
        }
    }
    //添加用户地址
    public function incraddressAction(){
        $data['addr_id'] = null;
        $data['user_name'] = $_POST['user_name'];
        $data['user_tel'] = $_POST['user_tel'];
        $data['user_province'] = $_POST['user_province'];
        $data['user_city'] = $_POST['user_city'];
        $data['user_area'] = $_POST['user_area'];
        $data['user_addr'] = $_POST['user_addr'];
        $data['user_id'] = $_POST['id'];
        $bool = $this->db->action($this->db->insertSql("user_addr",$data));
        if($bool){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //用户地址列表
    public function showaddressAction(){
        $id = $_POST['id'];
        $field = "addr_id,user_name,user_tel,user_province,user_city,user_area,user_addr,user_id as id";
        $data = $this->db->action("SELECT {$field} FROM zs_user_addr WHERE user_id = {$id}");
        if(!empty($data)){
            $this->ajax_return(0,$data);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //删除地址列表
    public function deladdressAction(){
        $id = $_POST['id'];
        $addr_id = $_POST['addr_id'];
        $data = $this->db->action($this->db->deleteSql("user_addr"," addr_id = {$addr_id} and user_id = {$id}"));
        if($data){
            $this->ajax_return(0);
        }else{
            $this->ajax_return(500);
        }
    }
    //宠爱优品-banner与推荐
    public function shopmixedAction(){
        $data['banner'] = $this->db->field("*")->table("zs_merc_banner")->select();
        $len = $this->db->zscount("shop");
        $stat = ($len - 10)<=0 ? $len : $len - 10;
        $start = rand(0,ceil($stat/2));
        $arrData = $this->db->action("SELECT id as shop_id,shop_name,shop_banner,type FROM zs_shop LIMIT {$start},10");
        $arr = [];
        foreach ($arrData as $k=>$v){
            $arr[$k]['shop_id'] = $v['shop_id'];
            $arr[$k]['shop_name'] = $v['shop_name'];
            $banner = json_decode($v['shop_banner'],true);
            $arr[$k]['shop_banner'] = $banner['shop_banner1'];
            $arr[$k]['type'] = $v['type'];
        }
        $data['recommend'] = $arr;
        $this->ajax_return(0,$data);
    }
    //宠爱优品-优品
    public function shoplistAction(){
        $currentPage = empty($_POST['page']) ? "1" : $_POST['page'];
        $showPage = empty($_POST["showpage"]) ? "10" : $_POST["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $arrData = $this->db->action("SELECT id as shop_id,shop_name,shop_banner,type FROM zs_shop WHERE status <> 3 ORDER BY id DESC LIMIT {$start},{$showPage}");
        $arr = [];
        foreach ($arrData as $k=>$v){
            $arr[$k]['shop_id'] = $v['shop_id'];
            $arr[$k]['shop_name'] = $v['shop_name'];
            $banner = json_decode($v['shop_banner'],true);
            $arr[$k]['shop_banner'] = $banner['shop_banner1'];
            $arr[$k]['type'] = $v['type'];
        }
        if(!empty($arr)){
            $this->ajax_return(0,$arr);
        }else{
            $this->ajax_return(0,[]);
        }
    }
    //生成订单接口
    public function buildorderAction(){
        $data['order_id'] = null;
        $data['user_id'] = $_POST['id'];
        $data['user_name'] = $_POST['user_name'];
        $data['user_tel'] = $_POST['user_tel'];
        $data['user_location'] = $_POST['user_province']." ".$_POST['user_city']." ".$_POST['user_area']." ".$_POST['user_addr'];
        $data['shop_banner'] = $_POST['shop_banner'];
        $data['shop_name'] = $_POST['shop_name'];
        $data['shop_type'] = $_POST['shop_type'];
        $data['shop_price'] = $_POST['shop_price'];
        $data['shop_num'] = $_POST['shop_num'];
        $data['shop_total_price'] = $_POST['shop_total_price'];
        $data['order_info'] = $_POST['order_info'];
        $data['pay_time'] = time();
        $data['pay_type'] = "支付宝";
        $data['pay_status'] = 0;
        $data['out_trade_no'] =  empty($_POST['out_trade_no'])?$this->out_trade_no():$_POST['out_trade_no'];
      //  $data['trade_no'] = empty($_POST['trade_no']) ? " " : $_POST['trade_no'];
        $data['express_status'] = 0;
        $data['close_status'] = 1;
        $bool = $this->db->action($this->db->insertSql('shop_order',$data));
        if($bool){
            $aliPayString=$this->apppay($_POST['shop_name'],$_POST['shop_name'],$data['out_trade_no'],$data['shop_total_price']);
            $this->ajax_return(0,$aliPayString);
        }else{
            $this->ajax_return(500);
        }
    }
    //支付宝app回调
    public function alipaynotifyAction(){
        $data = $_POST;
        file_put_contents(APP_PATH."/logpay.txt",json_encode($data).PHP_EOL."\r\n",FILE_APPEND);//确认是支付宝回调的
        //商户订单号
        $order_id     = $data['out_trade_no'];
        //交易状态
        $trade_status = $data['trade_status'];
        //查看是否存在该未支付订单，下面可以根据自己业务逻辑编写
        $result= $this->db->field("*")->table("zs_shop_order")->where("out_trade_no = '{$order_id}' and pay_status=0")->find();
        //存在该订单并支付成功，进行修改
        if ($result && $trade_status=="TRADE_SUCCESS") {
            $sqlData = array(
                "pay_status" => 1,
                'trade_no'=>$data['trade_no']
            );
            $res = $this->db->action($this->db->updateSql("shop_order",$sqlData,"out_trade_no = '{$order_id}'"));
            //修改成功
            if ($res) {
                file_put_contents(APP_PATH."/logpay.txt",$order_id.":修改商城订单数据成功"."\r\n",FILE_APPEND);//确认是支付宝回调的
            }
        }
        elseif ($trade_status=="TRADE_CLOSED")
        {
            file_put_contents(APP_PATH."/logpay.txt",$order_id.":支付交易超时"."\r\n",FILE_APPEND);//确认是支付宝回调的
        }
        else{

            file_put_contents(APP_PATH."/logpay.txt",$order_id.":支付失败"."\r\n",FILE_APPEND);//确认是支付宝回调的
        }

    }
}
