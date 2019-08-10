<?php
$pdo = new PDO("mysql:host=120.78.136.67;dbname=pettap","root","12345678");
$time=time();
$result=$pdo->query("select order_id,expire_time from zsshoporder where pay_status=1 and express_status=0  and expire_time<{$time} order by expire_time desc limit 0,30");
$a = $result->fetchAll();
foreach ($a as $key=>$value)
{

   // echo "update  zsshoporder set express_status=2  WHERE order_id={$value['order_id']}";
        $pdo->query("update  zsshoporder set express_status=2  WHERE order_id={$value['order_id']}");
}