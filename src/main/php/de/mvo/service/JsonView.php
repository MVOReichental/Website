<?php
namespace de\mvo\service;

use de\mvo\TwigRenderer;

class JsonView extends AbstractService
{
	public function get($template, $modelFilename)
	{
		$json = json_decode(file_get_contents(MODELS_ROOT . "/" . $modelFilename . ".json"));

		if (is_array($json))
		{
			$context = array
			(
				"json" => $json
			);
		}
		else
		{
			$context = (array) $json;
		}

		return TwigRenderer::render($template, $context);
	}
}