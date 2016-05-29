<?php
namespace de\mvo\service;

use de\mvo\model\pictures\Album;
use de\mvo\model\pictures\AlbumList;
use de\mvo\model\pictures\YearList;
use de\mvo\MustacheRenderer;

class Pictures extends AbstractService
{
	public function getYears()
	{
		return MustacheRenderer::render("pictures/years-overview", array
		(
			"years" => new YearList
		));
	}

	public function getAlbums()
	{
		return MustacheRenderer::render("pictures/albums-overview", array
		(
			"year" => $this->params->year,
			"albums" => AlbumList::getForYear($this->params->year)
		));
	}

	public function getAlbum()
	{
		$album = Album::getByYearAndName($this->params->year, $this->params->album);
		if ($album === null)
		{
			http_response_code(404);
		}

		return MustacheRenderer::render("pictures/album", $album);
	}
}