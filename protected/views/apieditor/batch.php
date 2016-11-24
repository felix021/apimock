<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Api Editor - 场景切换</title>
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
          <a class="brand" href="#">Api Editor</a>
          <div class="nav-collapse collapse">
              <ul class="nav">
                  <li><a href="/apieditor/index">Api</a></li>
                  <li class="active"><a href="/apieditor/batch">场景切换</a></li>
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

        function add_batch()
        {
            var batch_name = $('#query').val();
            $.post('/apieditor/addBatch', {batch_name: batch_name}, function (rsp, status, xhr) {
                if (rsp.code == 0) {
                    alert('添加成功');
                    details(rsp.data.batch_id);
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
            $.post('/apieditor/batchList', {key: text}, function (rsp) {
                if (rsp.code != 0) {
                    //alert("invalid request");
                    return;
                }
                var ul = $('#result').html('');
                query_results = [];
                var batch_list = rsp.data.batch_list;
                for (var i = 0; i < batch_list.length; i++) {
                    var batch = batch_list[i];
                    var a = $('<a>').html(batch.batch_name).attr('tabindex', batch_list.length + 2)
                                    .attr('batch_id', batch.batch_id)
									.on('click', function() { details($(this).attr('batch_id')); });
                    var li = $('<li>').append(a);
                    ul.append(li);
					query_results.push(batch.batch_id);
                }
                if (batch_list.length == 1) {
                    details(batch_list[0].batch_id);
                }
            });
        }

        function details(batch_id)
        {
            $('#rule_id').html('0');
            $('#rule_desc').val('');
            $('#rule_content').val('');

            $.post('/apieditor/batchDetail', {batch_id: batch_id}, function (rsp) {
                if (rsp.code != 0) {
                    alert("invalid response: " + rsp.message);
                    return;
                }
                $('#batch_id').val(rsp.data.batch_id);
                $('#batch_name').val(rsp.data.batch_name).attr('batch_id', rsp.data.batch_id);

                var t = $('#rule_set').html('');
                t.append(build_obj([
                    '<tr style="background-color:#ccc;">',
                    ['<td>', 'api'],
                    ['<td>', 'api用途'],
                    ['<td>', '返回结果说明'],
                    ['<td>', '操作']
                ]));

                var rule_set = rsp.data.rule_set;
                for (var i = 0; i < rule_set.length; i++) {
                    var rule = rule_set[i];
                    t.append(build_obj([
                        '<tr>',
                        ['<td>', rule.api_name],
                        ['<td>', rule.api_desc],
                        ['<td>', rule.result_desc],
                        ['<td>',
            $('<a class="btn">').html('修改').attr('api_name', rule.api_name).on('click', function () { change_rule($(this).attr('api_name')); }),
            '',
            $('<a class="btn">').html('删除').attr('rule_id', rule.rule_id).on('click', function () { remove_rule($(this).attr('rule_id')); })]
                    ]));
                }

                $('#detail').css('display', 'block');
            });
        }

        function details_current()
        {
            details($('#batch_name').attr('batch_id'));
        }

        function remove_rule(rule_id)
        {
            if (!confirm("确定要删除吗?")) {
                return;
            }
            $.post('/apieditor/removeRule', {rule_id:rule_id}, function (rsp) {
                if (rsp.code != 0) {
                    alert('删除失败: ' + rsp.message);
                    return;
                }
                alert('删除成功');
                details_current();
            });
        }

        function change_batch_name()
        {
            var batch_name = $('#batch_name').val();
            var batch_id = $('#batch_id').val();
            $.post('/apieditor/changeBatchName', {batch_id: batch_id, batch_name: batch_name}, function (rsp) {
                if (rsp.code != 0) {
                    alert('添加失败: ' + rsp.message);
                    return;
                }
                alert('修改成功');
                $('#query').val(batch_name);
            });
        }

        function result_select()
        {
            var api_name = $('#api_name').val();
            $.post('/apieditor/detail', {api_name: api_name}, function (rsp) {
                if (rsp.code != 0) {
                    return;
                }
                var sel = $('#result_id').html('');
                $.each(rsp.data.result_set, function (i, result) {
                    sel.append($('<option>').val(result.result_id).html(result.result_desc));
                });
            });
        }

        function change_rule(api_name)
        {
            $('#api_name').val(api_name);
            result_select();
        }

        function save_rule()
        {
            var batch_id = $('#batch_id').val();
            var api_name = $('#api_name').val();
            var result_id = $('#result_id').val();
            $.post('/apieditor/saveRule', {batch_id: batch_id, api_name: api_name, result_id: result_id}, function (rsp) {
                if (rsp.code != 0) {
                    alert('添加失败: ' + rsp.message);
                    return;
                }
                alert('添加成功');
                details_current();
            });
        }

        function apply_batch()
        {
            var batch_id = $('#batch_id').val();
            $.post('/apieditor/applyBatch', {batch_id: batch_id}, function (rsp) {
                if (rsp.code != 0) {
                    alert('切换失败: ' + rsp.message);
                    return;
                }
                alert('切换成功');
            });
        }

    </script>

    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">

          <div class="well sidebar-nav" style="padding:10px;text-align:center;">
              <input tabindex="1" class="input-xlarge search-query" id="query" type="text" style="padding-left:10px; width:65%"
                    placeholder="关键字(支持正则)，输入后按回车/Tab" onkeyup="javascript:query(this.value);" />
              <button class="btn" onclick="add_batch()">添加</button>
          </div><!--/.well -->

            <ul class="nav nav-tabs nav-stacked" id="result" onkeypress="ul_key_event()">
              <li><a href="#">先输入查询关键字 ↑↑↑ </a></li>
            </ul>
        </div><!--/span-->
        <div class="span9">
          <div class="hero-unit" style="padding:20px;">
            <div id="detail" style="display:none;font-size:14px;">
                <input type="hidden" id="batch_id" name="batch_id" value=""/>
                <div class="input-append">
                    <input type="text" id="batch_name">
                    <input type="button" onclick="change_batch_name()" value="修改" class="btn" />
                    <input type="button" onclick="apply_batch()" value="切换" class="btn" />
                </div>
                <table id="rule_set" class="table table-bordered"></table>

                <div class="row">
                    <div class="span4 offset1"><input class="typeahead" type="text" id="api_name" onblur="result_select()" placeholder="(api)"></div>
                    <div class="span3"><select id="result_id" style="width:100%"></select></div>
                    <div class="span1"><input type="button" onclick="save_rule()" value="添加" class="btn" /></div>
                </div>
            </div>
          </div>
        </div><!--/span-->
      </div><!--/row-->

    </div><!--/.fluid-container-->

    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/typeahead.bundle.min.js"></script>

    <script>

        $('#api_name').typeahead({
                hint: true,
                highlight: true,
                minLength: 2
            },
            {
                async: true,
                source: function (query, syncResults, asyncResults) {
                    $.post('/apieditor/list', {key: query}, function (rsp) {
                        if (rsp.code == 0) {
                            var api_list = rsp.data.api_list;
                            var matches = [];
                            for (var i = 0; i < api_list.length; i++) {
                                matches.push(api_list[i].api_name);
                            }
                            asyncResults(matches);
                        }
                    });
                }
            }
        ).bind('typeahead:select', function(ev, suggestion) {
            result_select();
        });

    </script>

  </body>
</html>

