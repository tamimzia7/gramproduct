<?php

namespace App\Models\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string|array $roles): bool
    {
        $roleSlugs = $this->roles()->pluck('slug')->all();

        foreach ((array) $roles as $role) {
            if (in_array($role, $roleSlugs, true)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyRole(): bool
    {
        return $this->roles()->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->roles()
            ->whereNotNull('permissions')
            ->get()
            ->contains(fn (Role $role) => $role->hasPermission($permission));
    }

    public function assignRole(string|Role $role): void
    {
        if (! $role instanceof Role) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([$role->id]);
    }
}
