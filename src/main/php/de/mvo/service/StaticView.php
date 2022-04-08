<?php
namespace de\mvo\service;

use de\mvo\TwigRenderer;
use Twig\Error\Error;

class StaticView extends AbstractService
{
    /**
     * @param $name
     * @return string
     * @throws Error
     */
    public function get($name)
    {
        return TwigRenderer::render($name);
    }
}