<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 13:21
 */


function get_stat($time){
    $db = new dbModel();
    $count = $db->field("distinct *")->table("tab_user_checkin")->where(" create_time = '{$time}' ")->select();
    return count($count);
}

function get_user_checkin_num($data){
    $db = new dbModel();
    $leveldata = $db->field("vip_level")->table("tab_user")->where(" id = {$data} ")->find();
    $arrx = $db->field("num")->table("tab_user_sign_num")->where("level = {$leveldata['vip_level']}")->find();
    return $arrx['num'];
}

function get_game_type_name($data){
    $db = new dbModel();
    $arrdata = $db->field("id,game_type_name")->table("tab_game")->where(" id = {$data} ")->find();
    return $arrdata['game_type_name'];
}

function is_follows($u_id,$group_type_id){
    $db = new dbModel();
    $result=$db->field("*")->table("tab_group")->where("user_id = {$u_id} and group_type_id = {$group_type_id}")->find();
    if(!empty($result)){
        return 1;
    }else{
        return 0;
    }
}
function get_cover($cover_id, $field = null){
    if(empty($cover_id)){
        return false;
    }
    $db = new dbModel();
    $picture=$db->field("*")->table("sys_picture")->where("id = {$cover_id}")->find();
    if($field == 'path'){
        if(!empty($picture['url'])){
            $picture['path'] = $picture['url'];
        }else{
            $picture['path'] = "http://www.zhishengwh.com".$picture['path'];
        }
    }
    return empty($field) ? $picture : $picture[$field];
}
function is_user_follows($pf_id,$u_id){
    $db = new dbModel();
    $result=$db->field("*")->table("tab_user_follow")->where(" pf_id = {$pf_id} and user_id = {$u_id}")->find();
    if(!empty($result)){
        return 1;
    }else{
        return 0;
    }
}


function apk_pck_name($game_id){
    $db = new dbModel();
    $result=$db->field("apk_pck_name")->table("tab_game_set")->where(" game_id = {$game_id} ")->find();
    if(!empty($result)){
        return $result['apk_pck_name'];
    }else{
        return "";
    }
}


function get_news_type($data){
    $db = new dbModel();
    $arrdata = $db->field("id,type")->table("tab_news_type")->where(" id = {$data} ")->find();
    return $arrdata['type'];
}

function get_game_name($data){
    $db = new dbModel();
    $arrdata = $db->field("id,game_name")->table("tab_game")->where(" id = {$data} ")->find();
    return $arrdata['game_name'];
}
function get_coupon_name($data)
{
    $db = new dbModel();
    $arrdata = $db->field("amount")->table("tab_coupons")->where(" id = {$data} ")->find();
    return $arrdata['amount'];
}
function get_giftbag_name($data)
{
    $db = new dbModel();
    $arrdata = $db->field("giftbag_name")->table("tab_giftbag")->where(" id = {$data} ")->find();
    return $arrdata['giftbag_name'];
}
function get_group_type_name($data){
    $db = new dbModel();
    $arrdata = $db->field("id,game_name")->table("tab_group_type")->where(" id = {$data} ")->find();
    return $arrdata['game_name'];
}

function get_group_type_id($data){
    $db = new dbModel();
    $arrdata = $db->field("id")->table("tab_group_type")->where(" game_id = {$data} ")->find();
    return $arrdata['id'];
}

function get_user_name($data){
    $db = new dbModel();
    $arrdata = $db->field("id,account")->table("tab_user")->where(" id = {$data} ")->find();
    return $arrdata['account'];
}

