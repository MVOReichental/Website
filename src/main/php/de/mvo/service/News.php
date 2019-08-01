<?php
namespace de\mvo\service;

use de\mvo\model\date\DateList;
use de\mvo\model\pictures\YearList;
use de\mvo\TwigRenderer;
use HTMLPurifier;
use Twig_Error;

class News extends AbstractService
{
    const NEWS_FILE = DATA_ROOT . "/news.html";

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
            "dates" => DateList::getPublic(3),
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
        $htmlPurifier = new HTMLPurifier;

        file_put_contents(self::NEWS_FILE, $htmlPurifier->purify($_POST["content"]));
    }

    public function delete()
    {
        if (!file_exists(self::NEWS_FILE)) {
            return;
        }

        unlink(self::NEWS_FILE);
    }
}