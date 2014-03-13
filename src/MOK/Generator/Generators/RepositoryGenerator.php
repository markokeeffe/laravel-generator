<?php namespace MOK\Generator\Generators;

use Way\Generators\Generators;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Facades\File;

class RepositoryGenerator extends Generators\Generator
{

	/**
	 * Repository base name
	 *
	 * @var
	 */
	public $repoName;

	/**
	 * Fetch the compiled template for a controller
	 *
	 * @param string $template Path to template
	 * @param string $name
	 *
	 * @return string Compiled template
	 */
	protected function getTemplate($template, $name = null)
	{
		$this->template = $this->file->get($template);

		$model = $this->cache->getModelName(); // post
		$models = Pluralizer::plural($model); // posts
		$Models = ucwords($models); // Posts
		$Model = Pluralizer::singular($Models); // Post
		$Repository = $this->repoName;

		foreach (array('model', 'models', 'Models', 'Model', 'Repository') as $var) {
			$this->template = str_replace('{{' . $var . '}}', $$var, $this->template);
		}

		return $this->template;
	}

	/**
	 * Compile template and generate
	 *
	 * @param  string $path
	 * @param  string $template Path to template
	 *
	 * @return boolean
	 */
	public function make($path, $template)
	{
		$this->name = basename($path);
		$this->path = $this->getPath($path);
		$template = $this->getTemplate($template);

		if (!$this->file->exists($this->path)) {
			return $this->file->put($this->path, $template) !== false;
		}

		return false;
	}

	public function updateProvider($path, $templatePath)
	{
		$Model = ucfirst(Pluralizer::singular($this->cache->getModelName()));
		$repository = $this->repoName;
		$template = $this->getTemplate($templatePath);

		//check if the file exists
		if (!$this->file->exists($path)) {

			//file does not exist create from new template;
			$templatePath = str_replace('partialServiceProvider.txt', 'fullServiceProvider.txt', $templatePath);
			$finalProvider = $this->getTemplate($templatePath);
		} else {

			//file exists - update existing template
			$currentProvider = $this->file->get($path);
			$updatedProvider = $this->templateAddServiceProvider($currentProvider, '}', $template);
			$finalProvider = $this->templateAddModel(
				$updatedProvider,
				"\n\n",
				"use {$Model}; \n"
			);

		}

		return $this->file->put($path , $finalProvider) !== false;
	}

	/**
	 * Create any number of folders
	 *
	 * @param  string|array $folders
	 *
	 * @return void
	 */
	public function folders($folders)
	{
		foreach ((array)$folders as $folderPath) {
			if (!$this->file->exists($folderPath)) {
				$this->file->makeDirectory($folderPath);
			}
		}
	}

	/**
	 * Replace the last second occurrence of character in string
	 *
	 * @param $haystack
	 * @param $needle
	 * @param $replaceWith
	 *
	 * @return bool|mixed
	 */
	protected function templateAddServiceProvider($haystack, $needle, $replaceWith)
	{
		//Replacing the last character
		$last = strrpos($haystack, $needle);
		if ($last !== false) {
			//finding the 2nd last occurrence
			$next_to_last = strrpos($haystack, $needle, $last - strlen($haystack) - 1);

			return substr_replace($haystack, $replaceWith, $next_to_last, strlen($needle));

		}
		return false;
	}

	/**
	 * Adds string after the first occurrence
	 *
	 * @param $haystack
	 * @param $needle
	 * @param $add
	 *
	 * @return bool|mixed
	 */
	protected function templateAddModel($haystack, $needle, $add)
	{
		$first = strpos($haystack, $needle);

		if ($first !== false) {
			return substr_replace($haystack, $needle . $add, $first, strlen($needle));
		}

		return false;
	}
}

