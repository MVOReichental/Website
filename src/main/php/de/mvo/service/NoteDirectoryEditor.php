<?php
namespace de\mvo\service;

use de\mvo\model\notedirectory\Program;
use de\mvo\model\notedirectory\Programs;
use de\mvo\model\notedirectory\Title;
use de\mvo\model\notedirectory\Titles;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;
use de\mvo\utils\StringUtil;

class NoteDirectoryEditor extends AbstractService
{
    public static function getEditorData($activePage)
    {
        $pages = array
        (
            "programs" => array
            (
                "title" => "Programme"
            ),
            "titles" => array
            (
                "title" => "Titel"
            )
        );

        foreach ($pages as $name => &$page) {
            if ($name === $activePage) {
                $page["active"] = true;
            }
        }

        return array
        (
            "pages" => $pages,
            "pageTitle" => $pages[$activePage]["title"]
        );
    }

    private static function setProgramTitles(Program $program)
    {
        $program->titles = new Titles;

        foreach ($_POST["title_number"] as $index => $number) {
            $title = Title::getById($_POST["title_id"][$index]);

            $title->number = $number;

            $program->titles->append($title);
        }
    }

    public function editProgram()
    {
        $program = Program::getById($this->params->id);
        if ($program === null) {
            throw new NotFoundException;
        }

        $program->year = $_POST["year"];
        $program->name = strtolower(StringUtil::removeNonAlphanumeric($_POST["title"]));
        $program->title = $_POST["title"];

        self::setProgramTitles($program);

        $program->save();

        header("Location: /internal/notedirectory/editor/programs", true, 302);
        return null;
    }

    public function createProgram()
    {
        $program = new Program;

        $program->year = $_POST["year"];
        $program->name = strtolower(StringUtil::removeNonAlphanumeric($_POST["title"]));
        $program->title = $_POST["title"];

        self::setProgramTitles($program);

        $program->save();

        header("Location: /internal/notedirectory/editor/programs", true, 302);
        return null;
    }

    public function deleteProgram()
    {
        $program = Program::getById($this->params->id);
        if ($program === null) {
            throw new NotFoundException;
        }

        $program->delete();
    }

    public function getProgramEditPage($createNewOnSave = false)
    {
        $program = Program::getById($this->params->id);
        if ($program === null) {
            throw new NotFoundException;
        }

        return TwigRenderer::render("notedirectory/editor/program-editor", array
        (
            "program" => $program,
            "titles" => Titles::getAll(),
            "createNewOnSave" => $createNewOnSave
        ));
    }

    public function getCreateProgramPage()
    {
        return TwigRenderer::render("notedirectory/editor/program-editor", array
        (
            "titles" => Titles::getAll()
        ));
    }

    public function getProgramsPage()
    {
        return TwigRenderer::render("notedirectory/editor/programs", array_merge(self::getEditorData("programs"), array
        (
            "programs" => Programs::getAll()
        )));
    }

    public function editTitle()
    {
        $title = Title::getById($this->params->id);
        if ($title === null) {
            throw new NotFoundException;
        }

        $title->title = $_POST["title"];
        $title->composer = $_POST["composer"];
        $title->arranger = $_POST["arranger"];
        $title->publisher = $_POST["publisher"];

        $title->save();

        header("Location: /internal/notedirectory/editor/titles", true, 302);
        return null;
    }

    public function createTitle()
    {
        $title = new Title;

        $title->title = $_POST["title"];
        $title->composer = $_POST["composer"];
        $title->arranger = $_POST["arranger"];
        $title->publisher = $_POST["publisher"];

        $title->save();

        header("Location: /internal/notedirectory/editor/titles", true, 302);
        return null;
    }

    public function deleteTitle()
    {
        $title = Title::getById($this->params->id);
        if ($title === null) {
            throw new NotFoundException;
        }

        $title->delete();
    }

    public function getTitleEditPage()
    {
        $title = Title::getById($this->params->id);
        if ($title === null) {
            throw new NotFoundException;
        }

        return TwigRenderer::render("notedirectory/editor/title-editor", array
        (
            "title" => $title
        ));
    }

    public function getCreateTitlePage()
    {
        return TwigRenderer::render("notedirectory/editor/title-editor");
    }

    public function getTitlesPage()
    {
        return TwigRenderer::render("notedirectory/editor/titles", array_merge(self::getEditorData("titles"), array
        (
            "titles" => Titles::getAll()
        )));
    }
}