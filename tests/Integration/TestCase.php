<?php declare(strict_types=1);

namespace Elastic\ScoutDriver\Tests\Integration;

use Elastic\Client\ServiceProvider as ElasticClientServiceProvider;
use Elastic\Migrations\ServiceProvider as ElasticMigrationsServiceProvider;
use Elastic\ScoutDriver\ServiceProvider as ElasticScoutDriverServiceProvider;
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
        $app['config']->set('elastic.migrations.storage.default_path', dirname(__DIR__) . '/App/elastic/migrations');
        $app['config']->set('elastic.scout_driver.refresh_documents', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(dirname(__DIR__) . '/App/database/migrations');
        $this->withFactories(dirname(__DIR__) . '/App/database/factories');

        $this->artisan('migrate')->run();
        $this->artisan('elastic:migrate')->run();
    }

    protected function tearDown(): void
    {
        $this->artisan('elastic:migrate:reset')->run();
        $this->artisan('migrate:reset')->run();

        parent::tearDown();
    }
}
