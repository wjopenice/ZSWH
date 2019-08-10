<?php
echo $_SERVER['HTTP_USER_AGENT'];
exit;

//$url = 'https://uat.entry.one/soapi/pay/unifiedorder';
//$key = 'oMqUe1gIeh2VomnqzESg2EGSHALXVKgM';
//$arr['app_id'] = 'sou0g5fq39nw';
//$arr['nonce_str'] = rand();
//$arr['body'] = '测试';
//$arr['out_trade_no'] = buildorderno();
//$arr['fee_type'] = 'CNY';
//$arr['total_fee'] = '0.01';
//$arr['notify_url'] = 'http://www.pettap.cn/test/notify';
//$arr['trade_type'] = 'MWEB2';
//$arr['m_user_id']='sou0g5fq39nw';
//$arr['m_user_nick']='no';
//
//$data_string = json_encode($arr);
//$header  = array('XPaySign:'.$data_string);
//
////$sign = $data_string.$key;
//
////$sign = hash_hmac('sha256', $sign, $key, true);
//$sign = strtolower(getSignature($data_string.$key, $key));
////$sign = strtolower(base64_encode($sign));
////echo $data_string ;
//$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json','Content-Length: ' . strlen($data_string),'XPaySign:'.$sign) );
//$result = curl_exec($ch);
//
//echo $result;
//
//
//function getSignature($str, $key) {
//    $signature = "";
//    if (function_exists('hash_hmac')) {
//        $signature = bin2hex(hash_hmac("sha256", $str, $key, true));
//    } else {
//        $blocksize = 64;
//        $hashfunc = 'sha1';
//        if (strlen($key) > $blocksize) {
//            $key = pack('H*', $hashfunc($key));
//        }
//        $key = str_pad($key, $blocksize, chr(0x00));
//        $ipad = str_repeat(chr(0x36), $blocksize);
//        $opad = str_repeat(chr(0x5c), $blocksize);
//        $hmac = pack('H*', $hashfunc(($key ^ $opad).pack('H*',$hashfunc(($key^$ipad).$str))));
//        $signature = bin2hex($hmac);
//    }
//    return $signature;
//}
//
//function buildorderno(){
//    return "X".date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8)."pay";
//}