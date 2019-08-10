<?php
require_once APP_PATH.'/alipay_app/aop/AopClient.php';
require_once APP_PATH.'/alipay_app/aop/request/AlipayTradeAppPayRequest.php';
class IoscommonwealController extends IosbaseController
{
    public $db;
    public $user;
    //支付宝公钥
    const alipay_public_key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkTerLUqqWaVGNTID0Di4Oni5XpX4QXbDySqXdC4/Yr++zknCHroxneEZMcLCt1qfjTG9N0ZlSWTC3SYwaYZvFagsNxk1og/v3kEyEfg29yTHPyI6OmKTQo4U/sejkxcLaYnp1VMiBnsiPv8ocE+S+4q0DKB9xSgj5iIVEbUL6xN7EBIpjN6PHr2ap3m2KkSil3ORocsFFhejz6kLNoWJQrqL31fUK1RTuavf9lWB34tJaJovE1Bf6G2F4+T2nxK0FzfUXAhblsK8qHG/4XNx0F39qtjauqQa91iDGHa6hgpqcYj0mr3haceSUP73m71R7bmIwfHZ89cyBIHoPNGTqwIDAQAB";
    //商户私钥
    const merchant_private_key = "MIIEpAIBAAKCAQEAuXd7XjAEWVKRrGoKQFYB5Vxm5Io3fb6tsovb+pG6AkoFsmlWBn9NCf6MqaOWxgl205ImHBnHRxbP2d/PzXZB3+Ng7gGzw4tipoT0VRkXPab93ZXKE+mgFvr/GZXOR3jsLNflqnu7zTvDNX8HvqeUBtndLb4nteHNXMEeNqT4m/8mO7DlQ7DJ64Dbd0L0WA4KR13w4h7R1dqMtgv5U6mIInszEhEdiGjOnB+VrL8EoY3LC5Hrn0rB4BNMDJ8hlao2jZPItVeI9E+Z0VhiX14HUJ5OeMWCPszl3FerkfrLr+NfFr+wdBBKMy3OJcHImcwisM+j6yvRI0X1OqxiDkh7awIDAQABAoIBAQCavqdPce7e/DaRTbSZ82kHju5Gt1APeb4BoBH94gL6D/rq3lqpdyO3OAzzKYwOVi0v39wuTA/qL41i8wu2GXpjLJteWks715uK5pnaOuIaTa+5Z1ZBAQfSxL9+AHEpTyp3S/fTJAQQ/FEm3IOAvt+SS8rwdJ07c1hekL79xu2rcW899NnF0ZCH+V94mbsE8Kyhg2WM7DDUM6LMA+8yIOpC4blEQhWTIGRJR3JuS79OeoJcn7fLQEQmjyH3WWJN1AEgHvTekyG8USV/qU3JuOyRaXnHDwyGlbVIzc4pa/DUmja7g/s5QfJIoFU3ye4Wq3377lCPnEa4DBQ3jhM5iMCBAoGBANr+3iu/yY6nROhQVb5SrG7VcBMvyavYRtnAZWfVcGTMKdct69MZxAJhZxm4RW4jUcZzhzKsRkApVUP/gi3mdgqH3LUG2OmqYOgWSuH0E6hVf273HsKJfrghvD/wg8hn3TRVJmCz52H6oo0cuuH6Odd88Tqf07NLmQpFW41XDWWrAoGBANjOQBXzx72iouv010Fw9EELk+XByvSFmMwk4YKLSg+AdAMfH8MvsGgNSmoEEtOQl527Twpg05q+NoWaT77KIHZUZTjbRAUaswR7CT5uMJjOxnyDm++i8VKJ6sG1FxCcvnEaz1u4efCrLVqbK5GKPSnR8xyJx7VAdlLCB/J/dAFBAoGAedi05MKg8q4+uMN58ZsuNbyrzwEXxHVhdmaGBW/MSUkPPppeS+ZaGLj5FGZiuxULus8suhUAQVK+Dkdrtv4zT0iolFBrABe8M2Wz5GRZS5/Gd4cnpjW6O9kJVMoNiMPBYAzAfa2bX/iD2N/TW0hORodN8MBcmbXGQOC2P73fxmECgYBCc1zzHYgQGKQk/CNp3GwQ77KCDlbdgYEmuPshnv2xKKbmOgjrM1e3XLN9MQhwLfY6kymTvb+9wyVE59ofWSZ//jgUKCh+BAPwkKFxsCZW/7GYgmIuHdwndzwr6QxLvC8mzZfWvgEqAd1h0wOUlTFP+xivm49Jf5uEnBIBgo0UwQKBgQCmo9LIa/xDwfEOfmFG/F3b9oQbSDiVWGsKVrWEqGIvMo0i4+zcnsDQVB7iKf6oH1oQ6AqDztDLXQxnZdh+7Zcq5U4o25q6ltc3az2HLS8ggWVDVrOnRbFJHcNAMJO74SNcZMvm0Gag3FbxYVDbk4hyLO3XE2/pPcfK8ZLPxJeA1Q==";
    //支付宝网关
    const gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //应用ID
    const app_id = "2018072060681440";
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
        $payData['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/ioscommonweal/alipaynotify";
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

    //宠爱之家App公益列表
    public function weallistAction()
    {
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = 9;
        $start = ($currentPage - 1) * $showPage;

        $picdata = array(
          /*  array('pic' => '/rescue/bg_rescue3.png', 'url' => 'http://www.pettap.cn/welfare3.html'),
            array('pic' => '/rescue/bg_rescue4.png', 'url' => 'http://www.pettap.cn/welfare4.html'),*/
            array('pic' => '/rescue/bg_rescue5.png', 'url' => 'http://www.pettap.cn/welfare5.html')
        );
        //紧急
        $urgent = $this->db->action("SELECT id as weal_id ,title,pic,target_amount FROM zs_commonweal where is_urgent=1 ORDER BY id DESC ");
        foreach ($urgent as $key => $value) {
            $order_amount = $this->db->field("SUM(pay_amount) as total")->table("zs_weal_order")->where("weal_id = {$value['weal_id']} and pay_status = 1")->find();
            if($order_amount['total']==null)
            {
                $order_amount['total']=0;
            }
            $urgent[$key]['balance']=intval($value['target_amount'])-$order_amount['total'];
            $urgent[$key]['order_amount'] = $order_amount['total'];
        }
        //非紧急
        $wealData = $this->db->action("SELECT id as weal_id ,title,pic,target_amount FROM zs_commonweal where is_urgent=0 ORDER BY id DESC  limit {$start},{$showPage}");
        foreach ($wealData as $key => $value) {
            $order_amount = $this->db->field("SUM(pay_amount) as total")->table("zs_weal_order")->where("weal_id = {$value['weal_id']} and pay_status = 1")->find();
            if($order_amount['total']==null)
            {
                $order_amount['total']=0;
            }
            $wealData[$key]['balance']=intval($value['target_amount'])-$order_amount['total'];
            $wealData[$key]['order_amount'] = $order_amount['total'];
        }
        $data['picdata'] =array('pic' => '/rescue/bg_rescue5.png', 'url' => 'http://www.pettap.cn/welfare5.html');
        $data['urgent'] = $urgent;
        $data['non_urgent'] = $wealData;
        $this->ajax_return(0,$data);
    }

    //宠爱之家App公益详情
    public function detailAction()
    {
         $weal_id = get("weal_id");
        $oneData = $this->db->field("id as weal_id,title,content,target_amount")->table("zs_commonweal")->where("id = {$weal_id}")->find();
        if (empty($oneData)) {
          $this->ajax_return(1300);
        }

        $oneData['content'] = htmlspecialchars_decode($oneData['content']);
        $order_amount = $this->db->field("SUM(pay_amount) as total,COUNT(*) as order_count")->table("zs_weal_order")->where("weal_id = {$weal_id} and pay_status = 1")->find();
        if($order_amount['total']==null)
        {
            $order_amount['total']=0;
        }
        $oneData['order_amount'] = $order_amount['total'];
        $oneData['order_count'] = $order_amount['order_count'];
        //宠爱之家App获取某公益的捐款信息
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "6" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $sql = "select a.weal_id ,a.user_id,b.nick_name as user_nickname,a.user_account,a.pay_amount,a.pay_time,b.avatar,a.is_hide from zs_weal_order a left join zs_ios_user b on a.user_id=b.id where a.weal_id='" . $weal_id . "' 
        and a.pay_status=1 and a.pay_weal_status=1  order by a.pay_time desc LIMIT {$start},{$showPage}  ";
        $result = $this->db->action($sql);
        foreach ($result as $key => $value) {
            $result[$key]['pay_time'] = get_time($value['pay_time']);
           // $result[$key]['avatar']="/public/user/".$value['avatar'];
        }
        $data['wealdata'] = $oneData;
        $data['orderdata'] = $result;
        $this->ajax_return(0,$data);

    }

    //宠爱之家App捐款下单
    public function iospayAction(){
         $request =$_POST;
         if(self::signkey!==$request['key'])
        {
            echo json_encode(['code'=>100,'message'=>"签名错误"]);exit;
        }
        $user_id=$request['id'];
        $user= $this->db->field("account,nick_name,avatar")->table("zs_ios_user")->where("id = {$user_id}")->find();
        $weal_id=$request['weal_id'];
        $weal=$this->db->field("*")->table("zs_commonweal")->where("id = {$weal_id}")->find();
        if(empty($user)||empty($weal) || empty($request['pay_amount']))
        {
            echo json_encode(['code'=>102,'message'=>"缺少参数"]);exit;
        }
        $data['user_id']=$user_id;
        $data['user_account']=$user['account'];
        $data['user_nickname']=$user['nick_name'];
        $data['avatar']=$user['avatar'];
        $data['weal_id']=$request['weal_id'];
        $data['weal_title']=$weal['title'];
        $data['pay_amount']=$request['pay_amount'];
        $data['is_hide']=$request['is_hide'];
        $data['pay_time']=time();
        $data['pay_status']=0;
        $data['order_number']=build_order_no();
        $data['pay_weal_status']=0;
        $data['pay_way']=0;
        $data['spend_ip']=getIp();
      //  echo $this->db->insertSql("weal_order",$data);exit;
        $bool = $this->db->action($this->db->insertSql("weal_order",$data));
        if($bool)
        {
            $aliPayString=$this->apppay($weal['title'],$weal['title'],$data['order_number'],$request['pay_amount']);
            echo json_encode(['code'=>0,'message'=>"success",'data'=>$aliPayString]);exit;
        }
        else{
            echo json_encode(['code'=>1301,'message'=>"没有找到用户信息"]);exit;
        }

    }
    //支付宝app回调
    public function alipaynotifyAction(){
       /*file_put_contents(APP_PATH."/logpay.txt","get".json_encode($_GET).PHP_EOL."\r\n",FILE_APPEND);//确认是支付宝回调的
        file_put_contents(APP_PATH."/logpay.txt","post".json_encode($_POST).PHP_EOL."\r\n",FILE_APPEND);//确认是支付宝回调的
        file_put_contents(APP_PATH."/logpay.txt","input".json_encode(file_get_contents("php://input")).PHP_EOL."\r\n",FILE_APPEND);//确认是支付宝回调的
        exit;*/
        $data = $_POST;
       file_put_contents(APP_PATH."/logpay.txt",json_encode($data).PHP_EOL."\r\n",FILE_APPEND);//确认是支付宝回调的
       //商户订单号
            $order_id     = $data['out_trade_no'];
            //交易状态
            $trade_status = $data['trade_status'];
             //查看是否存在该未支付订单，下面可以根据自己业务逻辑编写
            $result= $this->db->field("*")->table("zs_weal_order")->where("order_number = '{$order_id}' and pay_status=0")->find();
           //存在该订单并支付成功，进行修改
            if ($result && $trade_status=="TRADE_SUCCESS") {
                $sqlData = array(
                    "pay_status" => 1,
                    "pay_weal_status"   => 1,
                    'pay_order_number'=>$data['trade_no']
                );
                $res = $this->db->action($this->db->updateSql("weal_order",$sqlData,"order_number = '{$order_id}'"));
               //修改成功
                if ($res) {
                    file_put_contents(APP_PATH."/logpay.txt",$order_id.":修改数据成功"."\r\n",FILE_APPEND);//确认是支付宝回调的
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
