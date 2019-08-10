<?php
namespace app\core;
class Easemob{

    public $org_name = "1118190319055779";
    public $app_name = "pettap2";
    public $api = "https://a1.easemob.com/"; //接口地址
    public $client_id = "YXA6GQDh_VjdRAStYtjkW7eMxg";
    public $client_secret = "YXA6R91qhJdUNZl9hnCa3JtWdtb9SrA";
    public $url;
    public function __construct() {
         $this->url = $this->api.$this->org_name.'/'.$this->app_name.'/';
    }
    public function getToken()
    {
        $options=[
            "grant_type"=>"client_credentials",
            "client_id"=>$this->client_id,
            "client_secret"=>$this->client_secret
        ];
        $body=json_encode($options);
        $url=$this->url.'token';
        $tokenResult = $this->postCurl($url,$body,$header=array());
        return "Authorization: Bearer ".$tokenResult['access_token'];
    }
    /**
     *获取app中的所有群组----不分页
     */
    function getGroups($limit=0){
        if(!empty($limit)){
            $url=$this->url.'chatgroups?limit='.$limit;
        }else{
            $url=$this->url.'chatgroups';
        }
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,"GET");
        return $result;
    }
    /**
     *获取app中的所有群组---分页
     */
    function getGroupsForPage($limit=0,$cursor=''){
        $url=$this->url.'chatgroups?limit='.$limit.'&cursor='.$cursor;
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,"GET");
        if(!empty($result["cursor"])){
            $cursor=$result["cursor"];
            $this->writeCursor("groupfile.txt",$cursor);
        }
        return $result;
    }
    /**
     *获取一个或多个群组的详情
     */
    function getGroupDetail($group_ids){
        $g_ids=implode(',',$group_ids);
        $url=$this->url.'chatgroups/'.$g_ids;
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'GET');
        return $result;
    }
    /**
     *创建一个群组
     */
    function createGroup($options){
        $url=$this->url.'chatgroups';
        $header=[$this->getToken()];
        $body=json_encode($options);
        $result=$this->postCurl($url,$body,$header);
        return $result;
    }
    /**
    *修改群组信息
    */
    function modifyGroupInfo($group_id,$options){
        $url=$this->url.'chatgroups/'.$group_id;
        $body=json_encode($options);
        $header=[$this->getToken()];
        $result=$this->postCurl($url,$body,$header,'PUT');
        return $result;
    }
    /**
    *删除群组
    */
    function deleteGroup($group_id){
        $url=$this->url.'chatgroups/'.$group_id;
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'DELETE');
        return $result;
    }
    /**
    *获取群组中的成员
    */
    function getGroupUsers($group_id){
        $url=$this->url.'chatgroups/'.$group_id.'/users';
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'GET');
        return $result;
    }
    /**
    *群组单个加人
    */
    function addGroupMember($group_id,$username){
        $url=$this->url.'chatgroups/'.$group_id.'/users/'.$username;
        $header=[$this->getToken(),'Content-Type:application/json'];
        $result=$this->postCurl($url,'',$header);
        return $result;
    }
    /**
    *群组批量加人
    */
    function addGroupMembers($group_id,$usernames){
        $url=$this->url.'chatgroups/'.$group_id.'/users';
        $body=json_encode($usernames);
        $header=[$this->getToken(),'Content-Type:application/json'];
        $result=$this->postCurl($url,$body,$header);
        return $result;
    }
    /**
    *群组单个减人
    */
    function deleteGroupMember($group_id,$username){
        $url=$this->url.'chatgroups/'.$group_id.'/users/'.$username;
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'DELETE');
        return $result;
    }
    /**
    *群组批量减人
    */
    function deleteGroupMembers($group_id,$usernames){
        $url=$this->url.'chatgroups/'.$group_id.'/users/'.$usernames;
        //$body=json_encode($usernames);
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'DELETE');
        return $result;
    }
    /**
    *获取一个用户参与的所有群组
    */
    function getGroupsForUser($username){
        $url=$this->url.'users/'.$username.'/joined_chatgroups';
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'GET');
        return $result;
    }
    /**
    *群组转让
    */
    function changeGroupOwner($group_id,$options){
        $url=$this->url.'chatgroups/'.$group_id;
        $body=json_encode($options);
        $header=[$this->getToken()];
        $result=$this->postCurl($url,$body,$header,'PUT');
        return $result;
    }
    /**
    *查询一个群组黑名单用户名列表
    */
    function getGroupBlackList($group_id){
        $url=$this->url.'chatgroups/'.$group_id.'/blocks/users';
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'GET');
        return $result;
    }
    /**
    *群组黑名单单个加人
    */
    function addGroupBlackMember($group_id,$username){
        $url=$this->url.'chatgroups/'.$group_id.'/blocks/users/'.$username;
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header);
        return $result;
    }
    /**
    群组黑名单批量加人
    */
    function addGroupBlackMembers($group_id,$usernames){
        $url=$this->url.'chatgroups/'.$group_id.'/blocks/users';
        $body=json_encode($usernames);
        $header=[$this->getToken()];
        $result=$this->postCurl($url,$body,$header);
        return $result;
    }
    /**
    群组黑名单单个减人
    */
    function deleteGroupBlackMember($group_id,$username){
        $url=$this->url.'chatgroups/'.$group_id.'/blocks/users/'.$username;
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'DELETE');
        return $result;
    }
    /**
    群组黑名单批量减人
    */
    function deleteGroupBlackMembers($group_id,$usernames){
        $url=$this->url.'chatgroups/'.$group_id.'/blocks/users';
        $body=json_encode($usernames);
        $header=[$this->getToken()];
        $result=$this->postCurl($url,$body,$header,'DELETE');
        return $result;
    }

    /**
    加好友
     */
    function addFriend($owner_username,$friend_username){
        $url=$this->url.'users/'.$owner_username.'/contacts/users/'.$friend_username;
       // $body=json_encode($usernames);
        $header=[$this->getToken()];
        $result=$this->postCurl($url,'',$header,'post');
        return $result;
    }

    //发消息
    function sendmessage($options){
        $url=$this->url.'messages';
        $header=[$this->getToken()];
        $body=json_encode($options);
        $result=$this->postCurl($url,$body,$header);
        return $result;
    }
    //上传文件
    public function uploadfile($filePath){
        $url=$this->url.'chatfiles';
        $file=file_get_contents($filePath);
        $body['file']=$file;
       $header=[$this->getToken()];
        $result=$this->uploadCurl($url,$body,$header,'XXX');
        return $result;
    }
    public function uploadCurl($url,$body,$header,$type="POST"){
        //1.创建一个curl资源
        $ch = curl_init();
        //2.设置URL和相应的选项
        curl_setopt($ch,CURLOPT_URL,$url);//设置url
        //1)设置请求头
        array_push($header, 'restrict-access:true');
        array_push($header, 'Content-Type:multipart/form-data');
        //设置为false,只会获得响应的正文(true的话会连响应头一并获取到)
        curl_setopt($ch,CURLOPT_HEADER,0);
        //	curl_setopt ( $ch, CURLOPT_TIMEOUT,5); // 设置超时限制防止死循环
        //设置发起连接前的等待时间，如果设置为0，则无限等待。
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
        //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //2)设备请求体
        if (count($body)>0) {
           // $b=json_encode($body,true);
           // echo $b;exit;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);//全部数据使用HTTP协议中的"POST"操作来发送。
        }

        //设置请求头
        if(count($header)>0){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        }
        //上传文件相关设置
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);// 从证书中检查SSL加密算
        //3)设置提交方式
        switch($type){
            case "GET":
                curl_setopt($ch,CURLOPT_HTTPGET,true);
                break;
            case "POST":
                curl_setopt($ch,CURLOPT_POST,true);
                break;
            case "PUT"://使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请求。这对于执行"DELETE" 或者其他更隐蔽的HTT
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PUT");
                break;
            case "DELETE":
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"DELETE");
                break;
        }
        //4)在HTTP请求中包含一个"User-Agent: "头的字符串。-----必设
        //	curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
        //	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' );
        //5)模拟用户使用的浏览器
        //3.抓取URL并把它传递给浏览器
        $res=curl_exec($ch);
         $result=json_decode($res,true);
        //4.关闭curl资源，并且释放系统资源
        curl_close($ch);
        if(empty($result))
            return $res;
        else
            return $result;
    }

    /*
		发图片消息
	*/
    function sendImage($filePath,$from="admin",$target_type,$target,$filename,$ext){
        $result=$this->uploadFile($filePath);
        $uri=$result['uri'];
        $uuid=$result['entities'][0]['uuid'];
        $shareSecret=$result['entities'][0]['share-secret'];
        $url=$this->url.'messages';
        $body['target_type']=$target_type;
        $body['target']=$target;
        $options['type']="img";
        $options['url']=$uri.'/'.$uuid;
        $options['filename']=$filename;
        $options['secret']=$shareSecret;
        $options['size']=array(
            "width"=>480,
            "height"=>720
        );
       // print_r($options);exit;
        $body['msg']=$options;
        $body['from']=$from;
        $body['ext']=$ext;
        $b=json_encode($body);
        $header=array($this->getToken());
        //$b=json_encode($body,true);
        $result=$this->postCurl($url,$b,$header);
        return $result;
    }
    /**
     *$this->postCurl方法
     */
    public function postCurl($url,$body,$header,$type="POST"){
        //1.创建一个curl资源
        $ch = curl_init();
        //2.设置URL和相应的选项
        curl_setopt($ch,CURLOPT_URL,$url);//设置url
        //1)设置请求头
        //array_push($header, 'Accept:application/json');
        array_push($header, 'Content-Type:application/json');
        //array_push($header, 'http:multipart/form-data');
        //设置为false,只会获得响应的正文(true的话会连响应头一并获取到)
        curl_setopt($ch,CURLOPT_HEADER,0);
        //	curl_setopt ( $ch, CURLOPT_TIMEOUT,5); // 设置超时限制防止死循环
        //设置发起连接前的等待时间，如果设置为0，则无限等待。
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
        //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //2)设备请求体
        if (count($body)>0) {
            //$b=json_encode($body,true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);//全部数据使用HTTP协议中的"POST"操作来发送。
        }
        //设置请求头
        if(count($header)>0){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        }
        //上传文件相关设置
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);// 从证书中检查SSL加密算
        //3)设置提交方式
        switch($type){
            case "GET":
                curl_setopt($ch,CURLOPT_HTTPGET,true);
                break;
            case "POST":
                curl_setopt($ch,CURLOPT_POST,true);
                break;
            case "PUT"://使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请求。这对于执行"DELETE" 或者其他更隐蔽的HTT
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PUT");
                break;
            case "DELETE":
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"DELETE");
                break;
        }
        //4)在HTTP请求中包含一个"User-Agent: "头的字符串。-----必设
        //	curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
        //	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' );
        //5)模拟用户使用的浏览器
        //3.抓取URL并把它传递给浏览器
        $res=curl_exec($ch);
        $result=json_decode($res,true);
        //4.关闭curl资源，并且释放系统资源
        curl_close($ch);
        if(empty($result))
            return $res;
        else
            return $result;
    }
    //创建文件夹
    public function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
        if (!$this->mkdirs(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode);
    }
    //写入cursor
    public function writeCursor($filename,$content){
        //判断文件夹是否存在，不存在的话创建
        if(!file_exists("resource/txtfile")){
            $this->mkdirs("resource/txtfile");
        }
        $myfile=@fopen("resource/txtfile/".$filename,"w+") or die("Unable to open file!");
        @fwrite($myfile,$content);
        fclose($myfile);
    }
    //读取cursor
    public function readCursor($filename){
        //判断文件夹是否存在，不存在的话创建
        if(!file_exists("resource/txtfile")){
            $this->mkdirs("resource/txtfile");
        }
        $file="resource/txtfile/".$filename;
        $fp=fopen($file,"a+");//这里这设置成a+
        if($fp){
            while(!feof($fp)){
                //第二个参数为读取的长度
                $data=fread($fp,1000);
            }
            fclose($fp);
        }
        return $data;
    }
}