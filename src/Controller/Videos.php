<?php
namespace App\Controller;

use App\Entity\videos\VideoList;
use App\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Error\Error;

class Videos extends AbstractController
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