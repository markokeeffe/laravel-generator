<?php namespace MOK\Generator\Commands;

use Way\Generators\Commands;
use Illuminate\Support\Pluralizer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use MOK\Generator\Generators\RepositoryGenerator;

class RepositoryGeneratorCommand extends Commands\BaseGeneratorCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'generate2:repository';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate a new model repository.';

	/**
	 * Model generator instance.
	 *
	 * @mixed
	 */
	protected $generator;

	/**
	 * Create a new command instance.
	 *
	 * @param RepositoryGenerator $generator
	 */
	public function __construct(RepositoryGenerator $generator)
	{
		parent::__construct();

		$this->generator = $generator;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$template = $this->option('template');
		$repo_path = $this->option('path').'/Repo/';
		$model = ucfirst(Pluralizer::singular($this->argument('name')));
		$full_repo_path = $repo_path.$model.'/';
		$repoName = basename($this->option('path'));

		$this->generator->repoName = $repoName;

		//Folder generation
		$folders = array(
			$this->option('path'),
			$repo_path,
			$full_repo_path
		);
		$this->generator->folders($folders);

		//Model repository files
		$path = $full_repo_path.'Ardent'.$model.'.php';
		$this->printResult($this->generator->make($path, $template.'ardent.txt'),$path);

		$path = $full_repo_path.$model.'Interface.php';
		$this->printResult($this->generator->make($path, $template.'interface.txt'),$path);

		//Update service provider
		$path = $repo_path.'RepoServiceProvider.php';
		$this->generator->updateProvider($path, $template.'partialServiceProvider.txt');
		$this->info('Updated '.$path);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'Name of the model to generate.'),
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
			array('path', null, InputOption::VALUE_OPTIONAL, 'Path to the repository directory.', app_path() . '/MOK'),
			array('template', null, InputOption::VALUE_OPTIONAL, 'Path to template folder.', __DIR__.'/../Generators/templates/default/scaffold/repository/')
		);
	}

}
