<?php

namespace App\Http\Controllers\v1;

use App\Constants\HttpStatusConstant;
use App\Http\Controllers\BaseApiController;
use App\Models\OrganizationUser;
use Illuminate\Http\Request;

class OrganizationUserController extends BaseApiController
{
    /**
     * Add a user to an organization
     */
    public function addUserToOrganization(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:organization_roles,id'
        ]);
        try{
            $user_id = $request->user_id;
            $organization_id = $request->organization_id;
            $role_id = $request->role_id;

            $organization_user = OrganizationUser::where('organization_id', $organization_id)
                ->where('user_id', $user_id)
                ->first();
            if ($organization_user) {
                return errorResponse(HttpStatusConstant::CONFLICT, 'CONFLICT', 'User is already a member of the organization');
            }
            $new_organization_user = OrganizationUser::create([
                'organization_id' => $organization_id,
                'user_id' => $user_id,
                'role_id' => $role_id,
                'added_by' => $this->user_id(),
                'status' => 1
            ]);
            if (!$new_organization_user) {
                return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Failed to add user to organization');
            }
            return successResponse(HttpStatusConstant::OK, 'User added to organization successfully');
        } catch (\Exception) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Failed to add user to organization');
        }
    }

    public function getOrganizationUsers($org_id)
    {
        $organization_users = OrganizationUser::with(['user:id,name,email','role:id,role_name', 'addedBy:id,name', 'addedByOrganizationUser.role:id,role_name', 'updatedBy:id,name', 'updatedByOrganizationUser.role:id,role_name'])->where('organization_id', $org_id)->get();
        if($organization_users->isEmpty()) {
            return errorResponse(HttpStatusConstant::NOT_FOUND, 'NOT_FOUND', 'No users found in organization');
        }
        $response = $organization_users->map(function ($organization_user) {
            return [
                'id' => $organization_user->id,
                'added_by' => [
                    'id' => $organization_user->addedBy?->id,
                    'name' => $organization_user->addedBy?->name,
                    'role' => $organization_user->addedByOrganizationUser?->role?->role_name,
                ],
                'updated_by' => [
                    'id' => $organization_user->updatedBy?->id,
                    'name' => $organization_user->updatedBy?->name,
                    'role' => $organization_user->updatedByOrganizationUser?->role?->role_name,
                ],
                'user' => $organization_user->user,
                'role' => $organization_user->role,
                'status' => $organization_user->status,
                'created_at' => $organization_user->created_at,
                'updated_at' => $organization_user->updated_at,
            ];
        });
        return successResponse(HttpStatusConstant::OK, $response);
    }

    public function removeUserFromOrganization($org_id, $user_id)
    {
        $organization_user = OrganizationUser::where('organization_id', $org_id)->where('user_id', $user_id)->first();
        if($this->user_id() == $user_id){
            return errorResponse(HttpStatusConstant::FORBIDDEN, 'FORBIDDEN', 'You cannot remove yourself from the organization');
        }
        if (!$organization_user) {
            return errorResponse(HttpStatusConstant::NOT_FOUND, 'NOT_FOUND', 'User not found in organization');
        }
        $organization_user->delete();
        return successResponse(HttpStatusConstant::OK, 'User removed from organization successfully');
    }

    public function updateUser(Request $request, $org_id, $user_id)
    {
        $request->validate([
            'role_id' => 'required|exists:organization_roles,id',
            'status' => 'required|in:0,1'
        ]);
        if($this->user_id() == $user_id){
            return errorResponse(HttpStatusConstant::FORBIDDEN, 'FORBIDDEN', 'You cannot update your own role or status in the organization');
        }
        $organization_user = OrganizationUser::where('organization_id', $org_id)->where('user_id', $user_id)->first();
        if (!$organization_user) {
            return errorResponse(HttpStatusConstant::NOT_FOUND, 'NOT_FOUND', 'User not found in organization');
        }
        $organization_user->update([
            'role_id' => $request->role_id,
            'status' => $request->status,
            'updated_by' => $this->user_id()
        ]);
        return successResponse(HttpStatusConstant::OK, 'User updated successfully');
    }
}
