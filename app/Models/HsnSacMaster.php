<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HsnSacMaster extends Model
{
    use SoftDeletes;

    protected $table = 'hsn_sac_master';

    protected $fillable = ['code', 'description', 'is_active'];
}
