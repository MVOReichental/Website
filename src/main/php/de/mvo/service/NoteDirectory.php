<?php
namespace de\mvo\service;

use de\mvo\model\notedirectory\Program;
use de\mvo\model\notedirectory\Programs;
use de\mvo\model\notedirectory\Title;
use de\mvo\model\notedirectory\Titles;
use de\mvo\model\users\User;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;
use Twig_Error;

class NoteDirectory extends AbstractService
{
    /**
     * @return null
     * @throws NotFoundException
     */
    public function redirectToLatestDefaultProgram()
    {
        $program = Program::getLatest();
        if ($program === null) {
            if (User::getCurrent()->hasPermission("notedirectory.edit")) {
                header("Location: /internal/notedirectory/editor");
                return null;
            }

            throw new NotFoundException;
        }

        header("Location: /internal/notedirectory/programs/" . $program->year . "/" . $program->name, true, 302);
        return null;
    }

    /**
     * @param $title
     * @param $activePath
     * @param $list
     * @return string
     * @throws Twig_Error
     */
    private static function renderListPage($title, $activePath, $list)
    {
        return TwigRenderer::render("notedirectory/list/page", array
        (
            "title" => $title,
            "active" => $activePath,
            "programs" => Programs::getAll()->getGroupedByYear(),
            "list" => $list
        ));
    }

    /**
     * @return string
     * @throws NotFoundException
     * @throws Twig_Error
     */
    public function getProgram()
    {
        $program = Program::getByYearAndName($this->params->year, $this->params->name);
        if ($program === null) {
            throw new NotFoundException;
        }

        return self::renderListPage($program->title . " " . $program->year, "programs/" . $program->year . "/" . $program->name, TwigRenderer::render("notedirectory/list/program", array
        (
            "program" => $program
        )));
    }

    /**
     * @return string
     * @throws Twig_Error
     */
    public function getAllTitles()
    {
        return self::renderListPage("Alle Titel", "titles", TwigRenderer::render("notedirectory/list/titles", array
        (
            "titles" => Titles::getAll()
        )));
    }

    /**
     * @return string
     * @throws NotFoundException
     * @throws Twig_Error
     */
    public function getTitleDetails()
    {
        $title = Title::getById($this->params->id);
        if ($title === null) {
            throw new NotFoundException;
        }

        return self::renderListPage($title->title, null, TwigRenderer::render("notedirectory/list/title-details", array
        (
            "title" => $title
        )));
    }
}