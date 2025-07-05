<?php

namespace App\Http\Controllers\API;

use App\Models\SearchCriteria;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchCriteriaController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $profileId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($profileId = null)
    {
        $query = SearchCriteria::query();
        
        if ($profileId) {
            $query->where('user_profile_id', $profileId);
        }
        
        $searchCriteria = $query->paginate(10);
        
        return $this->sendPaginatedResponse($searchCriteria, 'Search criteria retrieved successfully');
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
            'user_profile_id' => 'required|exists:user_profiles,id',
            'name' => 'required|string|max:255',
            'is_default' => 'boolean',
            'keywords' => 'nullable|array',
            'locations' => 'nullable|array',
            'post_type' => 'nullable|string|max:100',
            'experience_level' => 'nullable|string|max:100',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gt:min_salary',
            'salary_currency' => 'nullable|string|size:3',
            'is_remote' => 'boolean',
            'industries' => 'nullable|array',
            'companies' => 'nullable|array',
            'post_titles' => 'nullable|array',
            'skills_included' => 'nullable|array',
            'skills_excluded' => 'nullable|array',
            'days_posted' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // If this is set as default, unset default from other criteria for this user
        if (isset($input['is_default']) && $input['is_default']) {
            SearchCriteria::where('user_profile_id', $input['user_profile_id'])
                ->update(['is_default' => false]);
        }

        $searchCriteria = SearchCriteria::create($input);

        return $this->sendResponse($searchCriteria, 'Search criteria created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $searchCriteria = SearchCriteria::find($id);

        if (is_null($searchCriteria)) {
            return $this->sendError('Search criteria not found');
        }

        return $this->sendResponse($searchCriteria, 'Search criteria retrieved successfully');
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
        $searchCriteria = SearchCriteria::find($id);

        if (is_null($searchCriteria)) {
            return $this->sendError('Search criteria not found');
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'name' => 'sometimes|required|string|max:255',
            'is_default' => 'boolean',
            'keywords' => 'nullable|array',
            'locations' => 'nullable|array',
            'post_type' => 'nullable|string|max:100',
            'experience_level' => 'nullable|string|max:100',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gt:min_salary',
            'salary_currency' => 'nullable|string|size:3',
            'is_remote' => 'boolean',
            'industries' => 'nullable|array',
            'companies' => 'nullable|array',
            'post_titles' => 'nullable|array',
            'skills_included' => 'nullable|array',
            'skills_excluded' => 'nullable|array',
            'days_posted' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // If this is set as default, unset default from other criteria for this user
        if (isset($input['is_default']) && $input['is_default']) {
            SearchCriteria::where('user_profile_id', $searchCriteria->user_profile_id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $searchCriteria->update($input);

        return $this->sendResponse($searchCriteria, 'Search criteria updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $searchCriteria = SearchCriteria::find($id);

        if (is_null($searchCriteria)) {
            return $this->sendError('Search criteria not found');
        }

        // Don't allow deletion if this is the only search criteria for the user
        $userCriteriaCount = SearchCriteria::where('user_profile_id', $searchCriteria->user_profile_id)
            ->count();
            
        if ($userCriteriaCount <= 1) {
            return $this->sendError('Cannot delete the only search criteria for this user', [], 409);
        }

        $searchCriteria->delete();

        return $this->sendResponse([], 'Search criteria deleted successfully');
    }

    /**
     * Get the default search criteria for a user profile.
     *
     * @param  int  $profileId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultCriteria($profileId)
    {
        $userProfile = UserProfile::find($profileId);

        if (is_null($userProfile)) {
            return $this->sendError('User profile not found');
        }

        $defaultCriteria = SearchCriteria::where('user_profile_id', $profileId)
            ->where('is_default', true)
            ->first();

        if (is_null($defaultCriteria)) {
            // If no default is set, return the most recently created
            $defaultCriteria = SearchCriteria::where('user_profile_id', $profileId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (is_null($defaultCriteria)) {
                return $this->sendError('No search criteria found for this user');
            }
        }

        return $this->sendResponse($defaultCriteria, 'Default search criteria retrieved successfully');
    }
}
