@extends('layouts.backend')

@section('content')
<div class="box box-{{ $errors->any() ? 'danger' : 'success' }}">
    <!-- /.box-header -->
    <div class="box-body">
        <form method="post" role="form" action="{{ route('user.store') }}">
            @method('POST')
            @csrf
        @if (session('roleid') == 1)
            <div class="form-group {{ $errors->has('super_user') ? 'has-error':'' }}">
                <div class="checkbox">
                    <input type="hidden" name="super_user" value="0">
                    <label>
                        <input type="checkbox" id="super_user" name="super_user" value="1" {{ $row['super_user'] == 1 ? 'checked' : '' }}>
                        超级用户 <span style="color:red;">【勾选后可通行各个营运商并自动加入白名单】</span>
                    </label>
                </div>
            </div>
        @endif
            <div class="form-group {{ $errors->has('user_name') ? 'has-error' : '' }}">
                <label>用户名称</label>
                <input type="text" name="user_name" class="form-control" placeholder="Enter ..." value="{{ old('user_name',$row['user_name']) }}">
                {!! $errors->first('user_name', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('user_pwd') ? 'has-error' : '' }}">
                <label>用户密码 <span style="color:red;">【请输入英数6至12码】</span></label>
                <input type="password" name="user_pwd" class="form-control" placeholder="Enter ...">
                {!! $errors->first('user_pwd', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('security_pwd') ? 'has-error' : '' }}">
                <label>提现密码 <span style="color:red;">【请输入纯数字6码】</span></label>
                <input type="password" name="security_pwd" class="form-control" placeholder="Enter ...">
                {!! $errors->first('security_pwd', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('real_name') ? 'has-error' : '' }}">
                <label>真实姓名 <span style="color:red;">【须与出款的银行户名相同】</span></label>
                <input type="text" name="real_name" class="form-control" placeholder="Enter ..." value="{{ old('real_name',$row['real_name']) }}">
                {!! $errors->first('real_name', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">
                <label>手机号码 <span style="color:red;">【作为短信彩金联系方式】</span></label>
                <input type="text" name="mobile" class="form-control" placeholder="Enter ..." value="{{ old('mobile',$row['mobile']) }}">
                {!! $errors->first('mobile', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('agent_code') ? 'has-error' : '' }}">
                <label>代理邀请码 <span style="color:red;">【请填写代理邀请码】</span></label>
                <input type="text" id="agent_code" name="agent_code" class="form-control" placeholder="Enter ..." value="{{ old('agent_code',$row['agent_code']) }}">
                {!! $errors->first('agent_code', '<span class="help-block">:message</span>') !!}
            </div>
            <div class="form-group {{ $errors->has('user_group_id') ? 'has-error' : '' }}">
                <label>所属分层</label>
                <select name="user_group_id" class="form-control">
                    @foreach ($user_group as $key => $val)
                        <option value="{{ $key }}" {{ old('user_group_id',$row['user_group_id']) == $key ? 'selected' : '' }}>{{ $val }}</option>
                    @endforeach
                </select>
                {!! $errors->first('user_group_id', '<span class="help-block">:message</span>') !!}
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
@if (session('roleid') == 1)
<script>
$('#super_user').click(function(){
    $('#agent_code').prop('readonly',$(this).prop('checked'));
});
</script>
@endif
@endsection
