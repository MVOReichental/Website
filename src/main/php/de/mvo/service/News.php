<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\pictures\YearList;
use de\mvo\TwigRenderer;

class News extends AbstractService
{
    public function get()
    {
        $newsFile = RESOURCES_ROOT . "/news.html";
        if (file_exists($newsFile)) {
            $newsContent = file_get_contents($newsFile);
        } else {
            $newsContent = null;
        }

        $albums = YearList::load()->getAllAlbums();

        $albums->sortByDate(false);

        return TwigRenderer::render("news", array
        (
            "news" => $newsContent,
            "dates" => DateList::get(null, 3),
            "albums" => $albums->getVisibleToUser(null)->slice(0, 3),
            "picturesBaseUrl" => "fotogalerie"
        ));
    }
}