<?php
namespace de\mvo\renderer;

use de\mvo\model\musicians\MusiciansList;
use de\mvo\renderer\utils\MustacheRenderer;

class MusiciansRenderer extends AbstractRenderer
{
	public function render()
	{
		return MustacheRenderer::render("verein/musicians", array
		(
			"groups" => new MusiciansList
		));
	}
}