<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <title><?php echo $OJ_NAME ?></title>
    <?php include("template/$OJ_TEMPLATE/css.php"); ?>
    <?php include("template/$OJ_TEMPLATE/js.php"); ?>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="http://cdn.bootcss.com/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<?php include("template/$OJ_TEMPLATE/nav.php");
include("csrf.php");
?>
<div class="container">
    <!-- Main component for a primary marketing message or call to action -->
    <div class="ui container padding">
        <div class="ui grid">
            <div class="row">
                <div class="ui column">
                    <div class='ui toggle checkbox refresh'>
                        <input type='checkbox'>
                        <label>停止自动刷新</label>
                    </div>
                    <div class='ui toggle checkbox sort'>
                        <input type='checkbox'>
                        <label>自动排序</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="ui grid">
            <div class="row">

                <div class="eleven wide column">

                    <table id='cs' class='ui padded celled selectable table' width=90%>
                        <thead>
                        <tr class=toprow align=center>
                            <th width=15% onclick="sortTable('cs', 0, 'float');">ID
                            <th width=20%>Name
                            <th width=55%>href
                        </tr>
                        </thead>
                        <tbody id="online_user_table" refresh="true">
                        <tr style="text-align:center" v-for="value in user" v-cloak>
                            <td>{{ value.user_id }}</td>
                            <td><a :href="'userinfo.php?user='+value.user_id" target="_blank">{{ value.nick }}</a></td>
                            <td><a :href="decodeURI(location.protocol+'//'+window.hostname+value.url)">{{ decodeURI(location.protocol+"//"+window.hostname+value.url) }}</a></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="five wide column">
                    <table id="name_list" class="ui padded celled selectable table">
                        <thead>
                        <tr class="toprow" align=center>
                            <th>user_id
                            <th>name
                            <th width=10% >Position
                        </thead>
                        <tbody id="user_list_table" refresh="true">
                        <tr style='text-align:center' v-for="value in user" v-cloak>
                            <td><a :href="'userinfo.php?user='+value.user_id">{{ value.user_id }}</a></td>
                            <td><a :href="'userinfo.php?user='+value.user_id">{{ value.nick||localStorage.getItem(value.user_id) }}</a></td>
                            <td width=30% :data-html="'<b>IP</b><p>内网IP:'+value.intranet_ip+'<br>外网IP:'+value.ip+'</p>'" >{{ value.place }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> <!-- /container -->
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->


<div style="display:none">
    <?php include("template/$OJ_TEMPLATE/bottom.php") ?>
</div>
</body>
<script>
function detect_ip(tmp){
    if(tmp.intranet_ip)
                                    {
                                    if(tmp.intranet_ip.match(/10\.10\.[0-9]{2}\.[0-9]{1,3}/))
                                    {
                                        tmp.place="润杰有线";
                                    }
                                    else if(tmp.intranet_ip.match(/10\.200\.28\.[0-9]{1,3}/)||tmp.intranet_ip.match(/10\.200\.26\.[0-9]{1,3}/)
                                        ||tmp.intranet_ip.match(/10\.200\.25\.[0-9]{1,3}/))
                                    {
                                        tmp.place="机房";
                                    }
                                    else if(tmp.intranet_ip.match(/10\.110\.[0-9]{1,3}\.[0-9]{1,3}/))
                                    {
                                        tmp.place="润杰公寓Wi-Fi";
                                    }
                                    else if(tmp.intranet_ip.match(/10\.102\.[0-9]{1,3}\.[0-9]{1,3}/))
                                    {
                                        tmp.place="第三教学楼Wi-Fi";
                                    }
                                    else if(tmp.intranet_ip.match(/10\.103\.[0-9]{1,3}\.[0-9]{1,3}/))
                                    {
                                        tmp.place="地质楼Wi-Fi";
                                    }
                                    else if(tmp.intranet_ip.match(/10\.1[0-9]{2}\.[0-9]{1,3}\.[0-9]{1,3}/))
                                    {
                                        tmp.place="其他Wi-Fi";
                                    }
                                    else if(tmp.intranet_ip.match(/172\.16\.[\s\S]+/))
                                    {
                                        tmp.place="VPN";
                                    }
                                    else if(tmp.intranet_ip && tmp.ip && tmp.intranet_ip != tmp.ip)
                                    {
                                        tmp.place="外网";
                                    }
                                    else if(tmp.intranet_ip.match(/2001:[\s\S]+/))
                                    {
                                        tmp.place = "IPv6";
                                    }
                                    else if(tmp.intranet_ip.match(/10\.3\.[\s\S]+/))
                                    {
                                        tmp.place="地质楼";
                                    }
                                    else
                                    {
                                        tmp.place="未知";
                                    }
                                    }
                                    else
                                        tmp.place="未知";
}
    var hostname = "<?=$_SERVER['HTTP_HOST']?>";
    var user_list = new Vue({
        el: "#user_list_table",
        data: {
            userlist: window.online_list
        },
        computed:
        {
            user:{
                get:function(){
                    if(!this.userlist)return [];
                    for (var i=0;i<this.userlist.length;++i){
                        var tmp=this.userlist[i];
                        detect_ip(tmp);
                    }
                    return this.userlist;
                },
                set:function(newval){
                    this.userlist=newval;
                }
            }
        }
    });
    var $online_list = new Vue({
            el: "#online_user_table",
            data: {
                userlist: window.online_list
            },
            computed:
                {
                    user: {
                        get: function () {
                            var newlist = [];
                            if(!this.userlist)return [];
                            for (var i = 0; i < this.userlist.length; ++i) {
                                var tat = this.userlist[i];
                                for (var j = 0; j < this.userlist[i].url.length; ++j) {
                                    var tmp = JSON.parse(JSON.stringify(tat));
                                    tmp.url = tmp.url[j];
                                    detect_ip(tmp);
                                    newlist.push(tmp);
                                }
                            }
                            if(localStorage.getItem("sort")=="true")
                            {
                            newlist.sort(function (a, b) {
                                var a1 = a["user_id"];
                                var b1 = b["user_id"];
                                if (!isNaN(parseInt(a1)) && !isNaN(parseInt(b1))) {
                                    return parseInt(a1) - parseInt(b1);
                                }
                                else {
                                    return isNaN(parseInt(b1)) ? 1 : -1;
                                }
                            });
                            }
                            return newlist;
                        },
                        set: function (newval) {
                            this.userlist = newval;
                        }
                    }
                },
                updated:function(){
                    $("td").popup({
                        on:'hover',
                        positon:"top center"
                    })
                }
        }
    );
    if (!localStorage.getItem("refresh")) {
        localStorage.setItem("refresh", "true");
    }
    if (!localStorage.getItem("sort")) {
        localStorage.setItem("sort", "true");
    }
    var stat = localStorage.getItem("refresh");
    if (stat == "true") {
        $(".toggle.checkbox.refresh").first().checkbox("uncheck");
    }
    else {
        $(".toggle.checkbox.refresh").first().checkbox("check");
    }
    $("#online_user_table").attr("refresh", stat);
    stat = localStorage.getItem("sort");
    if (stat == "true") {
        $(".toggle.checkbox.sort").first().checkbox("check");
    }
    else {
        $(".toggle.checkbox.sort").first().checkbox("uncheck");
    }
    $('.toggle.checkbox.refresh')
        .checkbox()
        .first().checkbox({
        onChecked: function () {
            $("#online_user_table").attr("refresh", "false");
            localStorage.setItem("refresh", "false");
        },
        onUnchecked: function () {
            $("#online_user_table").attr("refresh", "true");
            localStorage.setItem("refresh", "true");
        }
    });
    $('.toggle.checkbox.sort')
        .checkbox()
        .first().checkbox({
        onChecked: function () {
            localStorage.setItem("sort", "true");
            list_online(window.online_list);
        },
        onUnchecked: function () {
            localStorage.setItem("sort", "false");
        }
    })
    $('.toggle.checkbox.sort').popup({
        title: "自动排序",
        content: "根据学号升序排序(开启对性能要求较高，建议开启排序后关闭)"
    })

    function list_online() {
       user_list.user = window.online_list;
       $online_list.user = window.online_list;
    }

</script>
<script src="include/sortTable.js"></script>
</html>
