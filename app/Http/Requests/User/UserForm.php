<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

final class UserForm extends FormRequest
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
                    'user_name'    => 'required|min:4|max:11|unique:user,user_name',
                    'user_pwd'     => 'required|min:6|max:12',
                    'security_pwd' => 'required|min:6|max:12',
                    'real_name'    => 'required',
                    'mobile'       => 'required|min:11|max:11|unique:user,mobile',
                    'agent_code'   => 'required|exists:agent_code,code',
                ];
            }
            case 'PUT':
            case 'PATCH':
            {
                return [
                    'user_name'  => ['required','min:4','max:11',Rule::unique('user')->ignore($this->route('user'))],
                    'real_name'  => 'required',
                    'mobile'     => ['required','min:11','max:11',Rule::unique('user')->ignore($this->route('user'))],
                    'agent_code' => 'required|exists:agent_code,code',
                ];
            }
            default: break;
        }
    }
}
