<?php namespace MOK\Generator\Generators;

use Way\Generators\Generators;
use Illuminate\Support\Pluralizer;

class ControllerGenerator extends Generators\ControllerGenerator {

	/**
	 * Compile template and generate
	 *
	 * @param  string $path
	 * @param  string $template Path to template
	 * @return boolean
	 */
	public function make($path, $template)
	{
		$this->name = basename($path, '.php');
		$this->templatePath = $template;
		$this->path = $this->getPath($path);
		$template = $this->getTemplate($template, $this->name);



		if (! $this->file->exists($this->path))
		{
			return $this->file->put($this->path, $template) !== false;
		}

		return false;
	}

  /**
   * Get template for a scaffold
   *
   * @param string $template
   * @param        $className
   *
   * @return string
   */
  protected function getScaffoldedController($template, $className)
  {
    $model = $this->cache->getModelName(); // post
    $models = Pluralizer::plural($model); // posts
    $Models = ucwords($models); // Posts
    $Model = Pluralizer::singular($Models); // Post

		//get columns
    $columns = array();
    foreach($this->cache->getFields() as $column_title => $type) {
      $columns[] = $models.'.'.$column_title;
    }

		//check if the _tableOperations.php file exists. Create if not.
		if(!$this->file->exists(app_path('views/partials/_tableOperations.php'))){
			mkdir(app_path('views/partials'));

			$this->file->copy(
				dirname($this->templatePath).'/_tableOperations.txt',
				app_path('views/partials/_tableOperations.php')
			);
		}

    $this->template = str_replace('{{columns}}', "'" . implode("', '", $columns) . "'", $this->template);

    foreach(array('model', 'models', 'Models', 'Model', 'className') as $var)
    {
      $this->template = str_replace('{{'.$var.'}}', $$var, $this->template);
    }

    return $this->template;
  }

}
