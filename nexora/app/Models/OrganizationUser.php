<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationUser extends Model
{
    protected $table = 'organization_users';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function addedByOrganizationUser()
    {
        return $this->hasOne(
            OrganizationUser::class,
            'user_id',
            'added_by'
        )->whereColumn(
            'organization_id',
            'organization_users.organization_id'
        );
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function updatedByOrganizationUser()
    {
        return $this->hasOne(
            OrganizationUser::class,
            'user_id',
            'updated_by'
        )->whereColumn(
            'organization_id',
            'organization_users.organization_id'
        );
    }
}
