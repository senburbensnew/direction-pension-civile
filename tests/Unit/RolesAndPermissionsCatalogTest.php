<?php

namespace Tests\Unit;

use Database\Seeders\RolesAndPermissionsSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RolesAndPermissionsCatalogTest extends TestCase
{
    #[Test]
    public function catalog_describes_every_seeded_role_and_permission(): void
    {
        $roleDescriptions = RolesAndPermissionsSeeder::roleDescriptions();
        $permissionDescriptions = RolesAndPermissionsSeeder::permissionDescriptions();

        foreach (RolesAndPermissionsSeeder::allRoleNamesForSeeding() as $roleName) {
            $this->assertArrayHasKey($roleName, $roleDescriptions, "Missing role description for {$roleName}");
            $this->assertNotSame('', trim($roleDescriptions[$roleName]));
        }

        foreach (RolesAndPermissionsSeeder::permissionNames() as $permissionName) {
            $this->assertArrayHasKey($permissionName, $permissionDescriptions, "Missing permission description for {$permissionName}");
            $this->assertNotSame('', trim($permissionDescriptions[$permissionName]));
        }
    }
}
