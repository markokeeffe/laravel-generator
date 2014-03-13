<?php namespace MOK\Generator\Commands;

use Way\Generators\Commands;
use Symfony\Component\Console\Input\InputOption;
use MOK\Generator\Generators\TestGenerator;

class TestGeneratorCommand extends Commands\TestGeneratorCommand {


	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'generate2:test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate a PHPUnit test class.';

	protected $generator;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(TestGenerator $generator)
	{
		parent::__construct($generator);

		$this->generator = $generator;
	}

	/**
	 * Get the console command options.
	 *d
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('path', null, InputOption::VALUE_OPTIONAL, 'Path to tests directory.', app_path() . '/tests'),
			array('template', null, InputOption::VALUE_OPTIONAL, 'Path to template.', __DIR__.'/../Generators/templates/default/scaffold/repository/test.txt'),
		);
	}
}
