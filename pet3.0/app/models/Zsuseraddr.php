<?php
use Phalcon\Mvc\Model;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
class Zsuseraddr extends Model{
    public $addr_id;
    public $user_name;
    public $user_tel;
    public $user_province;
    public $user_city;
    public $user_area;
    public $user_addr;
    public $user_id;
    //添加用户地址
    public function incraddress($reqdata){
        $addr=new Zsuseraddr();
        $addr->user_name = $reqdata['user_name'];
        $addr->user_tel = $reqdata['user_tel'];
        $addr->user_province = $reqdata['user_province'];
        $addr->user_city = $reqdata['user_city'];
        $addr->user_area = $reqdata['user_area'];
        $addr->user_addr = $reqdata['user_addr'];
        $addr->user_id = $reqdata['id'];
        $bool = $addr->save();
        if($bool){
           return 0;
        }else{
           return 500;
        }
    }
    //用户地址列表
    public function showaddress($reqdata){
        $id = $reqdata['id'];
        $data = $this->modelsManager->createBuilder()
            ->columns(["addr_id","user_name","user_tel","user_province","user_city","user_area","user_addr","user_id as id"])
            ->from('Zsuseraddr')
            ->where("user_id = {$id}")
            ->orderBy('id DESC')
            ->getQuery()
            ->execute()
            ->toArray();
        if(!empty($data)){
            return $data;
        }else{
            return [];
        }
    }
    //删除地址列表
    public function deladdress($reqdata){
        $id = $reqdata['id'];
        $addr_id = $reqdata['addr_id'];
        $zsuseraddr=new Zsuseraddr();
        $data = $zsuseraddr::findFirst("addr_id={$addr_id} and user_id={$id}");
        if($data){
            $data->delete();
            return 0;
        }else{
            return 500;
        }
    }

}