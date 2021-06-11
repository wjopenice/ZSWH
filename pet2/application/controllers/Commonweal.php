<?php
use Yaf\Application;
use Yaf\Dispatcher;
require_once APP_PATH.'/alipay_app/aop/AopClient.php';
require_once APP_PATH.'/alipay_app/aop/request/AlipayTradeAppPayRequest.php';
class CommonwealController extends Yaf\Controller_Abstract
{
    public $db;
    public $user;
    //支付宝公钥
    const alipay_public_key = "";
    //商户私钥
    const merchant_private_key = "";
    //支付宝网关
    const gatewayUrl = "https://openapi.alipay.com/gateway.do";
    //应用ID
    const app_id = "";
    const signkey="MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    public function init()
    {
        $this->db = new dbModel();

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
        $payData['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/commonweal/paynotify";
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
    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    protected function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    protected function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }
    //RSA2签名
    public function sign2($data) {
        //读取私钥文件
        $priKey = file_get_contents(APP_PATH."/vendor/pay/rsa_private_key.pem");//私钥文件路径
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res ,OPENSSL_ALGO_SHA256);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }
    //后台公益列表
    public function monweallistAction()
    {
        include APP_PATH . "/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zscount("commonweal");
        $page->init($len, 13);
        $showstr = $page->show();
        $arrData = $this->db->action("SELECT * FROM zs_commonweal ORDER BY id DESC {$page->limit} ");
        foreach ($arrData as $key=>$value){
            $orderdata = $this->db->field("SUM(pay_amount) as raised_amount,COUNT(*) as donation_sum")->table("zs_weal_order")->where("weal_id = {$value['id']} and pay_status = 1")->find();
            $arrData[$key]['donation_sum'] = $orderdata['donation_sum'];
            $arrData[$key]['raised_amount'] = $orderdata['raised_amount'];
        }
        $this->getView()->assign(["arrData" => $arrData, "showstr" => $showstr]);
    }


    //后台流水账单
    public function stateorderAction()
    {
        include APP_PATH . "/application/core/Page.php";
        $page = new \app\core\Page();
        $len = $this->db->zsoddcount("weal_order", "commonweal", "zs_commonweal.id = zs_weal_order.weal_id");
        $page->init($len, 13);
        $showstr = $page->show();
        $sql = "
            SELECT w.id,w.user_id,com.title,w.order_number,w.pay_way,w.pay_amount,w.pay_status,w.pay_time 
            FROM zs_weal_order as w
            INNER JOIN zs_commonweal as com ON com.id = w.weal_id
            ORDER BY w.id DESC 
            {$page->limit} 
        ";
        $arrData = $this->db->action($sql);
        $this->getView()->assign(["arrData" => $arrData, "showstr" => $showstr]);
    }

    //后台增加公益
    public function addmonwealAction()
    {
        if ($this->getRequest()->isPost()) {
            Dispatcher::getInstance()->autoRender(false);
            if (!empty($_FILES['pic']['name'])) {
                $time = time();
                $dir = APP_PATH . "/public/commonweal/" . $time;
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $fileicon = files("pic");
                $pathicon = $dir . "/" . $fileicon['name'];
                move_uploaded_file($fileicon['tmp_name'], $pathicon);
                $fileArr = "/public/commonweal/" . $time . "/" . $fileicon['name'];
                $data = post();
                $data['content'] = htmlspecialchars($data['content']);
                $data['pic'] = $fileArr;
                $data['create_time'] = time();
                $bool = $this->db->action($this->db->insertSql("commonweal", $data));
                statusUrl($bool, "添加成功", "/commonweal/monweallist", "添加失败");
            } else {
                success("缺少图片资源", "/commonweal/addmonweal");
            }
        } else {
            $this->getView()->assign(["xxx" => "yyy"]);
        }
    }

