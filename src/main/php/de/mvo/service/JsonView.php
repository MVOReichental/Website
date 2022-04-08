<?php
namespace de\mvo\service;

use de\mvo\TwigRenderer;
use Twig\Error\Error;

class JsonView extends AbstractService
{
    /**
     * @param $template
     * @param $modelFilename
     * @return string
     * @throws Error
     */
    public function get($template, $modelFilename)
    {
        $json = json_decode(file_get_contents(MODELS_ROOT . "/" . $modelFilename . ".json"));

        if (is_array($json)) {
            $context = array
            (
                "json" => $json
            );
        } else {
            $context = (array)$json;
        }

        return TwigRenderer::render($template, $context);
    }
}