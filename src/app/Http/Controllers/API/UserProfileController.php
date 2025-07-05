<?php

namespace App\Http\Controllers\API;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $profiles = UserProfile::paginate(10);
        return $this->sendPaginatedResponse($profiles, 'User profiles retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user_profiles,email',
            'title' => 'required|string|max:255',
            'skills' => 'nullable|array',
            'experience' => 'nullable|array',
            'education' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $profile = UserProfile::create($input);

        return $this->sendResponse($profile, 'User profile created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $profile = UserProfile::find($id);

        if (is_null($profile)) {
            return $this->sendError('User profile not found');
        }

        return $this->sendResponse($profile, 'User profile retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $profile = UserProfile::find($id);

        if (is_null($profile)) {
            return $this->sendError('User profile not found');
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:user_profiles,email,' . $id,
            'title' => 'sometimes|required|string|max:255',
            'skills' => 'nullable|array',
            'experience' => 'nullable|array',
            'education' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $profile->update($input);

        return $this->sendResponse($profile, 'User profile updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $profile = UserProfile::find($id);

        if (is_null($profile)) {
            return $this->sendError('User profile not found');
        }

        $profile->delete();

        return $this->sendResponse([], 'User profile deleted successfully');
    }

    /**
     * Generate a customized resume based on a post description.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function customizeResume(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'profile_id' => 'required|exists:user_profiles,id',
            'post_description' => 'required|string',
            'format' => 'nullable|in:pdf,docx,txt,md',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        $profile = UserProfile::find($input['profile_id']);
        
        // TODO: Implement AI-powered resume customization using Gemini API
        // This is a placeholder for the actual implementation
        $customizedResume = [
            'profile_id' => $profile->id,
            'format' => $input['format'] ?? 'pdf',
            'content' => 'Customized resume content based on post description',
            'suggested_changes' => [
                'Highlighted relevant skills',
                'Reordered experience sections',
                'Added keywords from post description'
            ]
        ];

        return $this->sendResponse($customizedResume, 'Resume customized successfully');
    }
}
