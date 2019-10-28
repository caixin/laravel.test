@inject('Backend', 'App\Presenters\Layouts\BackendPresenter')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', '后台管理系统') }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('dist/css/skins/_all-skins.min.css') }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <!-- jQuery 3 -->
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- Layer -->
    <link href="{{ asset('plugins/layer/skin/layer.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <script src="{{ asset('plugins/layer/layer.js') }}"></script>
    <!-- timepicker -->
    <link href="{{ asset('plugins/jQueryUI/ui-lightness/jquery-ui-1.10.4.custom.css') }}" rel="stylesheet" type="text/css">
    <script src="{{ asset('plugins/jQueryUI/jquery-ui-1.10.4.custom.js') }}"></script>
    <link href="{{ asset('plugins/jQueryUI/jquery-ui-timepicker-addon.css') }}" rel="stylesheet" type="text/css">
    <script src="{{ asset('plugins/jQueryUI/jquery-ui-timepicker-addon.js') }}"></script>
    <script src="{{ asset('plugins/jQueryUI/ui.datepicker-zh-CN.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <style>
        .input-group .form-control {
            z-index: 1;
        }

        .ui-datepicker-title {
            color: black;
        }

        .table a.sort {
            background-position: 100% 45%;
            background-repeat: no-repeat;
            padding-right: 15px;
        }

        .table a.asc {
            background-image: url("{{ asset('dist/img/asc.png') }}");
        }

        .table a.desc {
            background-image: url("{{ asset('dist/img/desc.png') }}");
        }

        .select2-selection__choice {
            color: #000 !important;
        }
        .table>tbody>tr>td,
        .table>tbody>tr>th,
        .table>tfoot>tr>td,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>thead>tr>th {
            vertical-align: middle;
        }
    </style>
    <script>
        $(document).ready(function() {
            function customRange(input) {
                return {
                    minDate: (input.id.indexOf("_to") != -1 ? $('#' + input.id.replace("to", "from")).datepicker('getDate') : null),
                    maxDate: (input.id.indexOf("_from") != -1 ? $('#' + input.id.replace("from", "to")).datepicker('getDate') : null)
                };
            }
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                beforeShow: customRange
            });
            $('.timepicker').timepicker({
                timeFormat: 'HH:mm:ss',
                changeYear: true,
                changeMonth: true,
                showSecond: true,
                beforeShow: customRange
            });
            $('.secpicker').datetimepicker({
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss',
                changeMonth: true,
                changeYear: true,
                showSecond: true,
                beforeShow: customRange
            });

            $('#per_page').change(function() {
                $.post('{{ url("ajax/setPerPage") }}', {
                    'per_page': $(this).val()
                }, function(data) {
                    if (data == 'done') {
                        location.reload();
                    } else {
                        alert('操作失败!');
                    }
                });
            });
        });
        function layer_open(url) {
            layer.open({
                type: 2,
                shadeClose: false,
                title: false,
                closeBtn: [0, true],
                shade: [0.8, '#000'],
                border: [1],
                offset: ['20px', ''],
                area: ['80%', '90%'],
                content: url
            });
        }
    </script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">
        @if ($sidebar)
            <header class="main-header">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini"><b>后台</b></span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg"><b>后台管理中心</b></span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="col-xs-5" style="margin:8px 0 0 5px;">
                        <select id="global_operator" class="form-control" multiple="multiple" style="height:20px;">
                            @foreach ($allow_operator as $key => $val)
                                <option value="{{ $key }}" {{ Session::get('show_operator') && in_array($key, Session::get('show_operator')) ? 'selected':'' }}>{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- Messages: style can be found in dropdown.less-->
                            <li class="dropdown messages-menu">
                                <a href="#" class="dropdown-toggle">
                                    <i class="fa">在线会员</i>
                                    <span id="top_online" class="label label-success" style="display:none;">0</span>
                                </a>
                            </li>
                            <li class="dropdown messages-menu">
                                <a href="#" class="dropdown-toggle">
                                    <i class="fa">今日注册会员</i>
                                    <span id="top_register" class="label label-success" style="display:none;">0</span>
                                </a>
                            </li>
                            <!-- Tasks: style can be found in dropdown.less -->
                            <li class="dropdown tasks-menu">
                                <a href="#" class="dropdown-toggle">
                                    <i class="fa">充值</i>
                                    <span id="top_recharge" class="label label-danger" style="display:none;">0</span>
                                </a>
                            </li>
                            <li class="dropdown tasks-menu">
                                <a href="#" class="dropdown-toggle">
                                    <i class="fa">提现</i>
                                    <span id="top_withdraw" class="label label-danger" style="display:none;">0</span>
                                </a>
                            </li>
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <span class="hidden-xs">{{ Session::get('username') }}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
                                        <p>{{ Session::get('username') }}</p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="#" class="btn btn-default btn-flat">密码修改</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="{{ route('logout') }}" class="btn btn-default btn-flat">登出</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <script>
                //運營商
                $("#global_operator").select2();
                $("#global_operator").change(function() {
                    var global_operator = [];
                    $(this).find(":selected").each(function() {
                        global_operator[this.value] = this.value;
                    });
                    $.ajax({
                        type: "POST",
                        url: "{{ url('global_operator') }}",
                        data: {
                            operator: global_operator
                        },
                        dataType: "html",
                        success: function(result) {
                            if (result == 'done') {
                                location.reload();
                            }
                        }
                    });
                });
                //更新Top資訊
                var getTopMessage = function() {
                    var vid = document.getElementById("player_audio");
                    $.ajax({
                        type: "POST",
                        url: "{{ url('getTopMessage') }}",
                        data: {},
                        cache: false,
                        dataType: "json",
                        success: function(result) {
                            if (result.player_audio == 1) {
                                vid.play();
                            }
                            $('#top_online').html(result.online)
                            $('#top_register').html(result.register);
                            $('#top_recharge').html(result.recharge);
                            $('#top_withdraw').html(result.withdraw);
                            result.online > 0 ? $('#top_online').show() : $('#top_online').hide();
                            result.register > 0 ? $('#top_register').show() : $('#top_register').hide();
                            result.recharge > 0 ? $('#top_recharge').show() : $('#top_recharge').hide();
                            result.withdraw > 0 ? $('#top_withdraw').show() : $('#top_withdraw').hide();
                        }
                    });
                };
                setInterval(getTopMessage, 5000);
                getTopMessage();
            </script>
            <!-- =============================================== -->

            <!-- Left side column. contains the sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu" data-widget="tree">
                        @foreach ($navList as $row)
                            @if (session('roleid') == 1 || in_array($row['route'], $allow_url))
                                <li class="treeview {{ in_array($route, $row['subNavs']) ? 'active' : '' }}">
                                    <a href="#">
                                        <i class="fa {{ $row['icon'] }}"></i> <span>{{ $row['name'] }}</span>
                                        <span class="pull-right-container">
                                            <i class="fa fa-angle-left pull-right"></i>
                                        </span>
                                    </a>
                                    <ul class="treeview-menu">
                                        @foreach ($row['sub'] as $arr)
                                            @if (Session::get('roleid') == 1 || in_array($arr['route'], $allow_url))
                                                <li class="{{ $route == $arr['route'] ? 'active' : '' }}">
                                                    <a href="{{ route($arr['route']) }}"><i class="fa fa-circle-o"></i> {{ $arr['name'] }}</a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- =============================================== -->

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1 style="font-size:20px;">{{ $title }}</h1>
                    <ol class="breadcrumb">
                        <li><a href="{{ url('') }}"><i class="fa fa-dashboard"></i> 首页</a></li>
                        @foreach ($breadcrumb as $row)
                            <li><a href="{{ url($row['url']) }}">{{ $row['name'] }}</a></li>
                        @endforeach
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    @yield('content')
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version:</b> {{ $version }}
                </div>
                <strong>Page rendered in {{ $Backend->elapsedTime() }} second and used {{ $Backend->ramUsage() }} memory.</strong>
            </footer>
            <audio id="player_audio" controls preload hidden>
                <source src="{{ asset('prompt.mp3') }}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        @else
            <!-- Content Wrapper. Contains page content -->
            <div style="background:#ffffff;">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>{{ $title }}</h1>
                    <ol class="breadcrumb">
                        <li><a><i class="fa fa-dashboard"></i> 首页</a></li>
                        @foreach ($breadcrumb as $row)
                            <li><a>{{ $row['name'] }}</a></li>
                        @endforeach
                    </ol>
                </section>
                <!-- Main content -->
                <section class="content">
                    @yield('content')
                </section>
                <!-- /.content -->
            </div>
        @endif
    </div>
    <!-- ./wrapper -->
    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset('dist/js/demo.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.sidebar-menu').tree();
        })
    </script>
</body>

</html>
