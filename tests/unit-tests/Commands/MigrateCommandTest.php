<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\MigrateCommand;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Tests\Traits\InteractsWithMigrations;

class MigrateCommandTest extends Test
{
    use InteractsWithMigrations;

    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            MigrateCommand::class,
            $this->app->make(MigrateCommand::class)
        );
    }

    /**
     * @test
     */
    public function runs_migrate_on_tenants()
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);

        $this->migrateAndTest('migrate', function (Website $website) {
            $this->connection->set($website, $this->connection->migrationName());
            $this->assertTrue(
                $this->connection->migration()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        });
    }

    /**
     * @test
     * @depends runs_migrate_on_tenants
     */
    public function runs_rollback_on_tenants()
    {
        $this->migrateAndTest('rollback', function (Website $website) {
            $this->connection->set($website, $this->connection->migrationName());
            $this->assertFalse(
                $this->connection->migration()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has table samples"
            );
        });
    }

    /**
     * @test
     * @depends runs_rollback_on_tenants
     */
    public function runs_reset_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->migrateAndTest('reset', function (Website $website) {
            $this->connection->set($website, $this->connection->migrationName());
            $this->assertFalse(
                $this->connection->migration()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has table samples"
            );
        });
    }
}
