<?php
use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Http\Request;
require_once APP_PATH.'/core/alipay_app/aop/AopClient.php';
require_once APP_PATH.'/core/alipay_app/aop/request/AlipayTradeAppPayRequest.php';
use app\core\Pdb;
class RescueController extends Controller{
    public $error;
    public $zsrescue;
    //支付宝公钥
    const alipay_public_key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAkTerLUqqWaVGNTID0Di4Oni5XpX4QXbDySqXdC4/Yr++zknCHroxneEZMcLCt1qfjTG9N0ZlSWTC3SYwaYZvFagsNxk1og/v3kEyEfg29yTHPyI6OmKTQo4U/sejkxcLaYnp1VMiBnsiPv8ocE+S+4q0DKB9xSgj5iIVEbUL6xN7EBIpjN6PHr2ap3m2KkSil3ORocsFFhejz6kLNoWJQrqL31fUK1RTuavf9lWB34tJaJovE1Bf6G2F4+T2nxK0FzfUXAhblsK8qHG/4XNx0F39qtjauqQa91iDGHa6hgpqcYj0mr3haceSUP73m71R7bmIwfHZ89cyBIHoPNGTqwIDAQAB";
    //商户私钥
    const merchant_private_key = "MIIEpAIBAAKCAQEAuXd7XjAEWVKRrGoKQFYB5Vxm5Io3fb6tsovb+pG6AkoFsmlWBn9NCf6MqaOWxgl205ImHBnHRxbP2d/PzXZB3+Ng7gGzw4tipoT0VRkXPab93ZXKE+mgFvr/GZXOR3jsLNflqnu7zTvDNX8HvqeUBtndLb4nteHNXMEeNqT4m/8mO7DlQ7DJ64Dbd0L0WA4KR13w4h7R1dqMtgv5U6mIInszEhEdiGjOnB+VrL8EoY3LC5Hrn0rB4BNMDJ8hlao2jZPItVeI9E+Z0VhiX14HUJ5OeMWCPszl3FerkfrLr+NfFr+wdBBKMy3OJcHImcwisM+j6yvRI0X1OqxiDkh7awIDAQABAoIBAQCavqdPce7e/DaRTbSZ82kHju5Gt1APeb4BoBH94gL6D/rq3lqpdyO3OAzzKYwOVi0v39wuTA/qL41i8wu2GXpjLJteWks715uK5pnaOuIaTa+5Z1ZBAQfSxL9+AHEpTyp3S/fTJAQQ/FEm3IOAvt+SS8rwdJ07c1hekL79xu2rcW899NnF0ZCH+V94mbsE8Kyhg2WM7DDUM6LMA+8yIOpC4blEQhWTIGRJR3JuS79OeoJcn7fLQEQmjyH3WWJN1AEgHvTekyG8USV/qU3JuOyRaXnHDwyGlbVIzc4pa/DUmja7g/s5QfJIoFU3ye4Wq3377lCPnEa4DBQ3jhM5iMCBAoGBANr+3iu/yY6nROhQVb5SrG7VcBMvyavYRtnAZWfVcGTMKdct69MZxAJhZxm4RW4jUcZzhzKsRkApVUP/gi3mdgqH3LUG2OmqYOgWSuH0E6hVf273HsKJfrghvD/wg8hn3TRVJmCz52H6oo0cuuH6Odd88Tqf07NLmQpFW41XDWWrAoGBANjOQBXzx72iouv010Fw9EELk+XByvSFmMwk4YKLSg+AdAMfH8MvsGgNSmoEEtOQl527Twpg05q+NoWaT77KIHZUZTjbRAUaswR7CT5uMJjOxnyDm++i8VKJ6sG1FxCcvnEaz1u4efCrLVqbK5GKPSnR8xyJx7VAdlLCB/J/dAFBAoGAedi05MKg8q4+uMN58ZsuNbyrzwEXxHVhdmaGBW/MSUkPPppeS+ZaGLj5FGZiuxULus8suhUAQVK+Dkdrtv4zT0iolFBrABe8M2Wz5GRZS5/Gd4cnpjW6O9kJVMoNiMPBYAzAfa2bX/iD2N/TW0hORodN8MBcmbXGQOC2P73fxmECgYBCc1zzHYgQGKQk/CNp3GwQ77KCDlbdgYEmuPshnv2xKKbmOgjrM1e3XLN9MQhwLfY6kymTvb+9wyVE59ofWSZ//jgUKCh+BAPwkKFxsCZW/7GYgmIuHdwndzwr6QxLvC8mzZfWvgEqAd1h0wOUlTFP+xivm49Jf5uEnBIBgo0UwQKBgQCmo9LIa/xDwfEOfmFG/F3b9oQbSDiVWGsKVrWEqGIvMo0i4+zcnsDQVB7iKf6oH1oQ6AqDztDLXQxnZdh+7Zcq5U4o25q6ltc3az2HLS8ggWVDVrOnRbFJHcNAMJO74SNcZMvm0Gag3FbxYVDbk4hyLO3XE2/pPcfK8ZLPxJeA1Q==";
    //支付宝网关
    const gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //应用ID
    const app_id = "2018072060681440";
    const signkey="MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    public function initialize(){
        include APP_PATH."/core/Errorcode.php";
        $this->error = \app\core\Errorcode::getCodeConfig();
        $this->zsrescue = new Zsrescue();
        include APP_PATH."/core/Pdb.php";
    }
    public function ajax_return($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]],320);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data],320);exit;
        }
    }
    //捐助首页
    public function indexAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsrescue->index_model($reqdata);
        $this->ajax_return(0,$code);
    }
    //捐助详情
    public function detailAction(){
        $reqdata = $this->request->getPost();
        $code = $this->zsrescue->detail_model($reqdata);
        $this->ajax_return(0,$code);
    }
    //捐助下单
    public function createorderAction(){
        $reqdata = $this->request->getPost();
        $order=new Zsrescue();
        $code=$order->createorder($reqdata);
        if(!is_numeric($code))
        {
            $aliPayString=$this->apppay($code['title'],$code['title'],$code['out_trade_no'],$code['pay_amount']);
            $this->ajax_return(0,$aliPayString);
        }
        else{
            $this->ajax_return($code);
        }
    }
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
        $payData['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/callback/rescuenotify";
        $payData['timestamp'] = date("Y-m-d H:i:s",time());
        #对数组进行排序
        $param =argSort($payData);
        $arg =createLinkstring($param);
        $payData['sign'] = urlencode(sign2($arg));
        $datax = createLinkstring($payData);
        //返回给SDK控制器
        $sHtml['arg']  = $datax;
        $sHtml['sign'] =sign2($arg);
        $sHtml['out_trade_no'] = $out_trade_no;
        return $sHtml;
    }

    //宠爱之家App我的捐款
    public function mydonationAction()
    {
        $reqdata = $this->request->getPost();
        $order=new Zswealorder();
        $code=$order->mydonation($reqdata);
        $this->ajax_return(0,$code);
    }
    //捐助成功
    public function successdonationAction(){
        $reqdata = $this->request->getPost();
        $zswealorder=new Zswealorder();
        $result=$zswealorder::findFirst("order_number='{$reqdata['out_trade_no']}'");
        $user=Zsuser::findFirst("id={$result->user_id}");
        $rescue=Zsrescue::findFirst("r_id={$result->weal_id}");
        if(!empty($result->certno)) {
            $data['certno'] = $result->certno;
        }
        else{
           $count= count($zswealorder::find(" pay_status=1 and order_number!='{$reqdata['out_trade_no']}'"));
            $sourceNumber = $count+1;
            $newNumber = substr(strval($sourceNumber+100000),1,6);
            $data['certno']="CIZJ".date("Y").$newNumber;;
        }
        $data['nick_name']=parseHtmlemoji($user->nick_name)?$user->nick_name:$user->uid;
        $data['title']=$result->weal_title;
        $data['pay_amount']=$result->pay_amount;
        $data['pay_time']=date("Y年m月d日",$result->pay_time);
        $data['id']=$result->user_id;
        $data['r_id']=$result->weal_id;
        if(!empty($rescue->mech_name)) {
            $data['mech_name'] = $rescue->mech_name;
        }
        else{
            $data['mech_name'] ="";
        }
        $data['url']="http://test.pettap.cn/mobile/contributiondetail/{$result->weal_id}/$result->user_id";
        $this->ajax_return(0,$data);
    }

}