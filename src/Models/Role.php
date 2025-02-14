<?php

namespace Mate\Roles\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
      'name',
    ];

    protected $with = ['users', 'permissions'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('roles.tables_names.roles', 'roles');
    }

    /**
     * The users that belong to the role.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }

    /**
     * Has many permissions
     *
     * @return HasMany
     * */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermissions::class, 'role_id', 'id');
    }

    public static function dropDownOptions(): array
    {
        return self::all()->sortBy('name')->pluck('name', 'id')->toArray();
    }
}
