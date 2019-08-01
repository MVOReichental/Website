<?php
namespace de\mvo\service;

use de\mvo\model\pictures\Album;
use de\mvo\model\pictures\YearList;
use de\mvo\model\users\User;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;
use Twig_Error;

class Pictures extends AbstractService
{
    /**
     * @param bool $internal
     * @return string
     * @throws Twig_Error
     */
    public function getYears($internal = false)
    {
        $years = YearList::load()->getYearsVisibleToUser($internal ? User::getCurrent() : null);
        if (!$years->count()) {
            http_response_code(404);
        }

        return TwigRenderer::render("pictures/years-overview", array
        (
            "picturesBaseUrl" => $internal ? "internal/pictures" : "fotogalerie",
            "years" => $years
        ));
    }

    /**
     * @param bool $internal
     * @return string
     * @throws Twig_Error
     */
    public function getAlbums($internal = false)
    {
        $albums = null;

        $year = YearList::load()->getYear($this->params->year);
        if ($year !== null) {
            $albums = $year->albums->getVisibleToUser($internal ? User::getCurrent() : null);

            if ($albums != null and $albums->count()) {
                $albums->sortByDate(false);
            } else {
                http_response_code(404);
            }
        } else {
            http_response_code(404);
        }

        return TwigRenderer::render("pictures/albums-overview", array
        (
            "picturesBaseUrl" => $internal ? "internal/pictures" : "fotogalerie",
            "year" => $this->params->year,
            "albums" => $albums
        ));
    }

    /**
     * @param bool $internal
     * @return string
     * @throws Twig_Error
     */
    public function getAlbum($internal = false)
    {
        $album = null;

        $year = YearList::load()->getYear($this->params->year);
        if ($year !== null) {
            $albums = $year->albums;
            if ($albums !== null) {
                $album = $albums->getAlbum($this->params->album);
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

        return TwigRenderer::render("pictures/album", array
        (
            "picturesBaseUrl" => $internal ? "internal/pictures" : "fotogalerie",
            "year" => $this->params->year,
            "title" => $album === null ? $this->params->album : $album->title,
            "album" => $album
        ));
    }
}