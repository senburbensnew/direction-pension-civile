<?php

namespace App\Services\Account;

use App\Models\User;
use App\Models\UserCategoryAssignment;
use Illuminate\Support\Collection;

class UserCategoryResolver
{
    public function __construct(
        private readonly ?User $user,
    ) {
    }

    public static function for(?User $user): self
    {
        return new self($user);
    }

    /**
     * @return Collection<int, UserCategoryAssignment>
     */
    public function activeAssignments(): Collection
    {
        if (! $this->user) {
            return collect();
        }

        return $this->user->categoryAssignments()
            ->with('category')
            ->get()
            ->filter(fn (UserCategoryAssignment $assignment) => $assignment->isActive())
            ->values();
    }

    /**
     * @return list<string>
     */
    public function categoryCodes(): array
    {
        return $this->activeAssignments()
            ->pluck('user_category_code')
            ->unique()
            ->values()
            ->all();
    }

    public function hasCategory(string $code): bool
    {
        return in_array($code, $this->categoryCodes(), true);
    }

    public function primaryCategoryCode(): ?string
    {
        if (! $this->user) {
            return null;
        }

        $primary = $this->activeAssignments()
            ->first(fn (UserCategoryAssignment $a) => $a->is_primary);

        if ($primary) {
            return $primary->user_category_code;
        }

        return $this->categoryCodes()[0] ?? null;
    }

    public function hasPensionPortalAccess(): bool
    {
        if ($this->hasCategory('pensionne')) {
            return true;
        }

        if (! $this->user) {
            return false;
        }

        return $this->user->beneficiaireLinks()->exists()
            || $this->user->pensionMandats()->exists();
    }

    public function hasAgentPortalAccess(): bool
    {
        if ($this->hasCategory('personnel_dpc')) {
            return true;
        }

        if (! $this->user) {
            return false;
        }

        if ($this->user->hasRole('admin')) {
            return true;
        }

        $portalRoles = config('dpc_roles.portal_roles', []);

        foreach ($this->user->getRoleNames() as $roleName) {
            if (! in_array((string) $roleName, $portalRoles, true)) {
                return true;
            }
        }

        return false;
    }

}
