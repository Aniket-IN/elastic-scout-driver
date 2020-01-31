<?php
declare(strict_types=1);

namespace ElasticScoutDriver\Tests\Integration;

use ElasticClient\ServiceProvider as ElasticClientServiceProvider;
use ElasticMigrations\ServiceProvider as ElasticMigrationsServiceProvider;
use ElasticScoutDriver\ServiceProvider as ElasticScoutDriverServiceProvider;
use Laravel\Scout\ScoutServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ScoutServiceProvider::class,
            ElasticClientServiceProvider::class,
            ElasticMigrationsServiceProvider::class,
            ElasticScoutDriverServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('scout.driver', 'elastic');
        $app['config']->set('elastic.migrations.storage_directory', __DIR__.'/../app/elastic/migrations');
        $app['config']->set('elastic.scout_driver.refresh_documents', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../app/database/migrations');
        $this->withFactories(__DIR__.'/../app/database/factories');

        $this->artisan('migrate:refresh')->run();
        $this->artisan('elastic:migrate:refresh')->run();
    }

    protected function tearDown(): void
    {
        $this->artisan('elastic:migrate:reset')->run();
        $this->artisan('migrate:reset')->run();

        parent::tearDown();
    }
}
