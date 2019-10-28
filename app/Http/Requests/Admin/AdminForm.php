<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

final class AdminForm extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'username'     => 'required',
                    'password'     => 'required|min:6|max:12',
                    'mobile'       => 'required|min:11|max:11',
                    'roleid'       => 'required',
                    'security_pwd' => 'required_if:is_agent,1',
                ];
            }
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'username'     => ['required',Rule::unique('admin')->ignore($this->route('admin'))],
                    'mobile'       => ['required', 'min:11', 'max:11',Rule::unique('admin')->ignore($this->route('admin'))],
                    'roleid'       => 'required',
                ];
            }
            default: break;
        }
    }

    /**
     * 獲取已定義驗證規則的錯誤消息。
     *
     * @return array
     */
    public function messages()
    {
        return [
            'security_pwd.required_if' => '勾选代理时 提现密码 不可空白',
        ];
    }
}
