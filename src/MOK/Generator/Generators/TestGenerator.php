<?php namespace MOK\Generator\Generators;

use Config;
use Way\Generators\Generators;
use Illuminate\Support\Pluralizer;

class TestGenerator extends Generators\TestGenerator {

	/**
	 * Fetch the compiled template for a test
	 *
	 * @param string $template Path to template
	 * @param string $className
	 * @return string Compiled template
	 */
	protected function getTemplate($template, $className)
	{
		$model = $this->cache->getModelName(); // post
		$models = Pluralizer::plural($model); // posts
		$Models = ucwords($models); // Posts
		$Model = Pluralizer::singular($Models); // Post
		$columns = $this->TestColumns($this->requiredColumns());


		$template = $this->file->get($template);

		foreach(array('columns', 'model', 'models', 'Models', 'Model', 'className') as $var)
		{
			$template = str_replace('{{'.$var.'}}', $$var, $template);
		}

		return $template;
	}

	/**
	 * Get required columns form database
	 *
	 * @return array
	 */
	public function requiredColumns()
	{
		$table = Pluralizer::plural($this->cache->getModelName()); // table name
		//$table = 'comments'; //debug

		$schema = \DB::getDoctrineSchemaManager($table);

		$requiredColumns = array();
		$columns = $schema->listTableColumns($table);

		foreach ($columns as $column) {
			$name = $column->getName();

			//Get the required fields
			if ($column->getNotNull()) {
				$requiredColumns[] = $name;
			}
		}

		// remove unneeded fields (from config)
		foreach (Config::get('generator::removable') as $remove) {
			foreach ($requiredColumns as $key => $column) {
				if ($column == $remove) {
					unset($requiredColumns[$key]);
					break;
				}
			}
		}

		return $requiredColumns;
	}

	/**
	 * Generates the testable columns string
	 *
	 * @param $columns|array
	 *
	 * @return string
	 */
	public function TestColumns($columns)
	{
		$testColumns = '';

		foreach($columns as $column){
			$studlyCase = studly_case($column);

			$testColumns .= <<<EOT

	/**
	 * Test validation for required attribute '$column'
	 */
	public function testIsNotValidWithout$studlyCase()
	{
		\$model = Factory::make('{{Model}}', array('$column' => null));
		\$this->assertNotValid(\$model);
	}

EOT;
		}

		return $testColumns;
	}

}
