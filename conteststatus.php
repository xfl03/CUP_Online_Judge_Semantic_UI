<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <title><?php echo $OJ_NAME ?> -- Status</title>
    <?php include("template/semantic-ui/css.php"); ?>
    <?php include("template/semantic-ui/js.php"); ?>
    <script src="/template/semantic-ui/js/Chart.bundle.min.js"></script>
</head>

<body>
<script type="text/x-template" id="status_table">
    <table class="ui padded selectable unstackable table" align="center" width="90%">
        <thead v-cloak>
        <tr class='toprow'>
            <th>{{target.solution_id}}</th>
            <th width="15%">{{target.user}}</th>
            <th>{{target.problem_id}}</th>
            <th width="13%">{{target.result}}</th>
            <th width="15%">{{target.memory+"/"+target.time}}</th>
            <th>{{target.language+"/"+target.length}}</th>
            <th>{{target.submit_time}}</th>
            <th>{{target.judger}}</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="row in problem_lists" :class="row.sim?'warning':''">
            <td>{{row.solution_id}}</td>
            <td><a :href="'userinfo.php?user='+row.user_id">{{row.user_id}}<br>{{row.nick}}</a>
            </td>
            <td>
                <div class=center><a :href="(row.oj_name === 'local'?'new':row.oj_name.toLowerCase())+'submitpage.php?cid='+row.contest_id+'&pid='+row.num">{{end?((row.oj_name === "local"?"":row.oj_name.toUpperCase())+row.problem_id):(problem_alpha[row.num])}}</a>
                </div>
            </td>
            <td><a :href="(row.result == 11?'ce':'re')+'info.php?sid='+row.solution_id"
                   v-cloak :class="answer_class[row.result]" title='点击看详细'><i v-cloak :class="answer_icon[row.result]+' icon'"></i>{{result[row.result]}}</a>
                   <a v-if="row.sim" :href="'comparesource.php?left='+row.solution_id+'&right='+row.sim_id" v-cloak :class="answer_class[row.result]"><br>
                   {{(Boolean(row.sim) === false?'':row.sim_id+' ('+row.sim+'%)')}}
                   </a>
                   <a :class="answer_class[row.result]" v-if="row.result !== 4 && row.pass_rate > 0.05"><i :class="answer_icon[row.result]+' icon'" style="opacity:0"></i>Passed:{{(row.pass_rate*100).toString().substring(0,3)}}%</a>

            </td>
            <td>
                <div id=center>{{memory_parse(row.memory)}}<br>{{time_parse(row.time)}}</div>
            </td>
            <td><a v-if="self === row.user_id || isadmin" target=_blank :href="'showsource.php?'+(row.oj_name === 'local'?'':'h')+'id='+row.solution_id">{{language_name[row.oj_name.toLowerCase()][row.language]}}</a>
                <span v-else>{{language_name[row.oj_name.toLowerCase()][row.language]}}</span>
                <span v-if="(self === row.user_id || isadmin) && row.problem_id"> / </span>
                <a v-if="(self === row.user_id || isadmin) && row.problem_id" target="_blank"
                   :href="(row.oj_name === 'local'?'new':row.oj_name.toLowerCase())+'submitpage.php?cid='+row.contest_id+'&pid='+row.num+'&sid='+row.solution_id">Edit</a>
                <br>
                {{row.length}}B
            </td>
            <td class='need_to_be_rendered' :datetime="row.in_date">{{new Date(row.in_date).toLocaleString()}}</td>
            <td>{{row.judger}}</td>
        </tr>
        </tbody>
    </table>
