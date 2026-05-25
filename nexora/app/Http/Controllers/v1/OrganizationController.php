<?php

namespace App\Http\Controllers\v1;

use App\Constants\CommonConstant;
use App\Constants\HttpStatusConstant;
use App\Http\Controllers\BaseApiController;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends BaseApiController
{

    /**
     * List all organizations.
     */
    public function getOrganizations(Request $request)
    {
        $organizations = Organization::query();
        [$page, $limit] = getPaginationParams($request);

        $total = (clone $organizations)->count();
        $organizations_data = $organizations->skip(($page - 1) * $limit)->take($limit)->get();
        $response = $organizations_data->map(function ($organization) {
            return [
                'id' => $organization->id,
                'name' => $organization->name,
                'created_by' => $organization->created_by,
                'status' => $organization->status,
                'created_at' => $organization->created_at,
                'updated_at' => $organization->updated_at,
            ];
        });
        $meta = buildPaginationMeta($total, $page, $limit);
        return successResponse(HttpStatusConstant::OK, $response, $meta);
    }

    /**
     * Create a new organization.
     */
    public function createOrganization(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organizations,name',
        ]);
        try{
            $user_id = $this->user_id();
            $user_type = $this->user_type();
            if ($user_type != CommonConstant::USER_TYPE_ADMIN && $user_type != CommonConstant::USER_TYPE_USER) {
                return errorResponse(HttpStatusConstant::FORBIDDEN, 'FORBIDDEN', 'You do not have permission to create an organization');
            }
            DB::beginTransaction();
            $organization = Organization::create([
                'name' => $request->name,
                'created_by' => $user_id,
                'status' => CommonConstant::ACTIVE_STATUS,
            ]);
            if (!$organization) {
                return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Failed to create organization');
            }
            $organizationSetting = OrganizationSetting::create([
                'organization_id' => $organization->id,
                'timezone' => $request->timezone ?? 'UTC',
                'currency' => $request->currency ?? 'USD',
                'created_by' => $user_id,
                'date_format' => $request->date_format ?? 'Y-m-d',
                'logo' => $request->logo ?? null,
                'theme' => $request->theme ?? 'light'
            ]);
            $response = [
                'id' => $organization->id,
                'name' => $organization->name,
                'created_by' => $organization->created_by,
                'status' => $organization->status,
                'created_at' => $organization->created_at,
                'updated_at' => $organization->updated_at,
                'settings' => $organizationSetting,
            ];
            DB::commit();
            return successResponse(HttpStatusConstant::CREATED, $response);
        } catch (\Exception $e) {
            DB::rollBack();
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Something went wrong while creating the organization');
        }
    }

    /**
     * Get organization details by ID.
     */
    public function getOrganizationDetails($id)
    {
        $organization = Organization::where('id', $id)->where('created_by', $this->user_id())->first();
        if (!$organization) {
            return errorResponse(HttpStatusConstant::NOT_FOUND, 'NOT_FOUND', 'Organization not found');
        }
        $response = [
            'id' => $organization->id,
            'name' => $organization->name,
            'created_by' => $organization->created_by,
            'status' => $organization->status,
            'created_at' => $organization->created_at,
            'updated_at' => $organization->updated_at,
            'settings' => $organization->settings,
        ];
        return successResponse(HttpStatusConstant::OK, $response);
    }

    /**
     * Update organization details by ID.
     */
    public function updateOrganization(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organizations,name,'.$id,
        ]);
        try {
            $organization = Organization::where('id', $id)->where('created_by', $this->user_id())->first();
            if (!$organization) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'NOT_FOUND', 'Organization not found');
            }
            $user_id = $this->user_id();
            $user_type = $this->user_type();
            if ($user_type != CommonConstant::USER_TYPE_ADMIN && $user_type != CommonConstant::USER_TYPE_USER) {
                return errorResponse(HttpStatusConstant::FORBIDDEN, 'FORBIDDEN', 'You do not have permission to update this organization');
            }
            $organization->update([
                'name' => $request->name,
                'status' => $request->status ?? $organization->status,
                'updated_by' => $user_id,
            ]);
            $response = [
                'id' => $organization->id,
                'name' => $organization->name,
                'created_by' => $organization->created_by,
                'status' => $organization->status,
                'created_at' => $organization->created_at,
                'updated_at' => $organization->updated_at,
            ];
            return successResponse(HttpStatusConstant::OK, $response);
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Something went wrong while updating the organization');
        }
    }

    /**
     * Delete organization by ID.
     */
    public function deleteOrganization($id)
    {
        try {
            $organization = Organization::where('id', $id)->where('created_by', $this->user_id())->first();
            if (!$organization) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'NOT_FOUND', 'Organization not found');
            }
            $user_id = $this->user_id();
            $user_type = $this->user_type();
            if ($user_type != CommonConstant::USER_TYPE_ADMIN && $user_type != CommonConstant::USER_TYPE_USER) {
                return errorResponse(HttpStatusConstant::FORBIDDEN, 'FORBIDDEN', 'You do not have permission to delete this organization');
            }
            $organization->delete();
            return successResponse(HttpStatusConstant::OK, null, 'Organization deleted successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Something went wrong while deleting the organization' );
        }
    }

    /**
    * Update organization settings by ID.
    */
    public function updateOrganizationSettings(Request $request, $organization_id)
    {
        try {
            $organization_settings = OrganizationSetting::where('organization_id', $organization_id)->where('created_by', $this->user_id())->first();
            if (!$organization_settings) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'NOT_FOUND', 'Organization settings not found');
            }
            $user_id = $this->user_id();
            $user_type = $this->user_type();
            if ($user_type != CommonConstant::USER_TYPE_ADMIN && $user_type != CommonConstant::USER_TYPE_USER) {
                return errorResponse(HttpStatusConstant::FORBIDDEN, 'FORBIDDEN', 'You do not have permission to update this organization settings');
            }
            $organization_settings->update([
                'timezone' => $request->timezone ?? $organization_settings->timezone,
                'currency' => $request->currency ?? $organization_settings->currency,
                'date_format' => $request->date_format ?? $organization_settings->date_format,
                'logo' => $request->logo ?? $organization_settings->logo,
                'theme' => $request->theme ?? $organization_settings->theme,
                'updated_by' => $user_id,
            ]);
            return successResponse(HttpStatusConstant::OK, 'Settings updated successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Something went wrong while updating the organization settings');
        }
    }

    /**
     * Permanently delete organization by ID.
     */
    public function deleteForeverOrganization($id)
    {
        try {
            $organization = Organization::withTrashed()->where('id', $id)->where('created_by', $this->user_id())->first();
            if (!$organization) {
                return errorResponse(HttpStatusConstant::NOT_FOUND, 'NOT_FOUND', 'Organization not found');
            }
            $user_id = $this->user_id();
            $user_type = $this->user_type();
            if ($user_type != CommonConstant::USER_TYPE_ADMIN && $user_type != CommonConstant::USER_TYPE_USER) {
                return errorResponse(HttpStatusConstant::FORBIDDEN, 'FORBIDDEN', 'You do not have permission to delete this organization');
            }
            $organization->forceDelete();
            return successResponse(HttpStatusConstant::OK, null, 'Organization permanently deleted successfully');
        } catch (\Exception $e) {
            return errorResponse(HttpStatusConstant::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', 'Something went wrong while permanently deleting the organization' );
        }
    }
}
