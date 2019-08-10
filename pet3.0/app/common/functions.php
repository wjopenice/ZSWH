<?php
use app\core\Pdb;

if(!function_exists("isMobile")){
    function isMobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger','huawei');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('p')) {
    function p($data) {
        if(is_bool($data) || is_null($data)){
            var_dump($data);
        }

        if(is_array($data) || is_object($data) || is_resource($data)){
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        }

        if(is_int($data) || is_string($data) || is_float($data)){
            echo $data;
        }

        exit;
    }
}

if(!function_exists('showPage')){
    function showPage($page,$url){
        $start = ($page->current>2)?($page->current-2):1;
        $end = ($page->current+1)>$page->total_items?($page->total_items-1):$page->current+1;
        $strpage = "";
        $strpage .= "<div id='pages'>";
        $strpage .= \Phalcon\Tag::linkTo($url."?page=".$page->first, "首页");
        if($page->current == $page->first){
            $strpage .= \Phalcon\Tag::linkTo([$url.'?page='.$page->previous, '<i class="layui-icon"></i>','class'=>'layui-disabled']);
        }else{
            $strpage .= \Phalcon\Tag::linkTo($url.'?page='.$page->previous, '<i class="layui-icon"></i>');
        }
        for ($i=$start;$i<=$end;$i++){
            if($i == $page->current){
                $strpage .= \Phalcon\Tag::linkTo([$url.'?page='.$i, $i,'class' => 'active']);
            }else{
                $strpage .= \Phalcon\Tag::linkTo($url.'?page='.$i, $i);
            }
        }
        if($page->current == $page->total_pages){
            $strpage .= \Phalcon\Tag::linkTo([$url.'?page='.$page->next, '<i class="layui-icon"></i>','class'=>'layui-disabled']);
        }else{
            $strpage .= \Phalcon\Tag::linkTo($url.'?page='.$page->next, '<i class="layui-icon"></i>');
        }
        $strpage .= \Phalcon\Tag::linkTo($url.'?page='.$page->last, '末页');
        $strpage .= "共".$page->total_items."条总共".$page->current." / ".$page->total_items;
        $strpage .= "</div>";
        return $strpage;
    }
}

if (!function_exists('alertText')){
    function alertText($data,$url) {
        echo "<script>
    var divNode = document.createElement('div');
    divNode.setAttribute('id','msg');
    divNode.style.position = 'fixed';
    divNode.style.top = '50%';
    divNode.style.width = '400px';
    divNode.style.left = '50%';
    divNode.style.marginLeft = '-220px';
    divNode.style.height = '30px';
    divNode.style.lineHeight = '30px';
    divNode.style.marginTop = '-35px';
    var pNode = document.createElement('p');
    pNode.style.background = 'rgba(0,0,0,0.6)';
    pNode.style.width = '300px';
    pNode.style.color = '#fff';
    pNode.style.textAlign = 'center';
    pNode.style.padding = '20px';
    pNode.style.margin = '0 auto';
    pNode.style.fontSize = '16px';
    pNode.style.borderRadius = '4px';
    pNode.innerText = '".$data."';
    divNode.appendChild(pNode);
    var htmlNode = document.documentElement;
    htmlNode.style.background = 'rgba(0,0,0,0)';
    htmlNode.appendChild(divNode);
    var t = setTimeout(next,2000);
    function next(){
        htmlNode.removeChild(divNode);
        window.location.href='".$url."';
    }
    </script>";
    }
}
if (!function_exists('success')) {
    function success($msg,$url){
        echo "<script>alert('".$msg."');window.location.href='".$url."';</script>";
        exit;
    }
}

if (!function_exists('error')) {
    function error($msg){
        echo "<script>alert('".$msg."');window.history.back();</script>";
        exit;
    }
}
if (!function_exists('statusUrl')) {
    function statusUrl($bool,string $success_msg, string $success_url,string $error_msg){
        if($bool){
            success($success_msg,$success_url);
        }else{
            error($error_msg);
        }
    }
}
if (!function_exists('server')) {
    function server($data = null){
        if(is_null($data)){
            return $_SERVER;
        }else{
            $key = strtoupper($data);
            return $_SERVER[$key];
        }
    }
}
if (!function_exists('request')) {
    function request($data = null){
        if(is_null($data)){
            return $_REQUEST;
        }else{
            return $_REQUEST[$data];
        }
    }
}
if (!function_exists('post')) {
    function post($data = null){
        if(is_null($data)){
            return $_POST;
        }else{
            return $_POST[$data];
        }
    }
}
if (!function_exists('get')) {
    function get($data = null){
        if(is_null($data)){
            return $_GET;
        }else{
            return $_GET[$data];
        }
    }
}