</script>
<?php include("template/$OJ_TEMPLATE/nav.php"); ?>
<div>
    <!-- Main component for a primary marketing message or call to action -->
    <div class="padding ui container">
        <h2 class="ui dividing header">
            Status
        </h2>
        <div class="ui top attached tabular menu">
            <a v-cloak :class="(current_tag == 'status'?'active':'')+' item'" @click="tag('status',$event)" id="submitstatus">提交状态</a>
            <a v-cloak :class="(current_tag == 'graph'?'active':'')+' item'" @click="tag('graph',$event)" id="graph">提交图表</a>
        </div>
        <div class="ui bottom attached segment" v-show="current_tag == 'status'">
            <div align=center class="input-append">
                <form id=simform class="ui form segment" action="status.php" method="get">
                    <div class="four fields">
                        <div class="field">
                            <label> <?php echo $MSG_PROBLEM_ID ?></label>
                            <input v-model="problem_id" class="form-control" type=text size=4 name=problem_id>
                        </div>
                        <div class="field">
                            <label><?php echo $MSG_USER ?></label>
                            <input v-model="user_id" class="form-control" type=text size=4 name=user_id
                                   value=''>
                        </div>
                        <div class="field">
                            <label><?php echo $MSG_LANG ?></label>
                            <select v-model="language" class="ui search dropdown" size="1" name="language"
                                    data-toggle='select'>
                                <option value='-1' selected>All</option>
                                <?php
                                $i = 0;
                                foreach ($language_name as $lang) {
                                    echo "<option value=$i>$language_name[$i]</option>";
                                    $i++;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="field">
                            <label><?php echo $MSG_RESULT ?></label>
                            <?php
                            $jresult_get = -1;
                            ?>
                            <div class="ui fluid search dropdown selection" size="1">
                                <input v-model="problem_result" @change="problem_result=$event.target.value"
                                       type="hidden" name="jresult">
                                <i class="dropdown icon"></i>
                                <div class="default text">All</div>
                                <div class="menu">
                                    <div class='item' data-value='-1'>All<i class="dropdown icon"
                                                                            style="visibility: hidden; "></i></div>
                                    <?php
                                    for ($j = 0, $i = ($j + 4) % 12; $j < 12; $j++, $i = ($j + 4) % 12) {
                                        ?>
                                        <div class='item' data-value='<?= strval($i) ?>'><span
                                                class='<?= $judge_flag[$i] ?>'><i
                                                    class='<?= $jicon[$i] ?> icon'></i><?= $jresult[$i] ?></span></div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="center aligned">
                        <button class="ui labeled icon mini button" @click.prevent="search($event)"><i
                                class="search icon"></i><?= $MSG_SEARCH ?></button>
                    </div>
                </form>
            </div>
            <br>
            <div class="row">
                <status-table :target="target" :problem_list="problem_list"
                              :answer_class="judge_color" :answer_icon="icon_list" :language_name="language_name"
                              :self="self"
                              :result="result"
                              :end="end"
                              :isadmin="isadmin"></status-table>
                <div class="ui active inverted dimmer" v-if="dim"><div class="ui large text loader">Loading</div></div>
            </div>
            <br>
            <div class="row">
                <a v-cloak :class="'ui button '+(page_cnt == 0?'disabled':'')" @click="page_cnt != 0 && page(-page_cnt,$event)">Top</a>
                <div class="ui buttons">
                    <button v-cloak :class="'ui left labeled icon button '+(page_cnt == 0?'disabled':'')"
                            @click="page_cnt!=0&&page(-1,$event)">
                        <i class="left arrow icon"></i>
                        Prev
                    </button>
                    <div class="or" v-cloak></div>
                    <button v-cloak :class="'ui right labeled icon button '+(problem_list.length == 0?'disabled':'')"
                            @click="problem_list.length != 0 && page(1,$event)">
                        <i class="right arrow icon"></i>
                        Next
                    </button>
                </div>
            </div>
        </div>
        <div class="ui attached bottom segment" v-show="current_tag == 'graph'">
            <div style="width:75%;margin:auto">
                <canvas id="canvas"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->

<script>
    function draw(_result) {
        var result = _result.result;
        var _label = _result.label;
        var row = {};
        var _labels = [];
        var _submits = [];
        var _accepteds = [];
        var _persent = [];
        for(let i of result){
            if(i[_label[0]]&&i[_label[1]]){
                _labels.push(i[_label[0]]+"-"+i[_label[1]]);
                _submits.push(i.submit||0);
                _accepteds.push(i.accepted||0);
                _persent.push((i.accepted/i.submit*100).toString().substring(0,5));
            }
        };
        var config = {
            type: 'line',
            data: {
                labels: _labels,
                datasets: [{
                    label: "总提交",
                    yAxisID: "submit",
                    backgroundColor: window.chartColors.red,
                    borderColor: window.chartColors.red,
                    data: _submits,
                    fill: false,
                }, {
                    label: "正确",
                    yAxisID: "submit",
                    fill: false,
                    backgroundColor: window.chartColors.blue,
                    borderColor: window.chartColors.blue,
                    data: _accepteds,
                }, {
                    label: "正确率",
                    fill: false,
                    yAxisID: "per",
                    backgroundColor: window.chartColors.green,
                    borderColor: window.chartColors.green,
                    data: _persent,
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: '统计信息'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: _label[0]+" - "+_label[1]
                        }
                    }],
                    yAxes: [{
                        id: "submit",
                        position: "left",
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: '提交'
                        }
                    }, {
                        id: 'per',
                        type: 'linear',
                        position: 'right',
                        scaleLabel: {
                            display: true,
                            labelString: '正确率'
                        },
                        ticks: {
                            max: 100,
                            min: 0
                        }
                    }]
                }
            }
        };
        var ctx = document.getElementById("canvas").getContext("2d");
        window.myLine = new Chart(ctx, config);
    }

