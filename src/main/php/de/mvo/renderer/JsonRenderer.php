<?php
namespace de\mvo\renderer;

use de\mvo\renderer\utils\MustacheRenderer;

class JsonRenderer extends AbstractRenderer
{
	private $template;
	private $modelFilename;

	public function __construct($template, $modelFilename)
	{
		$this->template = $template;
		$this->modelFilename = $modelFilename;
	}

	public function render()
	{
		return MustacheRenderer::render($this->template, json_decode(file_get_contents(MODELS_ROOT . "/" . $this->modelFilename . ".json")));
	}
}