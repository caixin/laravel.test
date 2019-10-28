@extends('layouts.backend')
@inject('user', 'Models\User\User')

@section('content')
{!! lists_message() !!}
<div class="box">
    <div class="box-header">
        <form method="POST" action="{{ route("$controller.search") }}">
            @csrf
            <input type="hidden" name="sidebar" value="{{ $search['sidebar'] ?? '' }}">
            <div class="col-xs-1" style="width:auto;">
                <label>营运商</label>
                <select name="operator_id" class="form-control">
                    @foreach ($operator as $key => $val)
                        <option value="{{ $key }}" {{ $search['operator_id'] ?? '' == $key ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xs-1" style="width:150px;">
                <label>用户名称</label>
                <input type="text" name="user_name" class="form-control" placeholder="请输入..." value="{{ $search['user_name'] ?? '' }}">
            </div>
            <div class="col-xs-1" style="width:150px;">
                <label>邀请码</label>
                <input type="text" name="agent_code" class="form-control" placeholder="请输入..." value="{{ $search['agent_code'] ?? '' }}">
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户类型</label>
                <select name="type" class="form-control">
                    @foreach ($user::TYPE as $key => $val)
                        <option value="{{ $key }}" {{ $search['type'] ?? '' == $key ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>用户狀態</label>
                <select name="status" class="form-control">
                    <option value="">全部</option>
                    @foreach ($user::STATUS as $key => $val)
                        <option value="{{ $key }}" {{ $search['status'] ?? '' == $key ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xs-1" style="width:auto;">
                <label>所属分层</label>
                <select name="user_group_id" class="form-control">
                    <option value="">全部</option>
                    @foreach ($user_group as $key => $val)
                        <option value="{{ $key }}" {{ $search['user_group_id'] ?? '' == $key ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xs-1" style="width:250px;">
                <label>添加日期</label>
                <div class="input-group">
                    <input type="text" id="create_time_from" name="create_time1" class="form-control datepicker" style="width:50%" placeholder="起始时间" value="{{ $search['create_time1'] ?? '' }}" autocomplete="off">
                    <input type="text" id="create_time_to" name="create_time2" class="form-control datepicker" style="width:50%" placeholder="结束时间" value="{{ $search['create_time2'] ?? '' }}" autocomplete="off">
                </div>
            </div>
            <div class="col-xs-1">
                <label>&nbsp;</label>
                <button type="submit" class="form-control btn btn-primary">查询</button>
            </div>
        </form>
    </div>
    <div class="box-header">
        <label for="per_page">显示笔数:</label>
        <input type="test" id="per_page" style="text-align:center;" value="{{ $per_page }}" size="1">
        <h5 class="box-title" style="font-size: 14px;">
            <b>总计:</b> {{ $result->total() }} &nbsp;
            <b style="color:red;">蓝色为玩家邀请码，红色为代理邀请码</b>
        </h5>
        {!! $result->links() !!}
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <form method="post" action="{{ url("batch") }}" onsubmit="if (!confirm('您确定要批量设置吗?')) return false;">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><input type="checkbox" id="check_all"></th>
                    <th>{!! sort_title('id', '编号', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('user_name', '用户名称', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('agent_id', '代理名称', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('agent_code', '注册邀请码', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('mobile', '手机号码', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('real_name', '姓名', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('money', '余额', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('money1', '牛牛帐户', $route, $order, $search) !!}</th>
                    <th>输赢</th>
                    <th>{!! sort_title('type', '用户类型', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('status', '狀態', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('user_group_id', '所属分层', $route, $order, $search) !!}</th>
                    <th>{!! sort_title('last_login_ip', '登录IP', $route, $order, $search) !!}</th>
                    <th width="100">{!! sort_title('create_time', '注册日期', $route, $order, $search) !!}</th>
                    <th width="100">{!! sort_title('last_login_time', '最后登录', $route, $order, $search) !!}</th>
                    <th width="160">
                    @if (session('roleid') == 1 || in_array("$controller.create", $allow_url))
                        <button type="button" class="btn btn-primary" onclick="add()">添加</button>
                    @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result as $row)
                    <tr>
                        <td><input type="checkbox" name="id[]" value="{{ $row['id'] }}"></td>
                        <td>{{ $row['id'] }}</td>
                        <td>{!! $row['type'] == 1 ? "<font color=\"#aaaaaa\">$row[user_name]</font>" : $row['user_name'] !!}</td>
                        <td>{{ $row['agent_name'] }}</td>
                        <td style="color:{{ $row['code_color'] }}">{{ $row['agent_code'] }}</td>
                        <td>{{ $row['mobile'] }}</td>
                        <td>{{ $row['real_name'] }}</td>
                        <td>{{ $row['money'] }}</td>
                        <td>{{ $row['money1'] }}</td>
                        <td style="color:{{ $row['profit'] }}">{{ $row['profit'] }}</td>
                        <td>{{ $user::TYPE[$row['type']] }}</td>
                        <td>{{ $user::STATUS[$row['status']] }}</td>
                        <td>{{ $row['user_group_id'] }}</td>
                        <td>{{ $row['last_login_ip'] }}</td>
                        <td>{{ $row['create_time'] }}</td>
                        <td>{{ $row['last_login_time'] }}</td>
                        <td>
                        @if (session('roleid') == 1 || in_array("$controller.edit_pwd", $allow_url))
                            <button type="button" class="btn btn-primary" style="margin-bottom: 3px;" onclick="edit_pwd({{ $row->id }})">重置密码</button>
                        @endif
                        @if (session('roleid') == 1 || in_array("$controller.edit", $allow_url))
                            <button type="button" class="btn btn-primary" style="margin-bottom: 3px;" onclick="edit({{ $row->id }})">编辑</button>
                        @endif
                        @if (session('roleid') == 1 || in_array("$controller.edit_money", $allow_url))
                            <button type="button" class="btn btn-primary" onclick="edit_money({{ $row->id }})">人工存款</button>
                        @endif
                        @if (session('roleid') == 1 || in_array("$controller.remark", $allow_url))
                            <button type="button" class="btn btn-primary" onclick="remark({{ $row->id }})">备注</button>
                        @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </form>
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
        {!! $result->links() !!}
    </div>
</div>
<script>
    //添加
    function add() {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '{{ url("user/create") }}?operator_id={{ $search['operator_id'] }}'
        });
    }
    //编辑
    function edit_pwd(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '{{ url("user/1/edit") }}'
        });
    }
    //编辑
    function edit(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '{{ url("user") }}/' + id + '/edit'
        });
    }
    //修改餘額
    function edit_money(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['50%', '90%'],
            content: '{{ url("user") }}' + id
        });
    }
    //備註
    function remark(id) {
        layer.open({
            type: 2,
            shadeClose: false,
            title: false,
            closeBtn: [0, true],
            shade: [0.8, '#000'],
            border: [1],
            offset: ['20px', ''],
            area: ['80%', '90%'],
            content: '{{ url("user") }}' + id
        });
    }

    $('#check_all').click(function(){
        $('input[name="id[]"]').each(function(){
            $(this).prop('checked',$('#check_all').prop('checked'));
        });
    });
</script>
@endsection
