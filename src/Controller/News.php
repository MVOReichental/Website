<?php
namespace App\Controller;

use App\Repository\DateRepository;
use HTMLPurifier;
use App\Date;
use App\Entity\pictures\YearList;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(name: "News::", methods: ["GET"])]
class News extends AbstractController
{
    const NEWS_FILE = DATA_ROOT . "/news.html";
    const INTERNAL_NEWS_FILE = DATA_ROOT . "/news-internal.html";

    #[Route("/", name: "get")]
    #[Template("news.twig")]
    public function get(DateRepository $dateRepository): array
    {
        if (file_exists(self::NEWS_FILE)) {
            $newsContent = file_get_contents(self::NEWS_FILE);
        } else {
            $newsContent = null;
        }

        $albums = YearList::load()->getAllAlbums();

        $albums->sortByDate(false);

        return [
            "news" => $newsContent,
            "dates" => $dateRepository->get(true, 3),
            "albums" => $albums->getVisibleToUser()->slice(0, 3),
            "picturesBaseUrl" => "fotogalerie"
        ];
    }

    #[Route("/internal/admin/newseditor", name: "getEditor")]
    #[Template("admin/news-editor.twig")]
    #[IsGranted("ROLE_NEWS_EDITOR")]
    public function getEditor(): array
    {
        if (file_exists(self::NEWS_FILE)) {
            $content = file_get_contents(self::NEWS_FILE);
        } else {
            $content = null;
        }

        return [
            "content" => $content
        ];
    }

    #[Route("/internal/news", name: "getInternal")]
    #[Template("news-internal.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function getInternal(): array
    {
        if (file_exists(self::INTERNAL_NEWS_FILE)) {
            $content = file_get_contents(self::INTERNAL_NEWS_FILE);
            $date = new Date;
            $date->setTimestamp(filemtime(self::INTERNAL_NEWS_FILE));
        } else {
            $content = null;
            $date = null;
        }

        return [
            "content" => $content,
            "date" => $date
        ];
    }

    #[Route("/internal/newseditor", name: "getInternalEditor")]
    #[Template("internal-news-editor.twig")]
    #[IsGranted("ROLE_NEWS_EDITOR")]
    public function getInternalEditor(): array
    {
        if (file_exists(self::INTERNAL_NEWS_FILE)) {
            $content = file_get_contents(self::INTERNAL_NEWS_FILE);
        } else {
            $content = null;
        }

        return [
            "content" => $content
        ];
    }

    #[Route("/internal/newseditor/content.html", name: "save")]
    #[IsGranted("ROLE_NEWS_EDITOR")]
    public function save(string $filename): void
    {
        $htmlPurifier = new HTMLPurifier;

        file_put_contents($filename, $htmlPurifier->purify($_POST["content"]));
    }

    #[Route("/internal/newseditor/content.html", name: "delete")]
    #[IsGranted("ROLE_NEWS_EDITOR")]
    public function delete(string $filename): void
    {
        if (!file_exists($filename)) {
            return;
        }

        unlink($filename);
    }
}