<?php
namespace de\mvo\service;

use de\mvo\model\videos\VideoList;
use de\mvo\TwigRenderer;
use Twig\Error\Error;

class Videos extends AbstractService
{
    /**
     * @return string
     * @throws Error
     */
    public function getList()
    {
        return TwigRenderer::render("videos/page", array
        (
            "videos" => VideoList::load()->sortByDate(false, true)
        ));
    }
}