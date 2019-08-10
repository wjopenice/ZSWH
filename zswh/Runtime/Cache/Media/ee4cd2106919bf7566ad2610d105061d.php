<?php if (!defined('THINK_PATH')) exit();?><!--公共头部-->
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo C('PC_SET_TITLE');?></title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="<?php echo C('WEB_SITE_KEYWORD');?>">
    <meta name="description" content="<?php echo C('WEB_SITE_DESCRIPTION');?>">
    <meta name="viewport" content="width=device-width,target-densitydpi=device-dpi,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"  >
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="/Public/Media/css/base.css">
    <link rel="stylesheet" href="/Public/Media/css/index.css">
    <link rel="stylesheet" href="/Public/Media/css/bannercss/slider.css">
    <link rel="icon" href="/Public/Media/image/favicon.ico"/>
    <script src="/Public/Media/js/jquery-1.11.1.min.js"></script>
    <!--<script>
        var _hmt = _hmt || [];
        (function() {
          var hm = document.createElement("script");
          hm.src = "https://hm.baidu.com/hm.js?bc19aa51515f215def6b091f540c83ea";
          var s = document.getElementsByTagName("script")[0];
          s.parentNode.insertBefore(hm, s);
        })();
    </script>-->
    <!-- 如果是个人中心则显示个人中心的导航 -->
</head>
<body>
<!--头部-->
<div class="top " id="topPart">
    <div class="container">
        <div class="top-l">
            <a href="<?php echo U('Game/youxi');?>">游戏中心 ｜</a>
            <a href="<?php echo U('Gift/index');?>"> 礼包中心 ｜</a>
            <a target="_blank" href="http://open.zhishengwh.com/forum.php"> 游戏论坛 ｜</a>
            <a target="_blank" href="/index.php?s=/Home/Index/index.html"> 渠道平台 </a>
            <!--<a href="<?php echo U('Index/appdown');?>">玩转APP</a>-->
        </div>
        <div class="top-r">
            <a href="<?php echo U('Public/login');?>" id="entry">登录 |</a>
            <a href="<?php echo U('Public/register');?>" id="enroll">注册</a>
        </div>
    </div>
</div>
<!--导航-->

<div class="header clearfix" id="navBar">
    <div class="center">
        <h1 class="header-l">
             <a href="<?php echo U('Index/index');?>" class="logo"><img src="/Public/Media/image/header/logo.png" alt="玩转平台"></a>
        </h1>
        <div class="header-c">
            <ul class="list">
                <li><span class="menu-1" ></span><a href="<?php echo U('Index/index');?>" >首页</a> </li>
                <li><span class="menu-2"></span><a href="<?php echo U('Game/youxi');?>">游戏中心</a></li>
                <li><span class="menu-3"></span><a href="<?php echo U('Gift/index');?>">礼包中心</a></li>
                <li><span class="menu-7"></span><a href="<?php echo U('Exchange/index');?>">兑换中心</a></li>
                <li><span class="menu-4"></span><a href="<?php echo U('Category/zixun');?>">资讯中心</a></li>
                <li><span class="menu-5"></span><a href="<?php echo U('Recharge/chongzhi');?>">充值中心</a></li>
                <li><span class="menu-6"></span><a href="<?php echo U('Service/kefu');?>">客服中心</a></li>
                <li><span class="menu-8"></span><a target="_blank" href="/media.php?s=/Index/about">关于我们</a></li>
            </ul>
        </div>
        <div class="header-r">
            <input  class="sousuo" type="text" placeholder="请输入关键词" id="com_input" />
            <span class="icon-search" id="com_search"></span>
        </div>
    </div>
</div>
<?php if(CONTROLLER_NAME == Member): ?><!--banner 区域-->
<div class="pc_banner">
    <div class="container">
      <!--   <p>当前位置：个人中心</p> -->
    </div>
</div>

<!--个人中心的内容部分-->

