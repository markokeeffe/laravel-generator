<?php namespace MOK\Generator;

use Way\Generators;

class Cache extends Generators\Cache {

	/**
	 * Title
	 *
	 * @var string
	 */
	protected $title;


	/**
	 * Save fields to cached file
	 * for future use by other commands
	 *
	 * @param string $fields
	 * @param string $path
	 * @return mixed
	 */
	public function fields($fields, $path = null)
	{
		if (is_null($fields)) return;

		$path = $path ?: __DIR__.'/../tmp-fields.txt';
		$fields = preg_split('/, ?/', $fields);
		$arrayFields = array();

		foreach($fields as $pair)
		{
			list($key, $val) = preg_split('/ ?: ?/', $pair);
			$arrayFields[$key] = $val;
		}

		return $this->file->put($path, json_encode($arrayFields));
	}

	/**
	 * Fetches the cached fields for the resource
	 *
	 * @param string $path
	 * @return array|false
	 */
	public function getFields($path = null)
	{
		$path = $path ?:  __DIR__.'/../tmp-fields.txt';

		// Have we already fetched the fields?
		if (! is_null($this->fields))
		{
			return $this->fields;
		}

		// If not, let's grab it if it exists.
		if (file_exists($path))
		{
			return $this->fields = json_decode($this->file->get($path), true);
		}

		// Well, it doesn't exist. Just return false
		// from now on for this instance.
		$this->fields = false;

		return false;
	}

	/**
	 * Cache the model name
	 *
	 * @param  string $modelName
	 * @param  string $path
	 * @return mixed
	 */
	public function modelName($modelName, $path = null)
	{
		$path = $path ?: __DIR__.'/../tmp-model.txt';

		return $this->file->put($path, $modelName);
	}

	/**
	 * Fetch the model name
	 *
	 * @param  string $path
	 * @return mixed
	 */
	public function getModelName($path = null)
	{
		$path = $path ?: __DIR__.'/../tmp-model.txt';

		// Have we already fetched the model name?
		if (! is_null($this->model))
		{
			return $this->model;
		}

		// If not, let's grab it if it exists.
		if (file_exists($path))
		{
			return $this->model = $this->file->get($path);
		}
	}

	/**
	 * Cache the title
	 *
	 * @param string $title
	 * @param string $path
	 * @return mixed
	 */
	public function title($title, $path = null)
	{
		$path = $path ?: __DIR__.'/../tmp-title.txt';

		return $this->file->put($path, $title);
	}

	/**
	 * Fetch the title
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function getTitle($path = null)
	{
		$path = $path ?: __DIR__.'/../tmp-title.txt';

		// Have we already fetched the title
		if (! is_null($this->title))
		{
			return $this->title;
		}

		// If not, let's grab it if it exists.
		if (file_exists($path))
		{
			return $this->title = $this->file->get($path);
		}
	}

	/**
	 * Delete cached files
	 *
	 * @return void
	 */
	public function destroyAll()
	{
		unlink(__DIR__.'/../tmp-model.txt');
		unlink(__DIR__.'/../tmp-fields.txt');
	}

}