function foreachoption($data,$classify='arr',$id = 'id',$type = 'type'){
    $optionstr = "";
    foreach ($data as $key=>$value){
        if($classify == 'arr'){
            $optionstr .= "<option value='".$value[$id]."'>".$value[$type]."</option>";
        }else{
            $optionstr .= "<option value='".$value->$id."'>".$value->$type."</option>";
        }
    }
    echo $optionstr;
}

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
        $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger');
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
function isWechat() {
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
        $clientkeywords = array('MicroMessenger');
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
function isiphone() {
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
        $clientkeywords = array('iphone','ipod');
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
function statusUrl($bool,string $success_msg, string $success_url,string $error_msg){
    if($bool){
        success($success_msg,$success_url);
    }else{
        error($error_msg);
    }
}
function server($data = null){
    if(is_null($data)){
        return $_SERVER;
    }else{
        $key = strtoupper($data);
        return $_SERVER[$key];
    }
}
function request($data = null){
    if(is_null($data)){
        return $_REQUEST;
    }else{
        return $_REQUEST[$data];
    }
}
function post($data = null){
    if(is_null($data)){
        return $_POST;
    }else{
        return $_POST[$data];
    }
}
function get($data = null){
    if(is_null($data)){
        return $_GET;
    }else{
        return $_GET[$data];
    }
}
function files($data = null){
    if(is_null($data)){
        return $_FILES;
    }else{
        return $_FILES[$data];
    }
}
function load_view($filename=null){
    include_once APP_PATH."/application/views/{$filename}.phtml";
}
function p($data){
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
function dump($data){
    switch (true){
        case is_string($data) || is_int($data) || is_float($data): echo $data ; break; exit;
        case is_array($data) || is_object($data) : echo "<pre>";print_r($data);echo "</pre>"; break;exit;
        case is_bool($data) || is_null($data) : var_dump($data) ; break;exit;
        default: var_dump($data) ;break;exit;
    }
    exit;
}
//写一个数据类型的检测
function dataType($data){
    if(is_string($data)){
        echo "这是字符串";
    }else if(is_int($data)){
        echo "这是整型";
    }else if(is_object($data)){
        echo "这是对象";
    }else if(is_float($data)){
        echo "这是浮点类型";
    }else if(is_bool($data)){
        echo "这是布尔类型";
    }else if(is_null($data)){
        echo "这是NULL";
    }else if(is_array($data)){
        echo "这是数组";
    }else{
        echo "这是资源类型";
    }
    exit;
}
//删除文件
function file_delete($filename=null,$mktime=null){
    if(file_exists($filename)){
        $t1 = fileatime($filename);//获取上一次访问时间
        $t2 = time(); //获取本次访问时间
        $t3 = $t2-$t1;//时间差
        $t4 = $mktime;// 过期时间秒
        if($t3 >= $t4){//过期
            unlink($filename); //删除文件
        }
    }else{
        die($filename." not file code：404");
    }
}
//强化readfile函数安全
function Exreadfile($fileName = null,$tags=true){
    if($tags){
        ob_start();//打开输出缓冲
        readfile($fileName);  //写数据到输出缓冲
        $strData = ob_get_flush();//提前输出缓冲数据和关闭
        ob_clean();//清空输出缓冲里面的内容
        return htmlspecialchars($strData);
    }else{
        ob_start();//打开输出缓冲
        readfile($fileName);  //写数据到输出缓冲
        $strData = ob_get_flush();//提前输出缓冲数据和关闭
        ob_clean();//清空输出缓冲里面的内容
        return $strData;
    }
}
//点击率
function file_addclick($fileName = null){
    $L = filesize($fileName)+1;
    $fileRes1 = fopen($fileName,"r");
    $str = fread($fileRes1,$L);
    $str+=1;
    $fileRes2 = fopen($fileName,"w+");
    fwrite($fileRes2,$str);
    rewind($fileRes2);
    return fread($fileRes2,$L);
}
//PHP生成日历
//function datetime(){
//    $y = isset($_GET['y'])?$_GET['y']:date("Y"); //当前年
//    $m = isset($_GET['m'])?$_GET['m']:date("m"); //当前月
//    $d = isset($_GET['d'])?$_GET['d']:date("d"); //当前日
//    $days = date("t",mktime(0,0,0,$m,$d,$y));//获取当月的天数
//    $statweek = date("w",mktime(0,0,0,$m,1,$y));//获取当月的第一天是星期几
//    $str = "<style>table{background: #999;}td,th{background: white;}</style>";
//    $str .="<table style='height: 100%' width='100%' align='center' cellpadding='0' cellspacing='1'>";
//    //$str .="<p>当前为{$y}年{$m}月</p>";
//    $str .="<tr><th>星期天</th><th>星期一</th><th>星期二</th><th>星期三</th><th>星期四</th><th>星期五</th><th>星期六</th></tr>";
//    $str .="<tr>";
//    for($i=0;$i<$statweek;$i++){
//        $str .="<td align='center'>&nbsp;</td>";
//    }
//    for($j=1;$j<=$days;$j++){
//        $i++;
//        $str .="<td align='center' onclick='alert({$j})' bgcolor='white'>";
//        $str .= "<img src='/public/img/index/icon_liwu.png'/><div style='text-align: center;width: 100%;'><div style='display: inline-block;text-align: center;box-shadow: 1px 1px 10px #bababa;border-radius: 8px;padding: 5px 10px;background: #f5d44c;color: white; '>{$j}天</div></div>";
//        $str .="</td>";
//        if($i % 7 == 0){
//            $str .="</tr><tr>";
//        }
//    }
//    while($i % 7 !== 0){
//        $str .="<td align='center'>&nbsp;</td>";
//        $i++;
//    }
//    $str .="</tr>";
//    $str .="</table>";
//    return $str;
//}
//转静态化
function static_page($url,$descname){
    set_time_limit(0);
    //实现HTML静态化
    $data = base64_encode(file_get_contents($url));
    file_put_contents($descname,$data);  //W+
    $strData = base64_decode(file_get_contents($descname));
    return $strData;
}
//文件下载
function apkdownload($file){
    if(file_exists($file)){
        header("Content-type:application/vnd.android.package-archive");
        $filename = basename($file);
        header("Content-Disposition:attachment;filename = ".$filename);
        header("Accept-ranges:bytes");
        header("Accept-length:".filesize($file));
        readfile($file);
    }else{
        echo "<script>alert('文件不存在')</script>";
    }
}
function StrX_shuffle($str=null){
    $a1 = range("a","z");
    shuffle($a1);
    $a2 = range("a","z");
    shuffle($a2);
    $a3 = range("a","z");
    shuffle($a3);
    $a4 = range("a","z");
    shuffle($a4);
    $a5 = range("a","z");
    shuffle($a5);
    $a6 = range("a","z");
    shuffle($a6);
    $strData = $str.$a1[0].$a2[0].$a3[0].$a4[0].$a5[0].$a6[0];
    return $strData;
}
//随机字符串
function Mer_shuffle($string,$maxlen = 20){
    $int_arr = range(0,9);
    $str_arr = range("a","z");
    $str1 = mb_splitchar($string);
    $new_arr = array_merge($int_arr,$str_arr);
    shuffle($new_arr);
    $strData = $str1.date("YmdHi",time()).implode($new_arr);
    $new_str = substr($strData,0,$maxlen);
    //file_put_contents("./c.html",$new_str);
    return $new_str;
}
//获取单个汉字拼音首字母。注意:此处不要纠结。汉字拼音是没有以U和V开头的
function getfirstchar($s0){
    $fchar = ord($s0{0});
    if($fchar >= ord("A") and $fchar <= ord("z") )return strtoupper($s0{0});
    $s1 = iconv("UTF-8","gb2312", $s0);
    $s2 = iconv("gb2312","UTF-8", $s1);
    if($s2 == $s0){$s = $s1;}else{$s = $s0;}
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if($asc >= -20319 and $asc <= -20284) return "A";
    if($asc >= -20283 and $asc <= -19776) return "B";
    if($asc >= -19775 and $asc <= -19219) return "C";
    if($asc >= -19218 and $asc <= -18711) return "D";
    if($asc >= -18710 and $asc <= -18527) return "E";
    if($asc >= -18526 and $asc <= -18240) return "F";
    if($asc >= -18239 and $asc <= -17923) return "G";
    if($asc >= -17922 and $asc <= -17418) return "H";
    if($asc >= -17922 and $asc <= -17418) return "I";
    if($asc >= -17417 and $asc <= -16475) return "J";
    if($asc >= -16474 and $asc <= -16213) return "K";
    if($asc >= -16212 and $asc <= -15641) return "L";
    if($asc >= -15640 and $asc <= -15166) return "M";
    if($asc >= -15165 and $asc <= -14923) return "N";
    if($asc >= -14922 and $asc <= -14915) return "O";
    if($asc >= -14914 and $asc <= -14631) return "P";
    if($asc >= -14630 and $asc <= -14150) return "Q";
    if($asc >= -14149 and $asc <= -14091) return "R";
    if($asc >= -14090 and $asc <= -13319) return "S";
    if($asc >= -13318 and $asc <= -12839) return "T";
    if($asc >= -12838 and $asc <= -12557) return "W";
    if($asc >= -12556 and $asc <= -11848) return "X";
    if($asc >= -11847 and $asc <= -11056) return "Y";
    if($asc >= -11055 and $asc <= -10247) return "Z";
    return NULL;
}
//获取整条字符串汉字拼音首字母
function mb_splitchar($str){
    $strX = "";
    for($i=0;$i<mb_strlen($str);$i++){
        $strData = mb_substr($str,$i,1);
        if(ord($strData) > 160){
            $strX .= getfirstchar($strData);
        }else{
            $strX .= $strData;
        }
    }
    return $strX;
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
function generateToken($user_id,$account,$password){
    $str = $user_id.$account.$password.time().sp_random_string(7);
    $token = MD5($str);
    return $token;
}

/**
 *随机生成字符串
 *@param  $len int 字符串长度
 *@return string
 *@author 小纯洁
 */
function sp_random_string($len = 6) {
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}












