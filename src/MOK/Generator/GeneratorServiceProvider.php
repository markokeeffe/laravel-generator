<?php namespace MOK\Generator;

use Illuminate\Support\ServiceProvider;
use MOK\Generator\Commands;
use MOK\Generator\Generators;
use Way\Generators\Cache as Cache2;

class GeneratorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('markokeeffe/generator');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

    $this->registerModelGenerator();
    $this->registerScaffoldGenerator();
    $this->registerViewGenerator();
    $this->registerControllerGenerator();
		$this->registerRepositoryGenerator();
		$this->registerTestGenerator();

    $this->commands(
      'generator.scaffold',
      'generator.model',
      'generator.view',
      'generator.controller',
			'generator.repository',
			'generator.test'
    );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
    //
  }

  /**
   * Register generate:controller
   *
   * @return Commands\ControllerGeneratorCommand
   */
  protected function registerControllerGenerator()
  {
    $this->app['generator.controller'] = $this->app->share(function($app)
      {
        $cache = new Cache($app['files']);
        $generator = new Generators\ControllerGenerator($app['files'], $cache);

        return new Commands\ControllerGeneratorCommand($generator);
      });
  }

	/**
	 * Register generate:test
	 *
	 * @return Commands\TestGeneratorCommand
	 */
	protected function registerTestGenerator()
	{
		$this->app['generator.test'] = $this->app->share(function($app)
			{
				$cache = new Cache($app['files']);
				$generator = new Generators\TestGenerator($app['files'], $cache);

				return new Commands\TestGeneratorCommand($generator);
			});
	}

	/**
   * Register generate:view
   *
   * @return Commands\ViewGeneratorCommand
   */
  protected function registerViewGenerator()
  {
    $this->app['generator.view'] = $this->app->share(function($app)
      {
        $cache = new Cache($app['files']);
        $generator = new Generators\ViewGenerator($app['files'], $cache);

        return new Commands\ViewGeneratorCommand($generator, $cache);
      });
  }

  protected function registerScaffoldGenerator()
  {
    $this->app['generator.scaffold'] = $this->app->share(function($app)
      {
        $cache = new Cache($app['files']);
				$cache2 = new Cache2($app['files']);
				$generator = new Generators\ResourceGenerator($app['files'], $cache);

        return new Commands\ScaffoldGeneratorCommand($generator, $cache, $cache2);
      });
  }


  /**
   * Register generate:model
   *
   * @return Commands\ModelGeneratorCommand
   */
  protected function registerModelGenerator()
  {
    $this->app['generator.model'] = $this->app->share(function($app)
      {
        $cache = new Cache($app['files']);
        $generator = new Generators\ModelGenerator($app['files'], $cache);

        return new Commands\ModelGeneratorCommand($generator);
      });
  }

	/**
	 * Register generate:repository
	 *
	 * @return Commands\RepositoryGeneratorCommand
	 */
	protected function registerRepositoryGenerator()
	{
		$this->app['generator.repository'] = $this->app->share(function($app)
			{
				$cache = new Cache($app['files']);
				$generator = new Generators\RepositoryGenerator($app['files'], $cache);

				return new Commands\RepositoryGeneratorCommand($generator);
			});
	}


}
