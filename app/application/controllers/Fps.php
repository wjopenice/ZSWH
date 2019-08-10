<?php
use Yaf\Application;
use Yaf\Dispatcher;
class FpsController extends Yaf\Controller_Abstract{

    public function indexAction(){

    }

    public function oneAction(){

    }

    public function listAction(){
        $db = new dbModel();
        $data = $db->action("SELECT * FROM tab_pay_log order by  id desc");
        $this->getView()->assign(["data"=>$data]);
    }

    public function lockAction(){
        $db = new dbModel();
        $data = $db->action("SELECT * FROM tab_pay_log  ");
        $this->getView()->assign(["data"=>$data]);
    }
    //下发
    public function fiatdeliveryAction(){


    }
//下发订单
    public function fiatdeliverylistAction(){
        $db = new dbModel();
        $data = $db->action("SELECT * FROM tab_pay_fiatdelivery order by id desc");
        $this->getView()->assign(["data"=>$data]);

    }
    public function exchangeAction(){

    }


}