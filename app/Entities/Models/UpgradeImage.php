<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class UpgradeImage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'upgrade_images';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'image',
        'type',
        'created_at',
        'updated_at',
    ];
}
