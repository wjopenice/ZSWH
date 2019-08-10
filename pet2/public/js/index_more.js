$(function(){
    //tab切换
    $(".game_tap a").click(function(){
        let index = $(this).index();
        $(this).addClass("active").siblings().removeClass("active");
        $(".game_content .game_ul").eq(index).addClass('active').siblings().removeClass('active');
        let gameval = $(this).data("gid");
        $("#gamecontent").empty();
        $.get("/Index/ajaxmore",{id:gameval},function (msg) {
            let len = msg.length;
            var strli = "";
            for(var i=0; i<len; i++){
                strli += "<li class='fl'>";
                strli += "<a href='/List/index?type=index&apkname="+msg[i].title+"&shoptype=all'>";
                strli += "<img src='http://www.zhishengwh.com/uploads/"+msg[i].icon+"' width='120' height='120' alt='"+msg[i].title+"'>";
                strli += "<p class='game_name'>"+msg[i].title+"</p>";
                strli += "</a>";
                strli += "</li>";
            }
            $(strli).appendTo($("#gamecontent"));
        },"json");
    });
});