    //后台修改公益
    public function monwealeditAction()
    {
        if ($this->getRequest()->isPost()) {
            Dispatcher::getInstance()->autoRender(false);
            $data = post();
            $data['content'] = htmlspecialchars($data['content']);
            if (!empty($_FILES['pic']['name'])) {
                $time = time();
                $dir = APP_PATH . "/public/commonweal/" . $time;
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $fileicon = files("pic");
                $pathicon = $dir . "/" . $fileicon['name'];
                move_uploaded_file($fileicon['tmp_name'], $pathicon);
                $fileArr = "/public/commonweal/" . $time . "/" . $fileicon['name'];
                $data['pic'] = $fileArr;
            }
            $this->db->action($this->db->updateSql("commonweal", $data, " id = {$data['id']}"));
            success("修改成功", "/commonweal/monweallist");
        } else {
            $id = get("id");
            $oneData = $this->db->field("*")->table("zs_commonweal")->where("id = {$id}")->find();
            $this->getView()->assign(["oneData" => $oneData]);
        }
    }

    //后台删除公益
    public function monwealdelAction()
    {
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $bool = $this->db->action($this->db->deleteSql("commonweal", "id = {$id}"));
        if ($bool) {
            echo json_encode(['msg' => "ok"]);
        } else {
            echo json_encode(['msg' => "no"]);
        }
    }

    //宠爱之家App公益列表
    public function indexAction()
    {
        Dispatcher::getInstance()->autoRender(false);
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "5" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $wealData = $this->db->action("SELECT id ,title,create_time,remark,pic,target_amount FROM zs_commonweal ORDER BY id DESC  limit {$start},{$showPage}");
        foreach ($wealData as $key => $value) {
            $wealData[$key]['weal_id']=$value['id'];
            $order_amount = $this->db->field("SUM(pay_amount) as total")->table("zs_weal_order")->where("weal_id = {$value['id']} and pay_status = 1")->find();
            if($order_amount['total']==null)
            {
                $order_amount['total']=0;
            }
            $wealData[$key]['order_amount'] = $order_amount['total'];
        }
        $picdata = array(
            array('pic' => '/rescue/bg_rescue3.png', 'url' => 'http://www.pettap.cn/welfare3.html'),
            array('pic' => '/rescue/bg_rescue4.png', 'url' => 'http://www.pettap.cn/welfare4.html'),
            array('pic' => '/rescue/bg_rescue5.png', 'url' => 'http://www.pettap.cn/welfare5.html')
        );
        $data['wealdata'] = $wealData;
        $data['picdata'] = $picdata;
        echo json_encode(['code' => 0, 'message' => "success", "data" => $data]);
        exit;
    }

    //宠爱之家App公益详情
    public function detailAction()
    {
        Dispatcher::getInstance()->autoRender(false);
        $weal_id = get("weal_id");
        $oneData = $this->db->field("*")->table("zs_commonweal")->where("id = {$weal_id}")->find();
        if (empty($oneData)) {
            echo json_encode(['code' => 1300, 'message' => "没有找到此公益"]);
        }

        $oneData['content'] = htmlspecialchars_decode($oneData['content']);
        $order_amount = $this->db->field("SUM(pay_amount) as total,COUNT(*) as order_count")->table("zs_weal_order")->where("weal_id = {$weal_id} and pay_status = 1")->find();
        if($order_amount['total']==null)
        {
            $order_amount['total']=0;
        }
        $oneData['order_amount'] = $order_amount['total'];
        $oneData['order_count'] = $order_amount['order_count'];
        $oneData['top_pic'] = '/rescue/bg_rescue5.png';
        //宠爱之家App获取某公益的捐款信息
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "5" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $sql = "select a.weal_id , a.user_id,b.nick_name as user_nickname,a.user_account,a.pay_amount,a.pay_time,b.avatar from zs_weal_order a left join zs_user b on a.user_id=b.id where a.weal_id='" . $weal_id . "' 
        and a.pay_status=1 and a.pay_weal_status=1  order by a.pay_time desc LIMIT {$start},{$showPage}  ";
        $result = $this->db->action($sql);
        foreach ($result as $key => $value) {
            $result[$key]['ago_time'] = get_time($value['pay_time']);
        }
        $oneData['weal_id']=$oneData['id'];
        $data['wealdata'] = $oneData;
        $data['orderdata'] = $result;
        echo json_encode(['code' => 0, 'message' => "success", 'data' => $data]);
        exit;

    }

