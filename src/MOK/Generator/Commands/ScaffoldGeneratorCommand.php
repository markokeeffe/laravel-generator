<?php namespace MOK\Generator\Commands;

use Exception;
use Way\Generators\Commands;
use MOK\Generator\Cache;
use Way\Generators\Cache as Cache2;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Input\InputOption;
use MOK\Generator\Generators\ResourceGenerator;

class DatabaseException extends \Exception{}

class ScaffoldGeneratorCommand extends Commands\ResourceGeneratorCommand
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'generate2:scaffold';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate scaffolding for a resource.';
	protected $template;


	/**
	 * Create a new command instance.
	 *
	 * @param ResourceGenerator $generator
	 * @param Cache             $cache
	 * @param Cache2            $cache2
	 */
	public function __construct(ResourceGenerator $generator, Cache $cache, Cache2 $cache2)
	{
		parent::__construct($generator, $cache);

		$this->cache2 = $cache2;
	}

	/**
	 * Execute the console command.
	 *
	 * @throws DatabaseException
	 * @throws \Exception
	 * @return void
	 */
	public function fire()
	{
		// Scaffolding should always begin with the singular
		// form of the now.
		$this->model = Pluralizer::singular($this->argument('name'));

		if(!file_exists(__DIR__ . '/../Generators/templates/'.$this->option('template'))) {
			throw new Exception("Sorry. The template '{$this->option('template')}' does not exist.");
		}

		$this->template = $this->option('template');

		$table = Pluralizer::plural($this->argument('name')); //get table ame

		//$table = 'comments'; //debug

		//get table fields from DB
		$schema = \DB::getDoctrineSchemaManager($table);
		$columns = $schema->listTableColumns($table);

		$fields = array();
		foreach ($columns as $column) {
			$fields[] = $column->getName() . ':' . $column->getType()->getName();
		}

		if (!$fields) {
			throw new DatabaseException('The table has no columns or does not exist.');
		}

		$fields = implode(', ', $fields);

		// We're going to need access to these values
		// within future commands. I'll save them
		// to temporary files to allow for that.
		$this->cache->fields($fields);
		$this->cache->modelName($this->model);

		//also create the same in jeffry's cache
		$this->cache2->fields($fields);
		$this->cache2->modelName($this->model);

		$this->generateModel();
		$this->generateController();
		$this->generateViews();
		//$this->generateMigration();
		//$this->generateSeed();
		$this->generateModelDocs();
		//$this->generateTest(); // Controller test
		$this->generateRepoTest(); //Repository test
		$this->generateRepository();

		$this->generator->updateRoutesFile($this->model);
		$this->info('Updated ' . app_path() . '/routes.php');

		// We're all finished, so we
		// can delete the cache.
		$this->cache->destroyAll();
		$this->cache2->destroyAll(); //destroy Jeffry way cache
	}

	/**
	 * Call generate:views
	 *
	 * @return void
	 */
	protected function generateViews()
	{
		$viewsDir = app_path().'/views';
		$container = $viewsDir . '/' . Pluralizer::plural($this->model);
		$layouts = $viewsDir . '/layouts';
		$views = array('index', 'show', 'create', 'edit', 'scaffold');

		$this->generator->folders(
			array($container)
		);

		// Let's filter through all of our needed views
		// and create each one.
		foreach($views as $view)
		{
			$path = $view === 'scaffold' ? $layouts : $container;
			$this->generateView($view, $path);
		}
	}

	/**
	 * Get the path to the template for a model.
	 *
	 * @return string
	 */
	protected function getModelTemplatePath()
	{
		return __DIR__ . "/../Generators/templates/{$this->template}/scaffold/model.txt";
	}

	/**
	 * Get the path to the template for a controller.
	 *
	 * @return string
	 */
	protected function getControllerTemplatePath()
	{
		return __DIR__ . "/../Generators/templates/{$this->template}/scaffold/controller.txt";
	}


	/**
	 * Get the path to the template for a controller.
	 *
	 * @return string
	 */
	protected function getTestTemplatePath()
	{
		return __DIR__ . "/../Generators/templates/{$this->template}/scaffold/controller-test.txt";
	}

	/**
	 * Get the path to the template for a repository test
	 *
	 * @return string
	 */
	protected function getRepoTestTemplatePath()
	{
		return __DIR__ . "/../Generators/templates/{$this->template}/scaffold/repository/test.txt";
	}


	/**
	 * Get the path to the template for a view.
	 *
	 * @param string $view
	 *
	 * @return string
	 */
	protected function getViewTemplatePath($view = 'view')
	{
		return __DIR__ . "/../Generators/templates/{$this->template}/scaffold/views/{$view}.txt";
	}


	/**
	 * Call generate:model
	 *
	 * @return void
	 */
	protected function generateModel()
	{
		// For now, this is just the regular model template
		$this->call(
			'generate2:model',
			array(
				'name'       => $this->model,
				'--template' => $this->getModelTemplatePath()
			)
		);
	}

	/**
	 * Generate a view
	 *
	 * @param  string $view
	 * @param  string $path
	 *
	 * @return void
	 */
	protected function generateView($view, $path)
	{
		$this->call(
			'generate2:view',
			array(
				'name'       => $view,
				'--path'     => $path,
				'--template' => $this->getViewTemplatePath($view)
			)
		);
	}

	/**
	 * Call generate:controller
	 *
	 * @return void
	 */
	protected function generateController()
	{
		$name = Pluralizer::plural($this->model);

		$this->call(
			'generate2:controller',
			array(
				'name'       => "{$name}Controller",
				'--template' => $this->getControllerTemplatePath()
			)
		);
	}

	/**
	 * Call generate2:repository
	 *
	 * @return void
	 */
	protected function generateRepository()
	{
		$name = ucfirst(Pluralizer::singular($this->model));

		$this->call(
			'generate2:repository',
			array(
				'name'   => "{$name}",
			)
		);
	}

	/**
	 * Call ide-helper:models to generate model PhpDoc Block
	 *
	 * @return void
	 */
	protected function generateModelDocs()
	{
		$name = ucfirst(Pluralizer::singular($this->model));

		$this->call(
			'ide-helper:models',
			array(
				'model'   => "{$name}",
				'--write' => true
			)
		);
	}

	/**
	 * Call generate:test
	 *
	 * @return void
	 */
	protected function generateTest()
	{
		if ( ! file_exists(app_path() . '/tests/controllers'))
		{
			mkdir(app_path() . '/tests/controllers');
		}

		$this->call(
			'generate:test',
			array(
				'name' => Pluralizer::plural(strtolower($this->model)) . 'Test',
				'--template' => $this->getTestTemplatePath(),
				'--path' => app_path() . '/tests/controllers'
			)
		);
	}

	/**
	 * Call generate2:test
	 *
	 * @return void
	 */
	protected function generateRepoTest()
	{
		if ( ! file_exists(app_path() . '/tests/repos'))
		{
			mkdir(app_path() . '/tests/repos');
		}

		$this->call(
			'generate2:test',
			array(
				'name' => 'Ardent'.Pluralizer::plural(ucfirst($this->model)) . 'Test',
				'--template' => $this->getRepoTestTemplatePath(),
				'--path' => app_path() . '/tests/repos'
			)
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations folder', app_path() . '/database/migrations'),
			array('fields', null, InputOption::VALUE_OPTIONAL, 'Table fields', null),
			array('template', 't', InputOption::VALUE_OPTIONAL, 'Template for generation', 'default')
		);
	}

}
