<?php
namespace de\mvo\renderer;

use de\mvo\model\date\DateList;
use de\mvo\model\pictures\LatestAlbumsList;
use de\mvo\renderer\utils\MustacheRenderer;

class NewsRenderer extends AbstractRenderer
{
	public function render()
	{
		return MustacheRenderer::render("news", array
		(
			"dates" => new DateList(3),
			"albums" => new LatestAlbumsList(3)
		));
	}
}