<div class="pc_main">
        <div id="pc_main_title_bg">
        <div id="pc_main_title">
            <ul class="container" >
                <li><a href="<?php echo U('Member/personalcenter');?>"><img src="/Public/Media/image/personalcenter/baseinfo/jibenxinxi_1.png"/></a></li>
                <li><a href="<?php echo U('Member/pcsafecenter');?>"><img src="/Public/Media/image/personalcenter/baseinfo/anquanzhongxin_2.png"/></a></li>
                <li><a href="<?php echo U('Member/pcmessage');?>"><img src="/Public/Media/image/personalcenter/baseinfo/xinxizhongxin_2.png"/></a></li>
                <li><a href="<?php echo U('Member/pctrade');?>"><img src="/Public/Media/image/personalcenter/baseinfo/jiaoyijilv_1.png"/></a></li>
                <li class="last"><a href="<?php echo U('Member/pcaccountyue');?>"><img src="/Public/Media/image/personalcenter/baseinfo/zhanghuyue_1.png"/></a></li>
            </ul>
        </div>
    </div>
    <?php else: endif; ?>

    <script>

    var ACTION_STR = '/index.php?s=/Media/Game/yxchildren';
    var MODULE = "/index.php?s=/Media";
    var letter_number = "<?php echo ($letter_number); ?>";

    var navItem =  ACTION_STR.substring(ACTION_STR.lastIndexOf('/')+1);
    if(navItem == 'yxchildren'){
        navItem ='youxi';
    }
    var pcImgArr=[
            ["/Public/Media/image/personalcenter/baseinfo/jibenxinxi_1.png","/Public/Media/image/personalcenter/baseinfo/jibenxinxi_2.png"],
            ["/Public/Media/image/personalcenter/baseinfo/anquanzhongxin_1.png","/Public/Media/image/personalcenter/baseinfo/anquanzhongxin_2.png"],
            ["/Public/Media/image/personalcenter/baseinfo/xinxizhongxi_1.png","/Public/Media/image/personalcenter/baseinfo/xinxizhongxin_2.png"],
            ["/Public/Media/image/personalcenter/baseinfo/jiaoyijilv_2.png","/Public/Media/image/personalcenter/baseinfo/jiaoyijilv_1.png"],
            ["/Public/Media/image/personalcenter/baseinfo/zhanghuyue_2.png","/Public/Media/image/personalcenter/baseinfo/zhanghuyue_1.png"]
    ];
    pcUrlArr=[
            ["personalcenter"],
            ["pcsafecenter","bindphone","bindemail","noidcard","nobindphone","nobindemail","idcard","bindemailfinish"],
            ["pcmessage","pcmsgunread","pcmsghasread","detail"],
            ["pctrade","pctradexf"],
            ["pcaccountyue"]
    ];
    for(var i=0;i<pcUrlArr.length;i++){
        $("#pc_main_title").find("li").eq(i).find("img").attr("src",pcImgArr[i][1]);

        if($.inArray(navItem, pcUrlArr[i])!=-1){
            $("#pc_main_title").find("li").eq(i).find("img").attr("src",pcImgArr[i][0]);
        }
    }

</script>

<script src="/Public/Media/js/pop.js"></script>
<script src="/Public/Media/js/js.js"></script>


 <link rel="stylesheet" href="/Public/Media/css/yxchildren.css">
