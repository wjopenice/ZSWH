<?php
use app\core\Pdb;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/18
 * Time: 14:18
 */
$pdb=new Pdb();
$result=$pdb->action("select order_id,expire_time from zsshoporder where pay_status=1 and express_status=0 order by expire_time desc limit 0,1");
foreach ($result as $key=>$value)
{
    if($value['expire_time']>time())
    {
        $data['express_status']=2;
        $pdb->action($pdb->updateSql("zsshoporder",$data,"order_id={$value['order_id']}"));
    }
}
