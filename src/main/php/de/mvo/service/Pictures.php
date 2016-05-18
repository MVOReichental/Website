<?php
namespace de\mvo\service;

class Pictures
{
	public function getYears()
	{
		return array
		(
			array
			(
				"year" => 2016,
				"coverAlbum" => array
				(
					"id" => 137,
					"cover" => array
					(
						"id" => 123,
						"file" => "c9d11768ed1e830fbc0f6ceb42069f11"
					)
				)
			),
			array
			(
				"year" => 2015,
				"coverAlbum" => array
				(
					"id" => 143,
					"cover" => array
					(
						"id" => 123,
						"file" => "341806e61165a496cb4e60f05422b9fc"
					)
				)
			)
		);
	}

	public function getAlbums()
	{
		return array
		(
			"year" => 2016,
			"albums" => array
			(
				array
				(
					"id" => 137,
					"cover" => array
					(
						"id" => 123,
						"file" => "c9d11768ed1e830fbc0f6ceb42069f11"
					),
					"title" => "Example"
				)
			)
		);
	}

	public function getAlbumDetails()
	{
		return array
		(
			"id" => 137,
			"title" => "Example",
			"date" => "2016-02-05",
			"pictures" => array
			(
				array
				(
					"id" => 1,
					"file" => "c9d11768ed1e830fbc0f6ceb42069f11"
				),
				array
				(
					"id" => 2,
					"file" => "80330fc667a9f4870ae1f1d36fb54660"
				)
			)
		);
	}
}