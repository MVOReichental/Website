<?php
namespace de\mvo\service;

use de\mvo\TwigRenderer;

class StaticView extends AbstractService
{
	public function get($name)
	{
		return TwigRenderer::render($name);
	}
}