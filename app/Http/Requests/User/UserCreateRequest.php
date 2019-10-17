<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;

final class UserCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_name'    => 'required|min:4|max:11|unique:user,user_name',
            'user_pwd'     => 'required|min:6|max:12',
            'security_pwd' => 'required|min:6|max:12',
            'real_name'    => 'required',
            'mobile'       => 'required|min:11|max:11|unique:user,mobile',
            'agent_code'   => 'required|exists:agent_code,code',
        ];
    }
}
