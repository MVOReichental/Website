<?php
namespace de\mvo\service;

use de\mvo\TwigRenderer;
use Twig_Error;

class StaticView extends AbstractService
{
    /**
     * @param $name
     * @return string
     * @throws Twig_Error
     */
    public function get($name)
    {
        return TwigRenderer::render($name);
    }
}