    //宠爱之家App我的捐款
    public function mydonationAction()
    {
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $result = $this->db->field("id,uid,nick_name,account,avatar")->table("zs_user")->where("id={$id}")->find();
        $orderdata = $this->db->field("SUM(pay_amount) as order_amount,COUNT(*) as order_count")->table("zs_weal_order")->where("user_id = {$id} and pay_status = 1")->find();
      //宠爱之家App获取某用户的全部捐款信息
        $currentPage = empty($_GET["page"]) ? "1" : $_GET["page"];
        $showPage = empty($_GET["showpage"]) ? "5" : $_GET["showpage"];
        $start = ($currentPage - 1) * $showPage;
        $sql = "select a.weal_id,a.pay_amount,a.pay_time,a.pay_status,a.pay_weal_status,b.title ,b.pic,b.target_amount from zs_weal_order a  left join 
 zs_commonweal b on a.weal_id=b.id where a.user_id='" . $id . "'  and a.pay_status=1 and a.pay_weal_status=1  order by a.pay_time desc LIMIT {$start},{$showPage}  ";
        $orders = $this->db->action($sql);
        foreach ($orders as $key => $value) {
            $orders[$key]['pay_time']=date("Y-m-d H:i",$value['pay_time']);
            $pay_amount = $this->db->field("SUM(pay_amount) as order_amount")->table("zs_weal_order")->where("weal_id = {$value['weal_id']} and pay_status = 1")->find();
            $orders[$key]['order_amount'] = $pay_amount['order_amount'];
        }
        if($orderdata['order_amount']==null)
        {
            $orderdata['order_amount']=0;
        }
        $result['order_amount']=$orderdata['order_amount'];
        $result['order_count']=$orderdata['order_count'];
        $data['userdata'] = $result;
        $data['orderdata'] = $orders;
        if ($result) {
            echo json_encode(['code' => 0, 'message' => "success", 'data' => $data]);
            exit;
        } else {
            echo json_encode(['code' => 1301, 'message' => "没有找到用户信息"]);
            exit;
        }
    }
    //宠爱之家App捐款下单
    public function createorderAction(){
        Dispatcher::getInstance()->autoRender(false);
        $request =json_decode(file_get_contents("php://input"),true);
        if(self::signkey!==$request['key'])
        {
            echo json_encode(['code'=>100,'message'=>"签名错误"]);exit;
        }
        $user_id=$request['id'];
        $user= $this->db->field("account,nick_name,avatar")->table("zs_user")->where("id = {$user_id}")->find();
        $weal_id=$request['weal_id'];
        $weal=$this->db->field("*")->table("zs_commonweal")->where("id = {$weal_id}")->find();
        if(empty($user)||empty($weal) || empty($request['pay_amount'] || empty($request['weal_title'])))
        {
            echo json_encode(['code'=>102,'message'=>"缺少参数"]);exit;
        }
        $data['user_id']=$user_id;
        $data['user_account']=$user['account'];
        $data['user_nickname']=$user['nick_name'];
        $data['avatar']=$user['avatar'];
        $data['weal_id']=$request['weal_id'];
        $data['weal_title']=$request['weal_title'];
        $data['pay_amount']=$request['pay_amount'];
        $data['pay_time']=time();
        $data['pay_status']=0;
        $data['order_number']=build_order_no();
        $data['pay_weal_status']=0;
        $data['pay_way']=0;
        $data['spend_ip']=getIp();
        $bool = $this->db->action($this->db->insertSql("weal_order",$data));
        if($bool)
        {
            $aliPayString=$this->apppay($weal['title'],$request['weal_title'],$data['order_number'],$request['pay_amount']);
            echo json_encode(['code'=>0,'message'=>"success",'data'=>$aliPayString]);exit;
        }
        else{
            echo json_encode(['code'=>1301,'message'=>"没有找到用户信息"]);exit;
        }

    }
    //支付宝app回调
    public function paynotifyAction(){
        Dispatcher::getInstance()->autoRender(false);
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
