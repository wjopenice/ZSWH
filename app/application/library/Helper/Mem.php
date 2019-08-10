<?php
namespace Helper;
use Yaf\Application;
class Mem{
    public $mem_conf;
    public $yaf_mem;
    //初始化
    public function __construct($memconf = array()){
        if(empty($memconf)){
            $memconf = Application::app()->getConfig()->mem;
            $this->mem_conf = $memconf;
        }
        $this->yaf_mem = $this->init($this->mem_conf['host'],$this->mem_conf['port'],$this->mem_conf['timeout']);
    }
    //连接
    public function init($host,$port,$timeout){
        try {
            $this->yaf_mem = new \Memcache;
            $this->yaf_mem->connect($host, $port,$timeout);
        } catch (\PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
        return $this->yaf_mem;
    }
    //写入数据
    public function insert($key,$value,$timeout =  30){
       $bool = $this->yaf_mem->add($key,$value,false,$timeout);
       return $bool;
    }
    //修改数据
    public function update($key,$value,$timeout =  30){
        $bool = $this->yaf_mem->set($key,$value,false,$timeout);
        return $bool;
    }
    //查询数据
    public function select($key){
        $data = $this->yaf_mem->get($key);
        return $data;
    }
    //删除数据
    public function del($key){
        $bool = $this->yaf_mem->delete($key);
        return $bool;
    }
    //清除服务器数据
    public function remove(){
        $bool = $this->yaf_mem->flush();
        return $bool;
    }
}