</script>
<script>
    Vue.component("status-table", {
        template: "#status_table",
        props: {
            problem_list: Array,
            answer_icon: Array,
            answer_class: Array,
            target: Object,
            language_name: Array,
            result: Array,
            self: String,
            isadmin: Boolean,
            end:Boolean
        },
        data: function () {
            return {
                problem_alpha:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"
            };
        },
        methods: {
            memory_parse: function (_memory) {
                var unit = ["KB", "MB", "GB"];
                var cnt = 0;
                var memory = parseInt(_memory);
                if(isNaN(memory)){
                    return _memory;
                }
                while (memory > 1024) {
                    memory /= 1024;
                    ++cnt;
                }
                return memory.toString().substring(0, 5) + unit[cnt];
            },
            time_parse: function (_time) {
                var unit = ["ms", "s"];
                var cnt = 0;
                var time = parseInt(_time);
                if(isNaN(time)){
                    return _time;
                }
                while (time > 1000) {
                    ++cnt;
                    time /= 1000;
                }
                return time.toString().substring(0, 5) + unit[cnt];
            }
        },
        computed: {
            problem_lists: function () {
                return this.problem_list;
            }
        }
    })
    var problemStatus = new Vue({
        el: ".ui.container.padding",
        data: {
            problem_list: [],
            icon_list: [],
            judge_color: [],
            target: {},
            language_name: [],
            result: [],
            self: "",
            isadmin: false,
            problem_id: null,
            user_id: null,
            language: -1,
            problem_result: -1,
            page_cnt: 0,
            current_tag : "status",
            dim:false,
            end:false,
            cid:getParameterByName("cid")
        },
        computed: {},
        methods: {
            getUserId:function(){
                return getParameterByName("user_id")
            },
            getResult:function(){
                return getParameterByName("jresult")
            },
            getProblemID:function(){
                return getParameterByName("problem_id")
            },
            getLanguage:function(){
                return getParameterByName("language")
            },
            setQuery:function(){
                var queryobject = {
                    cid:this.cid
                };
                if(this.user_id && this.user_id.length > 0)
                    queryobject["user_id"] = this.user_id;
                if(this.problem_result && this.problem_result!== -1)
                    queryobject["jresult"] = this.problem_result;
                if(this.problem_id && this.problem_id !== 0)
                    queryobject["problem_id"] = this.problem_id;
                if(this.language && this.language !== -1)
                    queryobject["language"] = this.language;
                var url = location.origin+location.pathname+"?"+$.param(queryobject);
                if(url !== location.origin+location.pathname+"?")
                    history.pushState({},0,url);
            },
            search_func: function (data) {
                var that = this;
                this.setQuery();
                that.problem_list = data.result;
                that.icon_list = data.const_list.icon_list;
                that.judge_color = data.const_list.judge_color;
                that.target = data.const_list.language.cn.status;
                that.language_name = data.const_list.language_name;
                that.result = data.const_list.result.cn;
                that.self = data.self;
                that.end = data.end;
                that.isadmin = data.isadmin
            },
            search: function ($event) {
                this.dim = true;
                this.page_cnt = 0;
                var problem_id = this.problem_id || "null";
                var user_id = this.user_id || "null";
                var language = this.language == -1 ? "null" : this.language;
                var result = this.problem_result == -1 ? "null" : this.problem_result;
                var page_cnt = this.page_cnt * 20;
                var cid = this.cid||"";
                var that = this;
                $.get("../api/status/" + problem_id + "/" + user_id + "/" + language + "/" + result + "/" + page_cnt+"/"+cid, function (data) {
                    that.dim = false;
                    that.search_func(data);
                })
            },
            page: function (num, $event) {
                this.dim = true;
                this.page_cnt += num;
                var problem_id = this.problem_id || "null";
                var user_id = this.user_id || "null";
                var language = this.language == -1 ? "null" : this.language;
                var result = this.problem_result == -1 ? "null" : this.problem_result;
                var page_cnt = this.page_cnt * 20;
                var cid = this.cid||"";
                var that = this;
                $.get("../api/status/" + problem_id + "/" + user_id + "/" + language + "/" + result + "/" + page_cnt+"/"+cid, function (data) {
                    that.dim = false;
                    that.search_func(data);
                })
            },
            submit: function (data) {
                var obj = {};
                obj.problem_id = Math.abs(data.val.id);
                obj.solution_id = data.submission_id;
                obj.nick = data.nick;
                obj.user_id = data.user_id;
                obj.length = data.val.source.length;
                obj.language = data.val.language;
                obj.memory = obj.time = 0;
                obj.in_date = new Date().toISOString();
                obj.judger = "0号机";
                obj.result = 0;
                this.problem_list.pop();
                this.problem_list.unshift(obj);
            },
            update: function (data) {
                var solution_id = data.solution_id;
                var status = data.state;
                var time = data.time;
                var memory = data.memory;
                var pass_rate = data.pass_rate;
                for (let i of this.problem_list) {
                    if (i.solution_id == solution_id) {
                        i.result = status;
                        i.time = time;
                        i.memory = memory;
                        i.pass_rate = pass_rate;
                        return;
                    }
                }
            },
            tag:function(tag_name,$event){
                this.current_tag = tag_name;
            }
        },
        created: function () {
            var that = this;
            var problem_id = (this.problem_id = this.getProblemID()||null) || "null";
            var user_id = (this.user_id = this.getUserId()||null) || "null";
            var language = (this.language = this.getLanguage()||-1)==-1 ? "null" : this.getLanguage();
            var result = (this.problem_result = this.getResult()||-1) == -1 ? "null" : this.problem_result;
            var cid = this.cid||"";
            $.get("../api/status/" + problem_id + "/" + user_id + "/" + language + "/" + result + "/0/"+cid, function (data) {
                    that.dim = false;
                    that.search_func(data);
                })
                $.get("../api/status/" + problem_id + "/" + user_id + "/" + language + "/" + result + "/0/"+cid, function (data) {
                    that.dim = false;
                    that.search_func(data);
                })
        },
        mounted: function () {
            $.get("../api/status/graph?cid="+this.cid,function(data){
                draw(data);
            })
        }
    })
</script>
<?php include("template/$OJ_TEMPLATE/bottom.php"); ?>
</body>
</html>
