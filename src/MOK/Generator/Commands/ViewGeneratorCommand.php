<?php namespace MOK\Generator\Commands;

use Way\Generators\Commands;
use Symfony\Component\Console\Input\InputOption;
use MOK\Generator\Cache;
use MOK\Generator\Generators\ViewGenerator;


class ViewGeneratorCommand extends Commands\ViewGeneratorCommand {


  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'generate2:view';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate a new view.';

	protected $cache ;

	public function __construct(ViewGenerator $generator, Cache $cache)
	{
		parent::__construct($generator);

	  $this->cache = $cache;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->cache->title($this->option('title'));

		$path = $this->getPath();
		$template = $this->option('template');

		$this->printResult($this->generator->make($path, $template), $path);
		unlink(__DIR__.'/../../tmp-title.txt'); //remove cache
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('path', null, InputOption::VALUE_OPTIONAL, 'Path to views directory.', app_path() . '/views'),
			array('template', 't', InputOption::VALUE_OPTIONAL, 'Path to template.',  __DIR__.'/../Generators/templates/default/view.txt'),
			array('title', null, InputOption::VALUE_OPTIONAL, 'Title for generated page.', 'Title of your page'),
		);
	}

}
