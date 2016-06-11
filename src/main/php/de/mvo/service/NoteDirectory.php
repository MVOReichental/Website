<?php
namespace de\mvo\service;

use de\mvo\model\notedirectory\Categories;
use de\mvo\model\notedirectory\Category;
use de\mvo\model\notedirectory\Program;
use de\mvo\model\notedirectory\Programs;
use de\mvo\model\notedirectory\Title;
use de\mvo\model\notedirectory\Titles;
use de\mvo\MustacheRenderer;
use de\mvo\service\exception\NotFoundException;

class NoteDirectory extends AbstractService
{
	public function redirectToLatestProgram()
	{
		$program = Program::getLatest();
		if ($program === null)
		{
			throw new NotFoundException;
		}

		header("Location: /intern/notedirectory/programs/" . $program->year . "/" . $program->name, true, 302);
		return null;
	}

	private static function renderListPage($title, $list)
	{
		return MustacheRenderer::render("notedirectory/list/page", array
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
		if ($program === null)
		{
			throw new NotFoundException;
		}

		return self::renderListPage($program->title . " " . $program->year, MustacheRenderer::render("notedirectory/list/program", $program));
	}

	public function getTitlesWithCategory()
	{
		$category = Category::getById($this->params->id);
		if ($category === null)
		{
			throw new NotFoundException;
		}

		return self::renderListPage($category->title, MustacheRenderer::render("notedirectory/list/titles", array
		(
			"titles" => Titles::getByCategory($category)
		)));
	}

	public function getAllTitles()
	{
		return self::renderListPage("Alle Titel", MustacheRenderer::render("notedirectory/list/titles-grouped", array
		(
			"categories" => Titles::getAll()->getInCategories()
		)));
	}

	public function getTitleDetails()
	{
		$title = Title::getById($this->params->id);
		if ($title === null)
		{
			throw new NotFoundException;
		}

		return self::renderListPage("Details zu " . $title->title, MustacheRenderer::render("notedirectory/list/title-details", $title));
	}
}