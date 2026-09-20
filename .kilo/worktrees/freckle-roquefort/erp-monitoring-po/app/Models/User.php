<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'nik', 'email', 'password', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasRole(string $slug): bool
    {
        return $this->hasAnyRole([$slug]);
    }

    public function hasAnyRole(array $slugs): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn (Role $role) => in_array($role->slug, $slugs, true));
        }

        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    public function primaryRoleSlug(): ?string
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->sortBy('id')->first()?->slug;
        }

        return $this->roles()->orderBy('roles.id')->value('slug');
    }
}