<!--游戏详情-->
<div class="detail container clearfix">
    <div class="detail-l">
        <div class="detail-t">
            <p>当前位置:<a href="<?php echo U('Game/youxi');?>"> 游戏中心</a>><?php echo ($vo["game_name"]); ?></p>
        </div>
        <div class="detail-c clearfix">
            <img src="<?php echo (get_cover($vo["icon"],'path')); ?>" alt="" class="tp">
            <div class="wenzi">
                <p class="wenzi-t"><?php echo ($vo["game_name"]); ?></p>
                <p class="stitle">类型：<?php echo ($vo["game_type_name"]); ?></p>
                <p class="stitle">大小：<?php echo ($vo["game_size"]); ?></p>
                <p class="stitle">版本：<?php echo ($vo["version"]); ?></p>
                <p class="stitle">时间：<?php echo (date("Y-m-d",$vo["create_time"])); ?></p>
                <a href="<?php if($vo["dow_status"] == 0): ?>#<?php else: echo U('Down/down_file?game_id='.$vo['id'].'&type=1'); endif; ?>" class="load"><img src="/Public/Media/image/youxi/anzhuoxiazai.png" alt="" class="xz"></a>
             </div>
             <div class='imgEvm'>
                <img src="<?php echo U('Game/dow_url_generate?game_id='.$vo['id']);?>" alt="">
                 <p class='txt'>扫码下载</p>
            </div>
        </div>
        <div class="detail-b">
            <div class="detail-b-t">
                <p class="one">游戏详情</p>
                <p class="two"><?php echo ($vo["introduction"]); ?></p>
            </div>
            <div class="detail-b-b" id="box1">
                <div class="hd">
                    <span class="current">游戏图片</span>
                    <span>游戏礼包</span>
                </div>
                <div class="bd">
                    <ul>
                        <li class="show">
                            <!--游戏部分轮播     -->
                            <div id="slider">
                                <?php $_result=explode(',',$vo['screenshot']);if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$pid): $mod = ($i % 2 );++$i; if(!empty($pid)): ?><div class="spic" >
                                            <img src="<?php echo (get_cover($pid,'path')); ?>" />
                                        </div><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                            </div>
                        </li>
                        <li id='gifted'>
                            <?php if(empty($giftdetail)): ?><img src="/Public/Media/image/youxi/ku.png" alt="" class="ku">
                            <?php else: ?>
                                <?php if(is_array($giftdetail)): $i = 0; $__LIST__ = $giftdetail;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="lb">
                                        <div class="lb-t">
                                            <div class="lb-t-t">
                                                <p class="one" id="<?php echo ($vo["gift_id"]); ?>"><?php echo ($vo["giftbag_name"]); ?>-<?php echo ($vo["game_name"]); ?></p>
                                                <p class="two"><?php echo ($vo["desribe"]); ?></p>
                                            </div>
                                        </div>
                                        <div class="lb-b">
                                            <a href="javascript:;" class="lb-b-l"><?php echo (date("Y-m-d",$vo["start_time"])); ?>至<?php echo (date("Y-m-d",$vo["end_time"])); ?></a>
                                            <a href="javascript:;" class="lb-b-r">领&nbsp;取</a>
                                        </div>
                                    </div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
  <div class="detail-r">
      <!--游戏排行-->
      <div class=" paihang clearfix">
          <div class="paihang-t">
              <a href="javascript:;"  class="one"><span class="icon-libao"></span>游戏排行</a>
          </div>
         <div class="paihang-b ">
                    <ul class="weekly-list">
                    <?php if(is_array($rank)): $i = 0; $__LIST__ = $rank;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li >
                                <div class="app-show-title clearfix">
                                    <?php if($num < 4): ?><span class="num s-index-org" style="background-color:#f19ec2;color: #fff;"><?php echo ($num++); ?></span>
                                        <?php elseif($num < 7): ?><span class="num s-index-org" style="background-color:pink;color: #fff;"><?php echo ($num++); ?></span>
                                        <?php else: ?> <span class="num s-index-org" style=";background-color:gray;color: #fff;"><?php echo ($num++); ?></span><?php endif; ?>                                    
                                    <a href="<?php echo U('Game/yxchildren',array('id'=>$vo['id'],'type'=>1));?>"  class="bl"><?php echo ($vo["game_name"]); ?></a>
                                    <a href="javascript:;" class="hh"><?php echo ($vo["game_type_name"]); ?></a>
                                </div>

                                <div class="app-show-block"> 
                                <?php if($num < 5): ?><span class="num s-index-org" style="background-color:#f19ec2;color: #fff;"></span>
                                        <?php elseif($num < 8): ?><span class="num s-index-org" style="background-color:pink;color: #fff;"></span>
                                        <?php else: ?> <span class="num s-index-org" style=";background-color:gray;color: #fff;"></span><?php endif; ?>   

                                <a href="<?php echo U('Game/yxchildren',array('id'=>$vo['id'],'type'=>1));?>" class="pic"><img src="<?php echo (get_cover($vo["icon"],'path')); ?>" alt="<?php echo ($vo["game_name"]); ?>"></a>
                                <div class='xzandli clearfix'>      
                                    <div class='title clearfix'>
                                    <a href="<?php echo U('Game/yxchildren',array('id'=>$vo['id'],'type'=>1));?>"  class="name"><?php echo ($vo["game_name"]); ?></a>
                                    <a href="javascript:;" class="jiaose"><?php echo ($vo["game_type_name"]); ?></a>
                                    </div>
                                    <div class='picandtxt'>   
                                    <a  class="xz" href="<?php if($vo["dow_status"] == 0): ?>#<?php else: echo U('Down/down_file?game_id='.$vo['id'].'&type=1'); endif; ?>" class="down">下&nbsp;载</a>                                  
                                    <a href="<?php echo U('Game/yxchildren',array('id'=>$vo['id'],'type'=>2));?>" class="lb">礼&nbsp;包</a>
                                    </div>
                            </li><?php endforeach; endif; else: echo "" ;endif; ?> 
                        
                        </ul>
                </div>
      </div>
  </div>
</div>

</div>
<script>
    var box1=document.getElementById('box1');
    var spans=box1.getElementsByTagName('span');
    var lis=box1.getElementsByTagName('li');
    for(var i=0;i<spans.length;i++){
        spans[i].index=i;
        spans[i].onmouseover=function(){
            for(var j=0; j<spans.length;j++){
                spans[j].className="";
                lis[j].className="";
            }
            this.className="current";
            lis[this.index].className="show";
        }
    }

