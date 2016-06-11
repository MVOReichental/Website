<?php
namespace de\mvo\service;

use de\mvo\TwigRenderer;

class JsonView extends AbstractService
{
	public function get($template, $modelFilename)
	{
		return TwigRenderer::render($template, array
		(
			"json" => json_decode(file_get_contents(MODELS_ROOT . "/" . $modelFilename . ".json"))
		));
	}
}