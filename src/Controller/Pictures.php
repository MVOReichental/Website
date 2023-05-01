<?php
namespace App\Controller;

use App\Entity\pictures\YearList;
use App\Entity\users\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(name: "Pictures::", methods: ["GET"])]
class Pictures extends AbstractController
{
    #[Route("/fotogalerie", name: "getYears")]
    #[Template("pictures/years-overview.twig")]
    public function getYears(): array
    {
        return $this->getDataForYears(false);
    }

    #[Route("/internal/pictures", name: "getInternalYears")]
    #[Template("pictures/years-overview.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function getInternalYears(): array
    {
        return $this->getDataForYears(true);
    }

    #[Route("/fotogalerie/{year}", name: "getAlbums", requirements: ["year" => "\d{4}"])]
    #[Template("pictures/albums-overview.twig")]
    public function getAlbums(int $year): array
    {
        return $this->getDataForYear($year, false);
    }

    #[Route("/internal/pictures/{year}", name: "getInternalAlbums", requirements: ["year" => "\d{4}"])]
    #[Template("pictures/albums-overview.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function getInternalAlbums(int $year): array
    {
        return $this->getDataForYear($year, true);
    }

    #[Route("/fotogalerie/{year}/{albumName}", name: "getAlbum", requirements: ["year" => "\d{4}"])]
    #[Template("pictures/album.twig")]
    public function getAlbum(int $year, string $albumName): array
    {
        return $this->getDataForAlbumByYearAndName($year, $albumName, false);
    }

    #[Route("/internal/pictures/{year}/{albumName}", name: "getInternalAlbum", requirements: ["year" => "\d{4}"])]
    #[Template("pictures/album.twig")]
    #[IsGranted("IS_AUTHENTICATED")]
    public function getInternalAlbum(int $year, string $albumName): array
    {
        return $this->getDataForAlbumByYearAndName($year, $albumName, true);
    }

    private function getDataForYears(bool $internal): array
    {
        $years = YearList::load()->getYearsVisibleToUser($internal ? User::getCurrent() : null);
        if (!$years->count()) {
            throw new NotFoundHttpException;
        }

        return [
            "picturesBaseUrl" => $internal ? "internal/pictures" : "fotogalerie",
            "years" => $years
        ];
    }

    private function getDataForYear(int $year, bool $internal): array
    {
        $albums = null;

        $yearList = YearList::load()->getYear($year);
        if ($yearList !== null) {
            $albums = $yearList->albums->getVisibleToUser($internal ? User::getCurrent() : null);

            if ($albums != null and $albums->count()) {
                $albums->sortByDate(false);
            } else {
                http_response_code(404);
            }
        } else {
            http_response_code(404);
        }

        return [
            "picturesBaseUrl" => $internal ? "internal/pictures" : "fotogalerie",
            "year" => $year,
            "albums" => $albums
        ];
    }

    private function getDataForAlbumByYearAndName(int $year, string $albumName, bool $internal): array
    {
        $album = null;

        $yearList = YearList::load()->getYear($year);
        if ($yearList !== null) {
            $albums = $yearList->albums;
            if ($albums !== null) {
                $album = $albums->getAlbum($albumName);
                if ($album === null) {
                    http_response_code(404);
                } elseif (!$album->isVisibleToUser($internal ? User::getCurrent() : null)) {
                    $album = null;
                    http_response_code(404);
                }
            } else {
                http_response_code(404);
            }
        } else {
            http_response_code(404);
        }

        return [
            "picturesBaseUrl" => $internal ? "internal/pictures" : "fotogalerie",
            "year" => $year,
            "title" => $album === null ? $albumName : $album->title,
            "album" => $album
        ];
    }
}