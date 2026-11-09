<?php

namespace Tests;

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tes yang memakai RefreshDatabase selalu memiliki peran & izin Spatie.
     */
    protected $seed = true;

    protected $seeder = RoleAndPermissionSeeder::class;
}
