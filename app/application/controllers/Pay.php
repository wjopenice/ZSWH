<?php
use Helper\Page;
use Helper\Idcard;
class PayController extends BaseController{
        //统一下单回调
        public function notifyAction(){
            $input = $this->input;
            file_put_contents(APP_PATH."/logpay.txt","input:".json_encode($input).PHP_EOL."end\r\n",FILE_APPEND);//确认是支付宝回调的
            //$data = json_decode($input,true);
            if($input['code'] == 0 && $input['msg'] == 'SUCCESS'){
                if($input['data']['biz_code'] == 0){
                    //通知上家
                    $res['status'] = 1;
                    $res['trade_no'] = $input['data']['biz_data']['transaction_id'];
                    $out_trade_no = $input['data']['biz_data']['out_trade_no'];
                    $sql = $this->db->updateSql('pay_log',$res,"out_trade_no = '{$out_trade_no}'");
                    $bool = $this->db->action($this->db->updateSql('pay_log',$res," out_trade_no = '{$out_trade_no}' "));
                    if($bool){
                        file_put_contents(APP_PATH."/logpay.txt","input:111end$sql\r\n",FILE_APPEND);//确认是支付宝回调的
                    }else{
                        file_put_contents(APP_PATH."/logpay.txt","input:222end$sql\r\n",FILE_APPEND);//确认是支付宝回调的
                    }
                    echo json_decode(['code'=>0,"msg"=>"SUCCESS"]);
                    //通知下家CURL
                }else{
                    file_put_contents(APP_PATH."/logpay.txt","input:444{$input['data']['biz_code']}end\r\n",FILE_APPEND);//确认是支付宝回调的
                }
            }else{
                file_put_contents(APP_PATH."/logpay.txt","input:333end\r\n",FILE_APPEND);//确认是支付宝回调的
            }
        }

        public function logAction(){
            $out_trade_no = $_POST['out_trade_no'];
            $data = $this->db->action("SELECT * FROM tab_pay_log WHERE out_trade_no='{$out_trade_no}'");
            if($data[0]['status'] == 1){
                echo 1;
            }else{
                echo 0;
            }
        }

        public function notify_fiatdeliveryAction(){
            $input = $this->input;
            file_put_contents(APP_PATH."/logpay.txt","notify_fiatdelivery:".json_encode($input).PHP_EOL."end\r\n",FILE_APPEND);//确认是支付宝回调的
            if($input['code'] == 0 && $input['msg'] == 'SUCCESS'){
                if($input['data']['biz_code'] == 0){
                    //通知上家
                    $res['status'] = 1;
                    $res['fee_type'] = $input['data']['biz_data']['fee_type'];
                    $res['delivery_order_id'] = $input['data']['biz_data']['delivery_order_id'];
                    $res['time_end'] = $input['data']['biz_data']['time_end'];
                    $out_trade_no = $input['data']['biz_data']['out_delivery_no'];
                    $sql = $this->db->updateSql('pay_fiatdelivery',$res,"out_delivery_no = '{$out_trade_no}'");
                    $bool = $this->db->action($this->db->updateSql('pay_fiatdelivery',$res," out_delivery_no = '{$out_trade_no}' "));
                    if($bool){
                        file_put_contents(APP_PATH."/logpay.txt","input:111下发回调$sql\r\n",FILE_APPEND);//下发回调
                    }else{
                        file_put_contents(APP_PATH."/logpay.txt","input:222下发回调$sql\r\n",FILE_APPEND);//下发回调
                    }
                    echo json_decode(['code'=>0,"msg"=>"SUCCESS"]);
                    //通知下家CURL
                }else{
                    file_put_contents(APP_PATH."/logpay.txt","input:444{$input['data']['biz_code']}end\r\n",FILE_APPEND);//下发回调
                }
            }else{
                file_put_contents(APP_PATH."/logpay.txt","input:333end\r\n",FILE_APPEND);//下发回调
            }
        }

