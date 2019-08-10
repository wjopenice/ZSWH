<?php
namespace app\core;
class Pdb{
    public $yafpdo;
    public $dbconfig;
    public $sqlstr;
	public $dbname;
    //构造函数
    public function __construct($db_config = array()) {
        $config = [
			"driver"=>"mysql",
			"hostname"=>"120.78.136.67",
			"username"=>"root",
			"password"=>"12345678",
			"dbname"=>"pettap",
			"port"=>"3306",
			"charset"=>"utf8",
            "prefix"=>"zs"
		];
        if(empty($db_config)){
        	$db_config = $config;
            $this->dbconfig = $db_config;
			$this->dbname = $config['dbname'];
        }else{
        	$this->dbname = $db_config['dbname'];
        }
        $this->yafpdo = $this->connect("mysql",$db_config["hostname"], $db_config["username"], $db_config["password"], $db_config["dbname"],$db_config["port"],$db_config["charset"]);
    }
    //数据库连接
    public function connect($driver, $dbhost, $dbuser, $dbpw, $dbname, $port, $charset) {
        $dsn = $driver.":dbname=".$dbname.";host=".$dbhost.";port=".$port.";charset=".$charset;

        try {
            $this->yafpdo = new \PDO($dsn, $dbuser, $dbpw);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
        return $this->yafpdo;
    }
    //获取写入ID
    public function getInsertId(){
        $getId = $this->yafpdo->lastInsertId();
        return  $getId;
    }
    //数据库删除
    public function deleteSql($tbname = null,$where=null){
        $sql = "DELETE FROM ".$tbname." WHERE {$where}";
        return  $sql;
    }
    //数据库增加
    public function insertSql($tbname = null,array $data=[]){
        $strkey = "";
        $strval = "";
        foreach ($data as $key=>$value){
            $strkey .= "`$key`,";
            if(is_int($value)){
                $strval .= "$value,";
            }else if(is_null($value)){
                $strval .= "null,";
            }else{
                $strval .= "'$value',";
            }
        }
        $sql = "INSERT INTO ".$tbname." (".substr($strkey,0,-1)." ) VALUES (".substr($strval,0,-1).")";
        return  $sql;
    }
    //数据库修改
    public function updateSql($tbname=null,array $data=[],$where=null){
        $strData = "";
        foreach ($data as $key=>$value){
            if(is_int($value)){
                $strData .= $key."=".$value.",";
            }else{
                $strData .= $key."='".$value."',";
            }
        }
        $sql = "UPDATE ".$tbname." SET ".substr($strData,0,-1)." WHERE {$where} ";
        return  $sql;
    }
    //单表求长度SELECT count(*) as total FROM zs_card
    public function zscount($table,$field = "*",$as = 'total',$where = null){
        if($where == null){
            $str = "SELECT count({$field}) AS {$as} FROM {$this->dbconfig['prefix']}{$table} ";
        }else{
            $str = "SELECT count({$field}) AS {$as} FROM {$this->dbconfig['prefix']}{$table} WHERE {$where}";
        }
        $result = $this->yafpdo->query($str);
        $data = $result->fetch(\PDO::FETCH_ASSOC);
        return $data[$as];
    }
    //多表求长度SELECT count(*) as total FROM zs_card
    public function zsoddcount($table,$from,$join,$field = "*",$as = 'total',$joinwhere = null){
        if($joinwhere == null){
            $str = "SELECT count({$field}) AS {$as} FROM {$this->dbconfig['prefix']}{$table} INNER JOIN {$this->dbconfig['prefix']}{$from} ON {$join}";
        }else{
            $str = "SELECT count({$field}) AS {$as} FROM {$this->dbconfig['prefix']}{$table} INNER JOIN {$this->dbconfig['prefix']}{$from} ON {$join} WHERE {$joinwhere}";
        }
        $result = $this->yafpdo->query($str);
        $data = $result->fetch(\PDO::FETCH_ASSOC);
        return $data[$as];
    }
    //执行SQL语句
    //$ftype = 2：返回一个索引为结果集列名的数组
    //$ftype = 4：返回一个索引为结果集列名和以0开始的列号的数组
    //$ftype = 6：返回 TRUE ，并分配结果集中的列值给 PDOStatement::bindColumn() 方法绑定的 PHP 变量。
    //$ftype = 8：返回一个请求类的新实例，映射结果集中的列名到类中对应的属性名。如果 fetch_style 包含 PDO::FETCH_CLASSTYPE（例如：PDO::FETCH_CLASS |PDO::FETCH_CLASSTYPE），则类名由第一列的值决定
    //$ftype = 9：更新一个被请求类已存在的实例，映射结果集中的列到类中命名的属性
    //$ftype = 1：结合使用 PDO::FETCH_BOTH 和 PDO::FETCH_OBJ，创建供用来访问的对象变量名
    //$ftype = 3：返回一个索引为以0开始的结果集列号的数组
    //$ftype = 5：返回一个属性名对应结果集列名的匿名对象
    //（注意：查询结果为多维数据，修改/增加/删除结果为布尔值）
    public function action($sql,$ftype = 2){
        if(stripos($sql,"SELECT") !==  false){
            try {
                $result = $this->yafpdo->query($sql);
                $data = $result->fetchAll($ftype);
                return $data;
            } catch (PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
            }
        }else{
            $bool = $this->yafpdo->exec($sql);
            return $bool;
        }
    }
	//SQL数据CREATE操作
	public function systemaction($sql,$ftype = 2){
		$result = $this->yafpdo->query($sql);
        $data = $result->fetchAll($ftype);
        return $data;
	}
	//SQL数据CREATE操作
	public function systemexec($sql){
        $bool = $this->yafpdo->exec($sql);
        return $bool;
	}
    //SQL链式操作显示的字段
    public function field(string $field = "*"){
        $this->sqlstr .= " SELECT {$field} ";
        return $this;
    }
    //SQL链式操作的表名
    public function table($table=null){
        $this->sqlstr .= " FROM {$table} ";
        return $this;
    }
    //SQL链式操作的条件
    public function where($where=null){
        $this->sqlstr .= " WHERE {$where} ";
        return $this;
    }
    //SQL链式操作的limit查询
    public function limit($start=0,$len=5){
        $this->sqlstr .= " LIMIT {$start},{$len} ";
        return $this;
    }
    //SQL链式操作的排序
    public function order($order = "id desc"){
        $this->sqlstr .= " ORDER BY {$order} ";
        return $this;
    }
    //SQL链式操作的like查询
    public function like($like=null){
        $data = preg_replace("/[%_\s]+/","",ltrim(addslashes($like)));
        $this->sqlstr .= " LIKE '%{$data}%' ";
        return $this;
    }
    //SQL链式操作的连接查询
    public function join($table=null,$join=null){
        $this->sqlstr .= " INNER JOIN ".$table." ON {$join} ";
        return $this;
    }
    //SQL链式操作的分组查询
    public function group($group=null){
        $this->sqlstr .= " GROUP BY {$group} ";
        return $this;
    }
    //SQL链式操作的正则查询
    public function regexp($regexp=null){
        $this->sqlstr .= " REGEXP '{$regexp}'";
        return $this;
    }
    //查询结果多维数组|SQL链式操作查询结果多维数组
    public function select(){
        $data = $this->action($this->sqlstr);
       // echo  $this->sqlstr;exit;
        $this->sqlstr = "";
        return $data;
    }
    //查询结果多维对象|SQL链式操作查询结果多维对象
    public function selectobj(){
        $data = $this->action($this->sqlstr,5);
        $this->sqlstr = "";
        return $data;
    }
    //查询结果一维数组|SQL链式操作查询结果一维数组
    public function find(){
        $result = $this->yafpdo->query($this->sqlstr);
        $data = $result->fetch(2);
        $this->sqlstr = "";
        return $data;
    }
    //查询结果一维对象|SQL链式操作查询结果一维对象
    public function findobj(){
        $result = $this->yafpdo->query($this->sqlstr);
        //$data = $result->fetch(\PDO::FETCH_OBJ);
         $data = $result->fetch(5);
        $this->sqlstr = "";
        return $data;
    }
    public function beginTransaction(){
        $this->yafpdo->beginTransaction();
    }
    public function rollback(){
        $this->yafpdo->rollback();
    }
    public function commit(){
        $this->yafpdo->commit();
    }
    public function getSql() {
        return $this->sqlstr;
        exit;
    }
}