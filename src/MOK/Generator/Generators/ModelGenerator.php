<?php namespace MOK\Generator\Generators;

use Way\Generators\Generators;
use Illuminate\Support\Pluralizer;
use Config;

class ModelGenerator extends Generators\ModelGenerator
{

  /**
   * Get template for a scaffold
   *
   */
  protected function getScaffoldedModel($className)
  {
    if (!$fields = $this->cache->getFields()) {
      return str_replace('{{rules}}', '', $this->template);
    }

    $fillable = array();
    $table = Pluralizer::plural($this->cache->getModelName()); // table name
		//$table = 'comments'; //debug

		$schema = \DB::getDoctrineSchemaManager($table);

    $tableRules = array();
    $columns = $schema->listTableColumns($table);

    //Table rule generation
    foreach ($columns as $column) {
      $type = $column->getType();
      $name = $column->getName();
      $fillable[] = $name; //collect columns

      if ($type == 'String') {
        if ($len = $column->getLength()) {
          $tableRules[$name][] = 'max:' . $len;
        }
      } elseif ($type == 'Integer') {
        $tableRules[$name][] = 'integer';
        if ($column->getUnsigned()) {
          $tableRules[$name][] = 'min:0';
        }
      }
      if ($column->getNotNull()) {
        $tableRules[$name][] = 'required';
      }
    }

    $indexes = $schema->listTableIndexes($table);

    //Unique Indexes
    foreach ($indexes as $index) {
      if ($index->isUnique()) {
        foreach ($index->getColumns() as $column) {
          $tableRules[$column][] = 'unique:' . $table . ',' . $column;
        }
      }
    }

    // remove rules
    foreach (Config::get('generator::removable') as $remove) {
      if (isset($tableRules[$remove])) {
        $tableRules[$remove] = array();
      }

      //also remove non editable fields from fillable array
      foreach ($fillable as $key => $fill) {
        if ($fill == $remove) {
          unset($fillable[$key]);
          break;
        }
      }
    }

    //Generate rules strings
    $rulesStr = array();
    foreach ($tableRules as $column => $columnRule) {
      $rule = implode('|', $columnRule);
      $rulesStr[] = "'$column' => '$rule'";
    }

		//Check if the table uses Eloquent timestamps
		$timestamp = 'false';
		if (isset($tableRules['created_at']) AND isset($tableRules['updated_at'])) {
			$timestamp = 'true';
		}

		$this->template = str_replace('{{timestamp}}', $timestamp, $this->template);
		$this->template = str_replace('{{fillable}}', "'" . implode("', '", $fillable) . "'", $this->template);
    $this->template = str_replace('{{rules}}', PHP_EOL . "\t\t" . implode(',' . PHP_EOL . "\t\t", $rulesStr) . PHP_EOL . "\t", $this->template);

    return $this->template;
  }
}