</script>
<script src="/Public/Media/js/jquery.min.js"></script>
<script src="/Public/Media/js/slider.js" type="text/javascript"></script>
<script type="text/javascript">
    var MODULE = "/index.php?s=/Media";
</script>
<script src="/Public/Media/js/pop.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#slider').slider({ speed: 500 });
    });
</script>
<script>
   // 排行榜

$(function(){
     var $gameOrder= $(".weekly-list");
            $gameOrder.find("li").eq(0).addClass('current');
            $gameOrder.find("li").each(function(){
                var num=$(this).find('.app-show-title').find('span').html();
                $(this).find('.app-show-block').find('span').html(num);
            });
            $gameOrder.find("li").mouseover(function(){
                $(this).addClass('current').siblings('li').removeClass('current');
            })
})
</script>
<script>
    $('#gifted').find('.lb-b').each(function () {
        $(this).find('.lb-b-r').click(function () {
            $.ajax({
                type:'post',
                dataType:'json',
                data:{
                    gift:$(this).parent(".lb-b").parent(".lb").find('.lb-t-t').find('.one').html(),
                    giftid:$(this).parent(".lb-b").parent(".lb").find('.lb-t-t').find('.one').attr('id')
                },
                url:"<?php echo U('Member/getGameGift');?>",
                success:function(data) {
                    if (parseInt(data.status) == 1 ) {
//                    登录成功时候的状态
                        if(data.msg=='ok'){
                            login_ok(data);
                        }
                        if(data.msg=='no'){
//                        您已领取过该礼包
                            login_no(data)
                        }
                        if(data.msg=='noc'){
//                        该礼包已领取完，下次请早
                            login_noc(data)
                        }
                    }
                    if (parseInt(data.status) == 0 ) {
                        //登录失败的时候
                        nologin_box();
                    }
                },
                error:function() {
                    alert('服务器故障，请稍候再试。。。。');
                }
            });
        })
    });

    $(function(){
        var type ='<?php echo ((isset($_GET['type']) && ($_GET['type'] !== ""))?($_GET['type']):1); ?>';       
        if(type==1){
            $(".hd").find("span").removeClass("current");
            $(".hd").find("span").eq(0).addClass("current");
            $(".bd").find("ul").find("li").removeClass("show");
            $(".bd").find("ul").find("li").eq(0).addClass("show");
        }
        if(type==2){
            $(".hd").find("span").removeClass("current");
            $(".hd").find("span").eq(1).addClass("current");
            $(".bd").find("ul").find("li").removeClass("show");
            $(".bd").find("ul").find("li").eq(1).addClass("show");
        }
    })


</script>
</body>
</html>


<!--小火箭-->
<div style="display: none;" id="rocket-to-top">
    <div style="opacity:0;display: block;" class="level-2"></div>
    <div class="level-3"></div>
</div>
<!--尾巴部分-->
<div class="footer clearfix">
    <div class="banxin">
        <div class="footer-t">
            <p>
                <a href="http://www.zhishengwh.com/media.php?s=/Category/zxchildren/id/35" target="_blank">关于我们 ｜  </a>
                <a href="http://www.zhishengwh.com/media.php?s=/Category/zxchildren/id/36" target="_blank">商务合作 ｜  </a>
                <a href="http://www.zhishengwh.com/media.php?s=/Category/zxchildren/id/37" target="_blank">合作伙伴 ｜  </a>
                <a href="http://www.zhishengwh.com/media.php?s=/Service/kefu" target="_blank">客服帮助</a>
            </p>
            <p>
                <a href="http://www.zhishengwh.com/media.php?s=/Index/regareement" target="_blank">注册协议｜ </a>
                <a href="http://www.zhishengwh.com/media.php?s=/Public/register" target="_blank">防沉迷系统 ｜ </a>
                <a href="http://www.zhishengwh.com/media.php?s=/Category/zxchildren/id/38" target="_blank">未成年家长监护工程 ｜ </a>
                <a href="http://www.zhishengwh.com/media.php?s=/Index/interareement" target="_blank">《网络游戏服务格式化协议必备条款》 </a>
            </p>
            <p>
                <a href="javascript:;">
                    深圳致晟文化创新有限公司 版权所有2018 zhishengwh.com
                </a>
            </p>
            <p><a href="http://www.miitbeian.gov.cn">网站备案：粤ICP备18083935号-1</a></p>
        </div>

        <!--<div class="footer-b clearfix">
            <a class="one" href="javascript:;"><img src="/Public/Media/image/footer/logobt.png" alt=""></a>
            <a class="two" href="javascript:;"><img src="/Public/Media/image/gzh.jpg" alt=""></a>
            <a class="wenzi" href="javascript:;">玩转平台</a>
        </div>-->
    </div>
