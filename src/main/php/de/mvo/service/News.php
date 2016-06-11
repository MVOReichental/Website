<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\pictures\AlbumList;
use de\mvo\TwigRenderer;

class News extends AbstractService
{
	public function get()
	{
		$newsFile = RESOURCES_ROOT . "/news.html";
		if (file_exists($newsFile))
		{
			$newsContent = file_get_contents($newsFile);
		}
		else
		{
			$newsContent = null;
		}

		return TwigRenderer::render("news", array
		(
			"news" => $newsContent,
			"dates" => new DateList(null, 3),
			"albums" => AlbumList::getLatest(3)
		));
	}
}