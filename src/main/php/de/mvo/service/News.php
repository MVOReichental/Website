<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\pictures\YearList;
use de\mvo\TwigRenderer;
use DOMDocument;
use DOMElement;
use Twig_Error;

class News extends AbstractService
{
    const NEWS_FILE = RESOURCES_ROOT . "/news.html";

    /**
     * @return string
     * @throws Twig_Error
     */
    public function get()
    {
        if (file_exists(self::NEWS_FILE)) {
            $newsContent = file_get_contents(self::NEWS_FILE);
        } else {
            $newsContent = null;
        }

        $albums = YearList::load()->getAllAlbums();

        $albums->sortByDate(false);

        return TwigRenderer::render("news", array
        (
            "news" => $newsContent,
            "dates" => DateList::get(3)->publiclyVisible(),
            "albums" => $albums->getVisibleToUser(null)->slice(0, 3),
            "picturesBaseUrl" => "fotogalerie"
        ));
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function getEditor()
    {
        if (file_exists(self::NEWS_FILE)) {
            $content = file_get_contents(self::NEWS_FILE);
        } else {
            $content = null;
        }

        return TwigRenderer::render("admin/news-editor", array
        (
            "content" => $content
        ));
    }

    public function save()
    {
        file_put_contents(RESOURCES_ROOT . "/news.html", $_POST["content"]);
    }

    public function delete()
    {
        if (!file_exists(self::NEWS_FILE)) {
            return;
        }

        unlink(self::NEWS_FILE);
    }
}