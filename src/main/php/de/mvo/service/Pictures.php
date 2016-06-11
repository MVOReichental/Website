<?php
namespace de\mvo\service;

use de\mvo\model\pictures\Album;
use de\mvo\model\pictures\AlbumList;
use de\mvo\model\pictures\YearList;
use de\mvo\TwigRenderer;

class Pictures extends AbstractService
{
	public function getYears()
	{
		return TwigRenderer::render("pictures/years-overview", array
		(
			"years" => new YearList
		));
	}

	public function getAlbums()
	{
		return TwigRenderer::render("pictures/albums-overview", array
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

		return TwigRenderer::render("pictures/album", array
		(
			"year" => $this->params->year,
			"title" => $album === null ? $this->params->album : $album->title,
			"album" => $album
		));
	}
}