<?php
namespace de\mvo\service;

use de\mvo\model\pictures\YearList;
use de\mvo\model\users\User;
use de\mvo\TwigRenderer;

class Pictures extends AbstractService
{
    public function getYears($intern = false)
    {
        $years = YearList::load();
        if (!$years->count()) {
            http_response_code(404);
        }

        return TwigRenderer::render("pictures/years-overview", array
        (
            "picturesBaseUrl" => $intern ? "intern/pictures" : "fotogalerie",
            "years" => $years
        ));
    }

    public function getAlbums($intern = false)
    {
        $albums = null;

        $year = YearList::load()->getYear($this->params->year);
        if ($year !== null) {
            $albums = $year->albums->getVisibleToUser($intern ? User::getCurrent() : null);

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
            "picturesBaseUrl" => $intern ? "intern/pictures" : "fotogalerie",
            "year" => $this->params->year,
            "albums" => $albums
        ));
    }

    public function getAlbum($intern = false)
    {
        $album = null;

        $year = YearList::load()->getYear($this->params->year);
        if ($year !== null) {
            $albums = $year->albums;
            if ($albums !== null) {
                $album = $albums->getAlbum($this->params->album);
                if ($album === null) {
                    http_response_code(404);
                } elseif (!$album->isVisibleToUser($intern ? User::getCurrent() : null)) {
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
            "picturesBaseUrl" => $intern ? "intern/pictures" : "fotogalerie",
            "year" => $this->params->year,
            "title" => $album === null ? $this->params->album : $album->title,
            "album" => $album
        ));
    }
}