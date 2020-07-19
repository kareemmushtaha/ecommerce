<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MainCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [  /* يعني لو كان في id جوا الفورم مش ضروري تدخل صورة ولكن لو كان ال id مش موجود بالفروم اذاٌ ضروري تدخل صورة */
            'photo' => 'required_without:id|mimes:jpg,jpeg,png',
            'category' => 'required|array|min:1', /* must be  get minimum on request */
            'category.*.name' => 'required',
            'category.*.abbr' => 'required',
            //'category.*.active' => 'required',
        ];
    }
}
