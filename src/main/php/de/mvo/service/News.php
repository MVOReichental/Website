<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\pictures\AlbumList;
use de\mvo\MustacheRenderer;

class News extends AbstractService
{
	public function get()
	{
		return MustacheRenderer::render("news", array
		(
			"dates" => new DateList(3),
			"albums" => AlbumList::getLatest(3)
		));
	}
}