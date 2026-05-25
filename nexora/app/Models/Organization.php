<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;
    protected $table = 'organizations';
    protected $guarded = [];

    public function settings()
    {
        return $this->hasOne(OrganizationSetting::class, 'organization_id');
    }
}
    