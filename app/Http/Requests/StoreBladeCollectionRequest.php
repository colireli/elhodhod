<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBladeCollectionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'images' => 'required|array|max:3', // Ensure it's an array and limit to 3 items
            'images.*' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048', // Validate each image
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'images.required' => 'At least one image is required.',
            'images.array' => 'Images must be an array.',
            'images.max' => 'You can upload a maximum of 3 images.',
            'images.*.required' => 'Each image is required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Only jpg, jpeg, png, and gif images are allowed.',
            'images.*.max' => 'Each image must be less than 2MB.',
        ];
    }
}