        //下发
        public function fiatdeliveryAction(){
            $url = "https://f1.s101010.com/soapi/pay/fiatdelivery";
            $secret = "o56ks0QgVuUNUF5QS8Oyv87GSLjnOSNx";
            $data['app_id'] = "soylmn6olfj5";//appid soA7wtrusfSg
            $data['nonce_str'] = md5(rand(0,9999)); //随机字符串
            $data['fee_type'] = "CNY"; //币种
            $data['notify_url'] = "http://app.zhishengwh.com/pay/notify_fiatdelivery"; //回掉地址
            $data['out_delivery_no'] = hash_hmac("sha256",time(),$secret); //订单号
            $data['amount'] = $_POST['amount']; //金额
            $data['name'] = $_POST['name']; //姓名
            $data['phone'] = $_POST['phone']; //手机号
            $data['cert_type'] = $_POST['cert_type']; //证件类型
            $data['cert_no'] = $_POST['certno']; //证件号
            $data['account_type'] = $_POST['account_type']; //账户类型
            $data['card'] = trim($_POST['card']); //卡号
            $data['bank_code'] = $_POST['bankcode']; //银行编码
            $data['bank_general_name'] = $_POST['bank_general_name']; //银行名称
           // $data['bank_general_code'] = $_POST['bank_general_code']; //总联行号
            if(!empty($_POST['bank_general_code'])) {
                $data['bank_general_code'] = $_POST['bank_general_code']; //总联行号
            }
            if(!empty($_POST['bank_branch_code'])) {
                $data['bank_branch_code'] = $_POST['bank_branch_code']; //支行联行号
            }
            $this->db->action($this->db->insertSql('pay_fiatdelivery',["out_delivery_no"=>$data['out_delivery_no'],"amount"=>$_POST['amount']]));
            $data_string = json_encode($data);
           // print_r($data_string);exit;
            $sign = $data_string.$secret;
            $sign = $this->getSignature($sign, $secret);

            //统一下单
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json','Content-Length: ' . strlen($data_string),'ffbodysign:'.$sign) );
            $result = curl_exec($ch);
            $data = json_decode($result,true);
            if($data['code'] == 0 && $data['msg'] == 'ok'){
                echo json_encode(['msg'=>"提现申请下单成功，请到下发订单查询状态"],320);

            }else{
                echo json_encode(['msg'=>"提现申请下单失败，请重新下单"],320);

            }
        }
        //下发订单查询
        public function fiatdeliverylistAction(){
            $url = "https://f1.s101010.com/soapi/pay/deliveryquery";
            $out_delivery_no = $_POST['trade_no'];
            $secret = "o56ks0QgVuUNUF5QS8Oyv87GSLjnOSNx";
            $data['app_id'] = "soylmn6olfj5";//appid soA7wtrusfSg
            $data['nonce_str'] =  md5(rand(0,9999)); //随机字符串
            $items['out_delivery_no'] =$out_delivery_no; //商户订单号
             $data['items']=[$items];
            $data_string = json_encode($data,320);
            $sign = $data_string.$secret;
            $sign = $this->getSignature($sign, $secret);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json','Content-Length: ' . strlen($data_string),'ffbodysign:'.$sign) );
            $result = curl_exec($ch);
            print_r($result);
    //            $data = json_decode($result,true);
    //            echo "<pre>";
    //            print_r($result);
    //            echo "</pre>";
        }
        public function fpsAction(){
            $url = "https://f1.s101010.com/soapi/pay/unifiedorder";
            //$secret = "CYD3yzcod66PmVeNWhtUyFpwE82pmwJaZllNau5uQIqTs74y";
            //$secret = "6zhZ2sUSzRSKVNotenxiUpwE82pmwJaZllNaHE2jLxJGREvq74";
            //$data['app_id'] = "sogeqikgw05t";//appid soA7wtrusfSg
            $secret = "o56ks0QgVuUNUF5QS8Oyv87GSLjnOSNx";
            $data['app_id'] = "soylmn6olfj5";//appid soA7wtrusfSg
            $data['nonce_str'] = md5(rand(0,9999)); //随机字符串
            $data['m_user_id'] = ""; //商户用户id
            $data['m_user_nick']  = "";//商户昵称
            $data['body'] = "test".rand(0,9999); //商品描述
            $data['attach']  = "http://www.baidu.com"; //附加数据
            $data['out_trade_no'] = $_POST['out_trade_no']; //订单号
            $data['fee_type'] = "CNY"; //币种
            $data['total_fee'] = $_POST['total_fee']; //金额
            $data['pay_type'] = $_POST['pay_type']; //法币支付方式
            $data['time_start'] = date("YmdHis");
            $data['time_expire'] = date("YmdHis",time()+60);
            $data['notify_url'] = "http://app.zhishengwh.com/pay/notify"; //回掉地址
            $data['trade_type'] = "MWEB"; //交易类型
            $data['process_type'] = "F2F";
             $this->db->action($this->db->insertSql('pay_log',["out_trade_no"=>$data['out_trade_no'],"status"=>0]));

            $data_string = json_encode($data);
            $sign = $data_string.$secret;
            $sign = $this->getSignature($sign, $secret);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json','Content-Length: ' . strlen($data_string),'ffbodysign:'.$sign) );
            $result = curl_exec($ch);
            var_dump($result);

            exit;

            $data = json_decode($result,true);





            if($data['code'] == 0 && $data['msg'] == 'ok'){
                $url = $data['data']['biz_data']['mweb_url'];
                header('Content-Type: image/png');
                include APP_PATH."/vendor/phpqrcode/phpqrcode.php";
                //$pic = "http://".$_SERVER["SERVER_NAME"]."/public/2.png";
                ob_clean();
//                QRcode::png($url);

                $time = time();
                $dir = APP_PATH."/public/qrcode/".$time;
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $filename = "/".time().".png";
                QRcode::png($url,$dir.$filename,"L",3,5);
                echo "/public/qrcode/".$time.$filename;
                exit;
            }else{
                echo json_encode(['msg'=>"支付错误"],320);
                exit;
            }
        }
        //订单查询
        public function listAction(){
            $url = "https://f1.s101010.com/soapi/pay/orderquery";
            $out_trade_no = $_POST['trade_no'];
           /* $secret = "6zhZ2sUSzRSKVNotenxiUpwE82pmwJaZllNaHE2jLxJGREvq74";

            $data['app_id'] = "sogeqikgw05t"; //appid*/
            $secret = "o56ks0QgVuUNUF5QS8Oyv87GSLjnOSNx";
            $data['app_id'] = "soylmn6olfj5";//appid soA7wtrusfSg
            $data['nonce_str'] =  md5(rand(0,9999)); //随机字符串
            $data['transaction_id'] = $out_trade_no; //fps订单号
            //$data['out_trade_no'] = $out_trade_no; //商户订单号

            $data_string = json_encode($data);
            $sign = $data_string.$secret;
            $sign = $this->getSignature($sign, $secret);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json','Content-Length: ' . strlen($data_string),'ffbodysign:'.$sign) );
            $result = curl_exec($ch);
            echo $result;
//            $data = json_decode($result,true);
//            echo "<pre>";
//            print_r($result);
//            echo "</pre>";
        }
        //关闭查询
        public function lockAction(){
            $url = "https://f1.s101010.com/soapi/pay/closeorder";
            $out_trade_no = $_POST['out_trade_no'];
            $secret = "6zhZ2sUSzRSKVNotenxiUpwE82pmwJaZllNaHE2jLxJGREvq74";

            $data['app_id'] = "sogeqikgw05t"; //appid
            $data['nonce_str'] =  md5(rand(0,9999)); //随机字符串
            $data['out_trade_no'] = $out_trade_no; //商户订单号

            $data_string = json_encode($data);
            $sign = $data_string.$secret;
            $sign = $this->getSignature($sign, $secret);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json','Content-Length: ' . strlen($data_string),'ffbodysign:'.$sign) );
            $result = curl_exec($ch);
            echo $result;
        }
        //查询汇率
        public function exchangeAction(){
            $url = "https://f1.s101010.com/soapi/pay/rate";
            $secret = "6zhZ2sUSzRSKVNotenxiUpwE82pmwJaZllNaHE2jLxJGREvq74";

            $data['app_id'] = "sogeqikgw05t"; //appid
            $data['nonce_str'] =  md5(rand(0,9999)); //随机字符串
            $data['curr_a'] = $_POST['curr1'];;
            $data['curr_b'] = $_POST['curr2']; //币种

            $data_string = json_encode($data);
            $sign = $data_string.$secret;
            $sign = $this->getSignature($sign, $secret);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json','Content-Length: ' . strlen($data_string),'ffbodysign:'.$sign) );
            $result = curl_exec($ch);
            echo $result;
        }

        public function getSignature($str, $key) {
            $signature = "";
            if (function_exists('hash_hmac')) {
                $signature = bin2hex(hash_hmac("sha256", $str, $key, true));
            } else {
                $blocksize = 64;
                $hashfunc = 'sha1';
                if (strlen($key) > $blocksize) {
                    $key = pack('H*', $hashfunc($key));
                }
                $key = str_pad($key, $blocksize, chr(0x00));
                $ipad = str_repeat(chr(0x36), $blocksize);
                $opad = str_repeat(chr(0x5c), $blocksize);
                $hmac = pack(
                    'H*', $hashfunc(
                        ($key ^ $opad) . pack(
                            'H*', $hashfunc(
                                ($key ^ $ipad) . $str
                            )
                        )
                    )
                );
                $signature = bin2hex($hmac);
            }
            return $signature;
        }
}