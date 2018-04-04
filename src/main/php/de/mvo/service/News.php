<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\pictures\YearList;
use de\mvo\TwigRenderer;
use Twig_Error;

class News extends AbstractService
{
    /**
     * @param bool $allowEdit
     * @return string
     * @throws Twig_Error
     */
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
        file_put_contents(RESOURCES_ROOT . "/news.html", $_POST["content"]);
    }

    public function delete()
    {
        if (!file_exists(RESOURCES_ROOT . "/news.html")) {
            return;
        }

        unlink(RESOURCES_ROOT . "/news.html");
    }
}