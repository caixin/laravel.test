@inject('Admin', 'Models\Admin\Admin')
@section('content')
<div class="box box-{{ $errors->any() ? 'danger' : 'success' }}">
    <!-- /.box-header -->
    <div class="box-body">
    @if ($action == 'create')
        <form method="post" role="form" action="{{ route("$controller.store") }}">
    @elseif ($action == 'edit')
        <form method="post" role="form" action="{{ route("$controller.update",['id'=>$row['id']]) }}">
            @method('PUT')
    @endif
            @csrf
            <div class="form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">
                <label>手机号码 <span style="color:red;">【前后台登入用】</span></label>
                <input type="text" name="mobile" class="form-control" placeholder="Enter ..." value="{{ old('mobile',$row['mobile']) }}">
                {!! $errors->first('mobile', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                    <label>用户密码 <span style="color:red;">【请输入英数6至12码】</span></label>
                <input type="text" name="password" class="form-control" placeholder="Enter ...">
                {!! $errors->first('password', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
                <label>用户名称</label>
                <input type="text" name="username" class="form-control" placeholder="Enter ..." value="{{ old('username',$row['username']) }}">
                {!! $errors->first('username', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('roleid') ? 'has-error' : '' }}">
                <label>角色群组</label>
                <select name="roleid" class="form-control">
                @foreach ($role as $key => $val)
                    <option value="{{ $key }}" {{ old('roleid',$row['roleid']) == $key ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
                </select>
                {!! $errors->first('roleid', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                <label>	状态</label>
                <select name="status" class="form-control" {{ $action == 'detail' ? 'disabled' : '' }}>
                    @foreach ($Admin::STATUS as $key => $val)
                        <option value="{{ $key }}" {{ old('status',$row['status']) == $key ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
                {!! $errors->first('status', '<span class="help-block">:message</span>') !!}
            </div>
        @if ($action == 'create')
            <div class="form-group {{ $errors->has('is_agent') ? 'has-error' : '' }}">
                <div class="checkbox">
                    <input type="hidden" name="is_agent" value="0">
                    <label>
                        <input type="checkbox" id="is_agent" name="is_agent" value="1" {{ old('is_agent',$row['is_agent']) == 1 ? 'checked' : '' }}>
                        是否为代理<span style="color:red;">【勾选后会新增玩家代理帐号】</span>
                    </label>
                </div>
            </div>
            <div id="div1" class="form-group {{ $errors->has('security_pwd') ? 'has-error' : '' }}" style="display:none;">
                <label>提现密码 <span style="color:red;">【请输入纯数字6码，非代理不需填写】</span></label>
                <input type="password" name="security_pwd" class="form-control" placeholder="Enter ..." value="">
                {!! $errors->first('security_pwd', '<span class="help-block">:message</span>') !!}
            </div>
        @endif
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
<script>
$('#is_agent').click(function() {
    $(this).prop('checked') ? $('#div1').show() : $('#div1').hide();
});
$('#is_agent').prop('checked') ? $('#div1').show() : $('#div1').hide();
</script>
@endsection
