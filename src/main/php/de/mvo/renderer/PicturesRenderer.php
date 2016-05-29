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
				$album = Album::getByYearAndName($this->params->year, $this->params->album);
				if ($album === null)
				{
					http_response_code(404);
				}

				return MustacheRenderer::render("pictures/album", $album);
			}
			else
			{
				return MustacheRenderer::render("pictures/albums-overview", array
				(
					"year" => $this->params->year,
					"albums" => AlbumList::getForYear($this->params->year)
				));
			}
		}
		else
		{
			return MustacheRenderer::render("pictures/years-overview", array
			(
				"years" => new YearList
			));
		}
	}
}