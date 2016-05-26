<?php
namespace de\mvo\renderer\utils;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

class MustacheRenderer
{
	public static function render($template, $context)
	{
		$mustache = new Mustache_Engine(array
		(
			"loader" => new Mustache_Loader_FilesystemLoader(VIEWS_ROOT)
		));

		return $mustache->render($template, $context);
	}
}