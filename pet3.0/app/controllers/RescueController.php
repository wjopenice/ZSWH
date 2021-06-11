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
    const alipay_public_key = "";
    //商户私钥
    const merchant_private_key = "";
    //支付宝网关
    const gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //应用ID
    const app_id = "";
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
