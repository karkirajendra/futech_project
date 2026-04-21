<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255','min:3'],
            'content' => ['required', 'string','min:10'],
            'image' => ['nullable', 'image','mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }


    public function messages(): array
    {
        return [
            'title.required' => 'Blog title is required.',
            'title.min' => 'Title must be at least 3 characters.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'content.required' => 'Blog content is required.',
            'content.min' => 'Content must be at least 10 characters.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'Image size cannot exceed 2MB.',
        ];
    }
    protected function prepareForValidation(): void
    {
        // Sanitize title and content
        $this->merge([
            'title' => strip_tags($this->title),
            'content' => strip_tags($this->content, '<p><br><b><i><u><a><ul><ol><li>'),
        ]);
    }

    /**
     * Force JSON response on validation errors for API usage.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
