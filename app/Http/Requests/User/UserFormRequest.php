<?php

namespace App\Http\Requests\User;

use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

final class UserFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_name'  => ['required','min:4','max:11',Rule::unique('user')->ignore($this->route('user'))],
            'real_name'  => 'required',
            'mobile'     => ['required','min:11','max:11',Rule::unique('user')->ignore($this->route('user'))],
            'agent_code' => 'required|exists:agent_code,code',
        ];
    }
}
