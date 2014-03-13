<?php namespace MOK\Generator\Generators;

use Way\Generators\Generators;
use MOK\Generator\Cache;
use Illuminate\Filesystem\Filesystem as File;
use Config;

class ViewGenerator extends Generators\ViewGenerator {

	/**
	 * Constructor
	 *
	 * @param File  $file
	 * @param Cache $cache
	 */
	public function __construct(File $file, Cache $cache)
	{
		$this->file = $file;
		$this->cache = $cache;
	}

	/**
	 * Fetch the compiled template for a view
	 *
	 * @param string $template Path to template
	 * @param string $name
	 * @return string Compiled template
	 */
	protected function getTemplate($template, $name)
	{
		$this->template = $this->file->get($template);

		if ($this->needsScaffolding($template))
		{
			return $this->getScaffoldedTemplate($name);
		}

		$this->template = str_replace('{{title}}', $this->cache->getTitle(), $this->template);

		return $this->template;
	}


  /**
   * Add Laravel methods, as string,
   * for the fields
   *
   * @return string
   */
  public function makeFormElements()
  {
    $formMethods = array();

    // make rules only for fillable fields
		$fields = $this->cache->getFields();
		foreach (Config::get('generator::removable') as $remove) {
      if (isset($fields[$remove])) {
        unset($fields[$remove]);
      }
    }

    foreach($fields as $name => $type)
    {
      $formalName = ucwords(str_replace('_', ' ', $name));

      // TODO: add remaining types
      switch($type)
      {
        case 'integer':
          $element = "{{ Former::text('$name')->label('$formalName') }}";
          break;

        case 'text':
          $element = "{{ Former::textarea('$name')->label('$formalName') }}";
          break;

        case 'boolean':
          $element = "{{ Former::checkbox('$name')->label('$formalName') }}";
          break;

        default:
          $element = "{{ Former::text('$name')->label('$formalName') }}";
          break;
      }

      // Now that we have the correct $element,
      // We can build up the HTML fragment
      $frag = <<<EOT
    $element
EOT;

      $formMethods[] = $frag;
    }

    return implode(PHP_EOL, $formMethods);
  }

	/**
	 * Create the table rows
	 *
	 * @param  string $model
	 * @return Array
	 */
	protected function makeTableRows($model)
	{
		$models = Pluralizer::plural($model); // posts

		$fields = $this->cache->getFields();

		// First, we build the table headings
		$headings = array_map(function($field) {
				return '<th>' . ucwords($field) . '</th>';
			}, array_keys($fields));

		// And then the rows, themselves
		$fields = array_map(function($field) use ($model) {
				return "<td>{{{ \$$model->$field }}}</td>";
			}, array_keys($fields));

		// Now, we'll add the edit and delete buttons.
		$editAndDelete = <<<EOT
					<td>
					{{ link_to_route('{$models}.edit', 'Edit', array(\${$model}->id), array('class' => 'btn btn-info pull-left')) }}

					{{ Form::open(array('method' => 'DELETE', 'route' => array('{$models}.destroy', \${$model}->id))) }}
						{{ Form::submit('Delete', array('class' => 'btn btn-danger')) }}
					{{ Form::close() }}
					</td>
EOT;

		return array($headings, $fields, $editAndDelete);
	}
}
