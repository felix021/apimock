<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?=Yii::app()->name?> - Api Editor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="i@felix021.com">
    <link rel="icon" type="image/png" href="/img/favicon.png">
    <link rel="apple-touch-icon" href="/img/apple-icon.png">

    <!-- Le styles -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }

      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
    </style>
    <link href="/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="shortcut icon" href="/ico/favicon.png">
	<script>
		function ul_key_event(evt) {
			var evt = (evt) ? evt : ((window.event) ? window.event : "")
			var key = evt.keyCode ? evt.keyCode : evt.which;
			if (key == 13 || key == 10) {
				if(evt.ctrlKey) { //CTRL+ENTER => focus()
					$('#query').focus();
				}
				else {
					var obj = document.activeElement;
					if (obj == $('#query')[0])
						details(query_results[0]);
					else
						details(obj.innerHTML);
				}
            }
        }
	</script>
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner" style="opacity:0.75">
        <div class="container-fluid">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#"><?=Yii::app()->name?></a>
          <div class="nav-collapse collapse">
              <ul class="nav">
                  <li class="active"><a href="#">Api</a></li>
                  <li><a href="/apieditor/batch">场景切换</a></li>
              </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    
    <script type="text/javascript">

        function build_obj(data) {
            if (!Array.isArray(data)) {
                alert('bad input: ' + data);
                return;
            }
            var obj = $(data[0]);
            for (var i = 1; i < data.length; i++) {
                var child = data[i];
                if (Array.isArray(child))
                    child = build_obj(child);
                obj.append(child);
            }
            return obj;
        }

        window.onload = function() {
            $('#query').focus();
        };

        function add_api()
        {
            var api_name = $('#query').val();
            $.post('/apieditor/addApi', {api_name: api_name}, function (rsp, status, xhr) {
                if (rsp.code == 0) {
                    alert('添加成功');
                    details(api_name);
                } else {
                    alert('添加失败: ' + rsp.message);
                }
            }, 'json');
        }

        var query_text = '';
        function query(text) {
            if (text == query_text)
                return;
            query_text = text;
            if (text.length >= 2) {
                do_query(text);
            }
        }

		var query_results = [];
        function do_query(text) {
            $.post('/apieditor/list', {key: text}, function (rsp) {
                if (rsp.code != 0) {
                    //alert("invalid request");
                    return;
                }
                var ul = $('#result').html('');
                query_results = [];
                var api_list = rsp.data.api_list;
                for (var i = 0; i < api_list.length; i++) {
                    var api = api_list[i];
                    var a = $('<a>').html(api.api_name).attr('tabindex', api_list.length + 2)
									.on('click', function() { details(this.innerHTML); });
                    var li = $('<li>').append(a);
                    ul.append(li);
					query_results.push(api.api_name);
                }
                if (api_list.length == 1) {
                    details(api_list[0].api_name);
                }
            });
        }

        function details(api_name)
        {
            $('#result_id').html('0');
            $('#result_desc').val('');
            $('#result_content').val('');

            $.post('/apieditor/detail', {api_name: api_name}, function (rsp) {
                if (rsp.code != 0) {
                    alert("invalid response: " + rsp.message);
                    return;
                }
                $('#api_id').val(rsp.data.api_id);
                $('#api_name').html(rsp.data.api_name);
                $('#api_desc').val(rsp.data.api_desc);

                var t = $('#result_set').html('');
                t.append(build_obj([
                    '<tr style="background-color:#ccc;">',
                    ['<td>', '编号'],
                    ['<td width="350">', '说明'],
                    ['<td>', '操作']
                ]));

                var result_set = rsp.data.result_set;
                for (var i = 0; i < result_set.length; i++) {
                    var result = result_set[i];
                    t.append(build_obj([
                        '<tr>',
                        ['<td>', result.result_id],
                        ['<td width="350">', $('<span>').attr('id', 'result_desc_' + result.result_id).html(result.result_desc)],
                        ['<td>',
                            result.chosen ? $('<a class="btn btn-info">').html('已选中') :
                            $('<a class="btn">').html('切换').attr('result_id', result.result_id).on('click', function () { choose_result($(this).attr('result_id')); }),
                            ' ',
                            $('<a class="btn">').html('查看').attr('result_id', result.result_id).on('click', function () { display_result($(this).attr('result_id')); }),
                            ' ',
                            $('<a class="btn">').html('删除').attr('result_id', result.result_id).on('click', function () { remove_result($(this).attr('result_id')); }),
                        ]
                    ]));

                    if (result.chosen == 1) {
                        display_result(result.result_id);
                    }
                }

                $('#detail').css('display', 'block');
            });
        }

        function details_current() {
            details($('#api_name').html());
        }

        function choose_result(result_id) {
            var api_id = $('#api_id').val();
            $.post('/apieditor/choose', {api_id: api_id, result_id: result_id}, function (rsp) {
                if (rsp.code != 0) {
                    alert("invalid response: " + rsp.message);
                    return;
                }
                alert('切换成功');
                details($('#api_name').html());
            });
        }

        function display_result(result_id) {
            $('#result_id').html(result_id);
            $.post('/apieditor/result', {result_id: result_id}, function (rsp) {
                if (rsp.code != 0) {
                    alert("invalid response: " + rsp.message);
                    return;
                }
                $('#result_desc').val(rsp.data.result_desc);
                $('#result_content').val(rsp.data.result_content);
            });
        }

        function new_result()
        {
            $('#result_id').html('0');
            save_result();
        }

        function remove_result(result_id) {
            if (!confirm('确认要删除这一条吗?')) {
                return;
            }
            $.post('/apieditor/removeResult', {result_id: result_id}, function (rsp, status, xhr) {
                if (rsp.code == 0) {
                    alert('删除成功');
                    details_current();
                } else {
                    alert('删除失败: ' + rsp.message);
                }
            }, 'json');
        }

        function save_result()
        {
            var result_id = $('#result_id').html();
            var result_desc = $('#result_desc').val();
            $.post('/apieditor/saveResult', {
                    result_id: result_id,
                    result_desc: result_desc,
                    result_content: $('#result_content').val(),
                    api_id: $('#api_id').val()
                },
                function (rsp, status, xhr) {
                    if (rsp.code != 0) {
                        alert("invalid response: " + rsp.message);
                        return;
                    }
                    alert('保存成功!');
                    if (result_id == 0) {
                        details($('#api_name').html());
                    } else {
                        $('#result_id').html(rsp.data.result_id);
                        $('#result_desc_' + result_id).html(result_desc);
                    }
                },
                'json'
            );
        }

        function change_api_desc()
        {
            $.post('/apieditor/changeApiDesc', {
                    api_id: $('#api_id').val(),
                    api_desc: $('#api_desc').val()
                },
                function (rsp, status, xhr) {
                    if (rsp.code != 0) {
                        alert("invalid response: " + rsp.message);
                        return;
                    }
                    alert('修改成功!');
                },
                'json'
            );
        }

        function remove_api()
        {
            if (!confirm("确定需要删除吗？")) {
                return;
            }
            $.post('/apieditor/removeApi', {
                    api_id: $('#api_id').val(),
                },
                function (rsp, status, xhr) {
                    if (rsp.code != 0) {
                        alert("删除失败: " + rsp.message);
                        return;
                    }
                    alert('删除成功!');
                },
                'json'
            );
        }

        function format_json()
        {
            $.post('/apieditor/formatJson', {'result_content': $('#result_content').val()}, function (rsp) {
                if (rsp.code != 0) {
                    alert('invalid response');
                    return;
                }
                $('#result_content').val(rsp.data.json);
            });
        }

    </script>

    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">

          <div class="well sidebar-nav" style="padding:10px;text-align:center;">
              <input tabindex="1" class="input-xlarge search-query" id="query" type="text" style="padding-left:10px; width:65%"
                    placeholder="关键字(支持正则)，输入后按回车/Tab" onkeyup="javascript:query(this.value);" />
              <button class="btn" onclick="add_api()">添加</button>
          </div><!--/.well -->

            <ul class="nav nav-tabs nav-stacked" id="result" onkeypress="ul_key_event()">
              <li><a href="#">先输入查询关键字 ↑↑↑ </a></li>
            </ul>
        </div><!--/span-->
        <div class="span9">
          <div class="hero-unit" style="padding:20px;">
            <h3 id="api_name">查询关键字支持模糊查询api名字用途</h3>
            <div id="detail" style="display:none;font-size:14px;">
            <div>
                <input type="hidden" id="api_id" name="api_id" value=""/>
                <div class="input-append">
                    <input type="text" id="api_desc">
                    <input type="button" onclick="change_api_desc()" value="修改" class="btn" />
                    <button class="btn" onclick="remove_api()">删除API</button>
                </div>
                <table id="result_set" class="table table-bordered"></table>
            </div>
            <div>
                <p><strong>结果</strong></p>
                <div class="input-prepend input-append">
                    <span class="add-on" id="result_id">0</span>
                    <input type="text" id="result_desc" name="result_desc" style="width:300px" value="" placeholder="(描述信息)"/>
                    <a class="btn" href="#" onclick="new_result()">新建</a>
                    <input type="button" onclick="save_result()" value="保存" class="btn" />
                    <button class="btn" onclick="format_json()">json排版</button>
                </div>
                <textarea id="result_content" name="result_content" style="width:98%;height:600px;" placeholder="(api返回json)"></textarea>
            </div>
            </div>
          </div>
        </div><!--/span-->
      </div><!--/row-->

        <div id="container_tooltips"></div>
    </div><!--/.fluid-container-->

    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>

  </body>
</html>

