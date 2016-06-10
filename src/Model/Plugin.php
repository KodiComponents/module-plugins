<?php

namespace KodiCMS\Plugins\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * KodiCMS\Plugins\Model\Plugin
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property integer $id
 * @property string $name
 * @property string $path
 * @property string $settings
 * @method static \Illuminate\Database\Query\Builder|\KodiCMS\Plugins\Model\Plugin whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\KodiCMS\Plugins\Model\Plugin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\KodiCMS\Plugins\Model\Plugin whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\KodiCMS\Plugins\Model\Plugin whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\KodiCMS\Plugins\Model\Plugin wherePath($value)
 * @method static \Illuminate\Database\Query\Builder|\KodiCMS\Plugins\Model\Plugin whereSettings($value)
 */
class Plugin extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'installed_plugins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'path', 'settings'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'       => 'integer',
        'name'     => 'string',
        'path'     => 'string',
        'settings' => 'array',
    ];
}
