<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\pictures\YearList;
use de\mvo\TwigRenderer;

class News extends AbstractService
{
    public function get($allowEdit = false)
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
            "news" => array
            (
                "allowEdit" => $allowEdit,
                "content" => $newsContent
            ),
            "dates" => DateList::get(3)->publiclyVisible(),
            "albums" => $albums->getVisibleToUser(null)->slice(0, 3),
            "picturesBaseUrl" => "fotogalerie"
        ));
    }

    public function save()
    {
        file_put_contents(RESOURCES_ROOT . "/news.html", $_POST["editabledata"]);
    }
}