if (!function_exists('files')) {
    function files($data = null){
        if(is_null($data)){
            return $_FILES;
        }else{
            return $_FILES[$data];
        }
    }
}

if (!function_exists('load_view')) {
    function load_view($filename=null){
        include_once APP_PATH."/views/{$filename}.phtml";
    }
}
//用户经验
if (!function_exists("user_bp")){
   function user_bp($user,$bp,$optime,$bp_type,$jf){
       $ptime = date("Y-m-d",$optime); //时间戳转为日期
       $zs_user_bp = new Zsuserbp();
       $zs_user = new Zsuser();
       $result = $zs_user_bp::findFirst(" user = {$user} and optime = '{$ptime}' ");
       $user_result = $zs_user::findFirst(" id = {$user} ");
       if(!empty($user_result)){
           if(empty($result)){
               //用户积分变动
               $user_result->user_exp = (int)$user_result->user_exp + $jf;
               $user_result->user_level = switch_user_level($user_result->user_exp);
               $user_result->save();
               //用户积分日志
               $zs_user_bp->user = $user;
               $zs_user_bp->bp = $bp;
               $zs_user_bp->optime = $ptime;
               $zs_user_bp->bp_type = $bp_type;
               $zs_user_bp->save();
           }
       }else{
           echo json_encode(['code'=>1004,'message'=>"手机号不存在，请注册"]);exit;
       }
   }
}
//用户等级
if (!function_exists('switch_user_level')) {
    function switch_user_level($level){
        $user_level = 1;
        switch(true){
            case $level>=0 && $level<50: $user_level = 1; break;
            case $level>=50 && $level<130: $user_level = 2; break;
            case $level>=130 && $level<290: $user_level = 3; break;
            case $level>=290 && $level<610: $user_level = 4; break;
            case $level>=610 && $level<1250: $user_level = 5; break;
            case $level>=1250 && $level<2530: $user_level = 6; break;
            case $level>=2530 && $level<5090: $user_level = 7; break;
            case $level>=5090 && $level<10210: $user_level = 8; break;
            case $level>=10210 && $level<20450: $user_level = 9; break;
            case $level>=20450 : $user_level = 10; break;
            default:$user_level = 1; break;
        }
        return $user_level;
    }
}

if(!function_exists('get_zs_type_name')){
    function get_zs_type_name($data){
       $zs_card_type = new Zscardtype();
       $arrdata = $zs_card_type::findFirstById($data)->toArray();
       return $arrdata['title'];
    }
}

if(!function_exists('get_zs_user_name')){
    function get_zs_user_name($data){
        $zs_user = new Zsuser();
        $arrdata = $zs_user::findFirstById($data);
        if($arrdata){
            return $arrdata->account;
        }else{
            return "";
        }
    }
}

if(!function_exists('total_amount')){
    function total_amount($data){
        $db = new Pdb();
        $pay_amount = $db->field("SUM(pay_amount) as total")
            ->table("zswealorder")->where("weal_id = {$data} and pay_status = 1")->find();
        if($pay_amount)
        {
            $total = $pay_amount['total'];
        }else{
            $total = "暂无";
        }
        return $total;
    }
}

if(!function_exists('get_zs_user_nikename')){
    function get_zs_user_nikename($data){
        $zs_user = new Zsuser();
        $arrdata = $zs_user::findFirstByUid($data);
        if($arrdata){
            if(!empty($arrdata->nick_name))
            {
                return parseHtmlemoji($arrdata->nick_name);
            }
            else{
                return $arrdata->uid;
            }

        }else{
            return "";
        }
    }
}

if(!function_exists('get_zs_news_type_name')){
    function get_zs_news_type_name($data){
        $str = "";
        switch ($data){
            case 0:$str = "首页N格";break;
            case 1:$str = "首页单格";break;
            case 2:$str = "首页视频";break;
        }
        return $str;
    }
}

if(!function_exists('get_zs_merc_name')){
    function get_zs_merc_name($data){
        $str = "";
        switch ($data){
            case "commercial":$str = "经营性";break;
            case "sex":$str = "性别";break;
            case "mobile_num":$str = "手机号";break;
            case "real_name":$str = "姓名";break;
            case "id_card":$str = "身份证号";break;
            case "address":$str = "所在地";break;
            case "info":$str = "说明";break;
            case "idcard":$str = "身份证正面";break;
            case "reidcard":$str = "身份证反面";break;
            case "handidcard":$str = "手持身份证";break;
            case "permit":$str = "营业执照";break;
            case "field1":$str = "场地照1";break;
            case "field2":$str = "场地照2";break;
            case "field3":$str = "场地照3";break;
            case "field4":$str = "场地照4";break;
        }
        return $str;
    }
}

