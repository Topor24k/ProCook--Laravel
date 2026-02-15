<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255'
            ],
            'short_description' => [
                'required',
                'string',
                'min:10',
                'max:500'
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:5120' // Max 5MB
            ],
            'cuisine_type' => [
                'required',
                'string',
                'max:100'
            ],
            'category' => [
                'required',
                'string',
                'max:100'
            ],
            'prep_time' => [
                'required',
                'integer',
                'min:1',
                'max:1440' // Max 24 hours
            ],
            'cook_time' => [
                'required',
                'integer',
                'min:0',
                'max:1440' // Max 24 hours
            ],
            'serving_size' => [
                'required',
                'integer',
                'min:1',
                'max:100'
            ],
            'preparation_notes' => [
                'nullable',
                'string',
                'min:20',
                'max:10000'
            ],
            'ingredients' => [
                'required',
                'array',
                'min:1',
                'max:50' // Max 50 ingredients
            ],
            'ingredients.*.name' => [
                'required',
                'string',
                'max:255'
            ],
            'ingredients.*.measurement' => [
                'required',
                'string',
                'max:50'
            ],
            'ingredients.*.substitution_option' => [
                'nullable',
                'string',
                'max:255'
            ],
            'ingredients.*.allergen_info' => [
                'nullable',
                'string',
                'max:255'
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Recipe title is required.',
            'title.min' => 'Recipe title must be at least 3 characters.',
            'title.max' => 'Recipe title cannot exceed 255 characters.',
            
            'short_description.required' => 'Short description is required.',
            'short_description.min' => 'Description must be at least 10 characters.',
            'short_description.max' => 'Description cannot exceed 500 characters.',
            
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be a file of type: jpeg, jpg, png, gif, webp.',
            'image.max' => 'Image size cannot exceed 5MB.',
            
            'cuisine_type.required' => 'Please select a cuisine type.',
            'category.required' => 'Please select a category.',
            
            'prep_time.required' => 'Preparation time is required.',
            'prep_time.min' => 'Preparation time must be at least 1 minute.',
            'prep_time.max' => 'Preparation time cannot exceed 24 hours.',
            
            'cook_time.required' => 'Cooking time is required.',
            'cook_time.max' => 'Cooking time cannot exceed 24 hours.',
            
            'serving_size.required' => 'Serving size is required.',
            'serving_size.min' => 'Serving size must be at least 1.',
            'serving_size.max' => 'Serving size cannot exceed 100.',
            
            'preparation_notes.min' => 'Instructions must be at least 20 characters.',
            
            'ingredients.required' => 'At least one ingredient is required.',
            'ingredients.min' => 'At least one ingredient is required.',
            'ingredients.max' => 'Cannot add more than 50 ingredients.',
            
            'ingredients.*.name.required' => 'Ingredient name is required.',
            'ingredients.*.measurement.required' => 'Ingredient measurement is required.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Recipe validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'You must be logged in to create a recipe.'
            ], 401)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $data = [
            'title' => strip_tags(trim($this->title ?? '')),
            'short_description' => strip_tags(trim($this->short_description ?? '')),
            'cuisine_type' => strip_tags(trim($this->cuisine_type ?? '')),
            'category' => strip_tags(trim($this->category ?? '')),
        ];

        // Decode ingredients if it's a JSON string (from FormData)
        if ($this->has('ingredients') && is_string($this->ingredients)) {
            $data['ingredients'] = json_decode($this->ingredients, true);
        }

        $this->merge($data);
    }
}
