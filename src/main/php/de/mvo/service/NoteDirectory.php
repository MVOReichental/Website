<?php
namespace de\mvo\service;

use de\mvo\model\notedirectory\Categories;
use de\mvo\model\notedirectory\Category;
use de\mvo\model\notedirectory\Program;
use de\mvo\model\notedirectory\Programs;
use de\mvo\model\notedirectory\SingleModel;
use de\mvo\model\notedirectory\Title;
use de\mvo\model\notedirectory\Titles;
use de\mvo\model\users\User;
use de\mvo\service\exception\NotFoundException;
use de\mvo\TwigRenderer;

class NoteDirectory extends AbstractService
{
    public static function getEditorPages()
    {
        return array
        (
            "titles" => array
            (
                "singleEditView" => "title-editor",
                "modelClass" => Title::class,
                "title" => "Titel"
            ),
            "programs" => array
            (
                "singleEditView" => "program-editor",
                "modelClass" => Program::class,
                "title" => "Programme"
            ),
            "categories" => array
            (
                "singleEditView" => "category-editor",
                "modelClass" => Category::class,
                "title" => "Kategorien"
            )
        );
    }

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

    private static function renderListPage($title, $list)
    {
        return TwigRenderer::render("notedirectory/list/page", array
        (
            "title" => $title,
            "otherPrograms" => Programs::getAll()->getGroupedByYear(),
            "categories" => Categories::getAll(),
            "list" => $list
        ));
    }

    public function getProgram()
    {
        $program = Program::getByYearAndName($this->params->year, $this->params->name);
        if ($program === null) {
            throw new NotFoundException;
        }

        return self::renderListPage($program->title . " " . $program->year, TwigRenderer::render("notedirectory/list/program", array
        (
            "program" => $program
        )));
    }

    public function getTitlesWithCategory()
    {
        $category = Category::getById($this->params->id);
        if ($category === null) {
            throw new NotFoundException;
        }

        return self::renderListPage($category->title, TwigRenderer::render("notedirectory/list/titles", array
        (
            "titles" => Titles::getByCategory($category)
        )));
    }

    public function getAllTitles()
    {
        return self::renderListPage("Alle Titel", TwigRenderer::render("notedirectory/list/titles-grouped", array
        (
            "categories" => Titles::getAll()->getInCategories()
        )));
    }

    public function getTitleDetails()
    {
        $title = Title::getById($this->params->id);
        if ($title === null) {
            throw new NotFoundException;
        }

        return self::renderListPage($title->title, TwigRenderer::render("notedirectory/list/title-details", array
        (
            "title" => $title
        )));
    }

    public function getEditPage()
    {
        $pages = self::getEditorPages();

        $activePage = null;

        foreach ($pages as $name => &$page) {
            $page["name"] = $name;

            if ($this->params->page == $page["name"]) {
                $page["active"] = true;

                $activePage = $page;
            } else {
                $page["active"] = false;
            }
        }

        $context = array
        (
            "pages" => array_values($pages),
            "title" => $activePage["title"],
            "activePage" => $activePage
        );

        switch ($activePage["name"]) {
            case "titles":
                $context["titles"] = Titles::getAll();
                break;
            case "programs":
                $context["programs"] = Programs::getAll();
                break;
        }

        return TwigRenderer::render("notedirectory/editor/" . $activePage["name"], $context);
    }

    private static function getEditorPage($page, $id, $createNewOnSave)
    {
        $pageConfig = self::getEditorPages()[$page];

        if ($id === null) {
            $modelData = null;
        } else {
            /**
             * @var $modelClass SingleModel
             */
            $modelClass = $pageConfig["modelClass"];

            $modelData = $modelClass::getById($id);

            if ($modelData === null) {
                throw new NotFoundException;
            }
        }

        return TwigRenderer::render("notedirectory/editor/" . $pageConfig["singleEditView"], array
        (
            "data" => $modelData,
            "createNewOnSave" => $createNewOnSave
        ));
    }

    public function getEditExistingPage($createNewOnSave = false)
    {
        return self::getEditorPage($this->params->page, $this->params->id, $createNewOnSave);
    }

    public function getEditNewPage()
    {
        return self::getEditorPage($this->params->page, null, true);
    }
}