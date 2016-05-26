<?php
namespace de\mvo\renderer;

use de\mvo\model\pictures\Album;
use de\mvo\model\pictures\AlbumList;
use de\mvo\model\pictures\YearList;
use de\mvo\renderer\utils\MustacheRenderer;

class PicturesRenderer extends AbstractRenderer
{
	public function render()
	{
		if (isset($this->params->year))
		{
			if (isset($this->params->album))
			{
				return MustacheRenderer::render("pictures/album", new Album);
			}
			else
			{
				return MustacheRenderer::render("pictures/album-list", array
				(
					"year" => $this->params->year,
					"albums" => new AlbumList($this->params->year
				)));
			}
		}
		else
		{
			return MustacheRenderer::render("pictures/year-list", array
			(
				"years" => new YearList
			));
		}
	}
}