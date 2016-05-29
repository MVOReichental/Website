<?php
namespace de\mvo\service;

use de\mvo\MustacheRenderer;

class JsonView extends AbstractService
{
	public function get($template, $modelFilename)
	{
		return MustacheRenderer::render($template, json_decode(file_get_contents(MODELS_ROOT . "/" . $modelFilename . ".json")));
	}
}