</div>

<script>
    $(function(){
        function placeholder(input,text){
            input.value=text;
            input.onfocus=function(){
                if(this.value==text){
                    this.value='';
                }
            };
            input.onblur=function(){
                if(this.value==''){
                    this.value=text;
                }
            };
        };
        var ocom_input=document.getElementById("com_input");
        var $input=$("#com_input");
        var $search=$("#com_search");

        $search.click(function(){
            var txt= $.trim($input.val());
            if(txt == ''){
                window.location.href="<?php echo U('Game/yx_fenlei');?>";
            }else{
                // window.location.href="yxfenli.html?keyword="+txt;
                window.location.href="/index.php?s=/Media/"+"Game/yx_fenlei/keyword/"+txt;
            }
            /*$.ajax({
             type: 'POST',
             async: true,
             dataType: 'json',
             url: "<?php echo U('search_keyword');?>",//提交给后台的地址
             data: {
             keyword:txt
             },
             success: function (data) {

             },
             error: function () {
             alert('服务器故障，稍后再试');


             }
             });*/
        });
        $("#com_input").keydown(function (e) {
            if (e.which == 13) {
                $('#com_search').trigger("click");//触发搜索按钮的点击事件
            }
        });

    })
</script>
<script type="text/javascript">


    var CON = "<?php echo (CONTROLLER_NAME); ?>";
    var navArr=["Index","Game","Gift","Exchange","Category","Recharge","Service"];
    var navImg=[
        [
            "url(/Public/Media/image/header/navbar.png) no-repeat -124px -16px",
            "url(/Public/Media/image/header/navbar.png) no-repeat -124px -45px"
        ],
        [
            "url(/Public/Media/image/header/navbar.png) no-repeat -12px  -17px",
            "url(/Public/Media/image/header/navbar.png) no-repeat -12px -45px"
        ],
        [
            "url(/Public/Media/image/header/navbar.png) no-repeat -161px -15px",
            "url(/Public/Media/image/header/navbar.png) no-repeat -161px -43px"
        ],
        [
            "url(/Public/Media/image/header/navbar.png) no-repeat -261px  -15px",
            "url(/Public/Media/image/header/navbar.png) no-repeat -261px -45px"
        ],
        [
            "url(/Public/Media/image/header/navbar.png) no-repeat -53px  -14px",
            "url(/Public/Media/image/header/navbar.png) no-repeat -53px -45px"
        ],
        [
            "url(/Public/Media/image/header/navbar.png) no-repeat -198px -17px",
            "url(/Public/Media/image/header/navbar.png) no-repeat -198px -46px"
        ],
        [
            "url(/Public/Media/image/header/navbar.png) no-repeat -94px  -15px",
            "url(/Public/Media/image/header/navbar.png) no-repeat -94px -45px"
        ]

    ];

    for(var i=0;i<navArr.length;i++){
        $(".header-c").find("ul").find("li").css("color","#666");
        $(".header-c").find("li").eq(i).find("span").css("background",navImg[i][0]);

        if(CON == $.trim(navArr[i])){
            $(".header-c").find("li").eq(i).find("a").css("color","#ED6557");
            $(".header-c").find("li").eq(i).find("span").css("background",navImg[i][1]);
        }
    }
    $(".header-c").find("ul").find("li").each(function(){
        var index=$(this).index();
        $(this).hover(function(){
            $(this).find("span").css("background",'url('+navImg[index][1]+')');

        },function(){
             if(CON == $.trim(navArr[index])){
            $(".header-c").find("li").eq(index).find("a").css("color","#ED6557");
            $(this).find("span").css("background",'url('+navImg[index][1]+')');
        }else{
            $(this).find("span").css("background",'url('+navImg[index][0]+')');
        }
        })
    });
</script>

<script>
    $(function(){
        $(window).scroll(function () {
    //1 页面滚动时获取卷曲高度
            var h = $(this).scrollTop();
    //获取头部的高度
            var tophead = $("#topPart").height();
            var navHeight = $("#navBar").height();
            if (h > tophead) {
    //让导航部定位
                $("#navBar").addClass("fixed");
            } else {
                $("#navBar").removeClass("fixed");
            }
        })

    });
</script>