if(!function_exists('get_zs_user_merc_status')){
    function get_zs_user_merc_status($id,$data){
        $zs_user_merc = new Zs_user_merc();
        $result = $zs_user_merc::findFirst(['columns'=>[$data],'conditions'=>'id = '.$id]);
        return $result->$data;
    }
}
if(!function_exists('get_userinfo_data')){
    function get_userinfo_data($uid){
        $db = new Pdb();
        $db->action("set names utf8mb4");
       $price = $db->field("sum(pay_amount) as commonweal_price")->table("zswealorder")->where("user_id = {$uid} and pay_status = 1")->find();
        $userdata['commonweal_price'] = ($price['commonweal_price'] == null)?"0.00":$price['commonweal_price'];
       $maptotal= count($db->field("*")->table("zsmap")->where("user_id = {$uid}")->select());
       $cardtotal= count($db->field("*")->table("zscard")->where("u_id = {$uid}")->select());
       $userdata['totalposts']=$maptotal+$cardtotal;
        return $userdata;
    }
}
/**
 * 对数组排序
 * @param $para 排序前的数组
 * return 排序后的数组
 */
 function argSort($para) {
    ksort($para);
    reset($para);
    return $para;
}
/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
 function createLinkstring($para) {
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
 function sign2($data) {
    //读取私钥文件
    $priKey = file_get_contents(APP_PATH."/core/pay/rsa_private_key.pem");//私钥文件路径
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

 function issign($lockdata,$sdkdata){
    $newdata = $this->sign($lockdata);
    if($sdkdata != $newdata){
        echo json_encode(['code'=>100,'message'=>"签名错误"]);exit;
    }else{
        return "ok";
    }
}
 function sign($data){
    $arrData = $this->argSort($data);
    $signstr = $this->strlink($arrData);
    return hash_hmac("sha256",$signstr,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
}
 function strlink($para) {
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
//获取ip
function getIp() {

    $arr_ip_header = array(
        'HTTP_CDN_SRC_IP',
        'HTTP_PROXY_CLIENT_IP',
        'HTTP_WL_PROXY_CLIENT_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    );
    $client_ip = 'unknown';
    foreach ($arr_ip_header as $key)
    {
        if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != 'unknown')
        {
            $client_ip = $_SERVER[$key];
            break;
        }
    }
    return $client_ip;
}

//证书编号
function build_cert_no(){
    $db = new Pdb();
    $count=count($db->action("select * from zswealorder where  pay_status=1"));
    $sourceNumber = $count+1;
    $newNumber = substr(strval($sourceNumber+100000),1,6);
    return "CIZJ".date("Y").$newNumber;
}
//判断时间在今天、昨天、前天、几天前几点
function get_time($targetTime)
{
    // 今天最大时间
    $todayLast   = strtotime(date('Y-m-d 23:59:59'));
    $agoTimeTrue = time() - $targetTime;
    $agoTime     = $todayLast - $targetTime;
    $agoDay      = floor($agoTime / 86400);
    if ($agoTimeTrue < 60) {
        $result = '刚刚';
    } elseif ($agoTimeTrue < 3600) {
        $result = (ceil($agoTimeTrue / 60)) . '分钟前';
    } elseif ($agoTimeTrue < 3600 * 12) {
        $result = (ceil($agoTimeTrue / 3600)) . '小时前';
    } elseif ($agoDay == 0) {
        $result = '今天 ' ;
    } elseif ($agoDay == 1) {
        $result = '昨天 ' ;
    } elseif ($agoDay == 2) {
        $result = '前天 ';
    } elseif ($agoDay > 2 && $agoDay < 16) {
        $result = $agoDay . '天前 ';
    } else {
        $format = date('Y') != date('Y', $targetTime) ? "Y-m-d H:i" : "m-d H:i";
        $result = date($format, $targetTime);
    }
    return $result;
}


 function parseHtmlemoji ($text)
{
    $text_r = preg_replace_callback('/@E(.{6}==)/', function ($r) {
        return base64_decode($r[1]);
    }, $text);
    return $text_r;
}
 function parseEmojiTounicode($stremoji)
{
    $text = preg_replace_callback('/[\xf0-\xf7].{3}/', function ($r) {
        return '@E' . base64_encode($r[0]);
    }, $stremoji);
    return $text;
}


