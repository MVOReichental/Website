#! /usr/bin/env php
<?php
require_once __DIR__ . "/../bootstrap.php";

foreach (new DirectoryIterator(PICTURES_ROOT) as $item) {
    if ($item->isDot()) {
        continue;
    }

    if (!$item->isDir()) {
        continue;
    }

    $sourcePath = $item->getPath() . "/" . $item->getFilename();

    $file = $sourcePath . "/album.json";
    if (!file_exists($file)) {
        continue;
    }

    $albumData = json_decode(file_get_contents($file));

    if (!isset($albumData->year)) {
        fwrite(STDERR, "[Error] Missing year in " . $file . "\n");
        continue;
    }

    $year = $albumData->year;

    if (isset($albumData->album)) {
        $folderName = $albumData->album;
    } elseif (isset($albumData->folderName)) {
        $folderName = $albumData->folderName;
    } else {
        fwrite(STDERR, "[Error] Missing album or folderName in " . $file . "\n");
        continue;
    }

    // Parse folder name in format "12.24 My Album" or "05.27-28 Another Album"
    if (preg_match("/^([0-9]+).([0-9]+).?(-[0-9\.]+)? (.*)$/", $folderName, $matches)) {
        $date = $year . "-" . $matches[1] . "-" . $matches[2];
        $title = $matches[4];
    } else {
        fwrite(STDERR, "[Error] Can not parse folder name in " . $file . ": " . $folderName . "\n");
        continue;
    }

    $pictures = array();

    foreach ($albumData->pictures as $picture) {
        if (is_string($picture)) {
            $pictureData = array
            (
                "file" => $picture,
                "title" => null
            );
        } else {
            $pictureData = array
            (
                "file" => $picture->name,
                "title" => null
            );
        }

        $pictures[] = $pictureData;
    }

    $albumName = preg_replace("/[^a-z0-9\-\_\.]/", "-", str_replace(array("ä", "ö", "ü", "ß"), array("ae", "oe", "ue", "ss"), mb_strtolower($title)));

    while (true) {
        $newAlbumName = str_replace("--", "-", $albumName);

        if ($newAlbumName == $albumName) {
            $albumName = $newAlbumName;
            break;
        }

        $albumName = $newAlbumName;
    }

    $albumName = trim($albumName, "-");

    echo $year . "/" . $folderName . " -> " . $year . "/" . $albumName . " -> " . $title . " (" . $date . ")\n";

    if (!is_dir(PICTURES_ROOT . "/" . $year)) {
        mkdir(PICTURES_ROOT . "/" . $year, 0775, true);

        file_put_contents(PICTURES_ROOT . "/" . $year . "/year.json", json_encode(array
        (
            "coverAlbum" => $albumName
        ), JSON_PRETTY_PRINT));
    }

    rename($sourcePath, PICTURES_ROOT . "/" . $year . "/" . $albumName);

    file_put_contents(PICTURES_ROOT . "/" . $year . "/" . $albumName . "/album.json", json_encode(array
    (
        "date" => $date,
        "title" => $title,
        "text" => null,
        "isPublic" => false,
        "coverPicture" => 0,
        "pictures" => $pictures
    ), JSON_PRETTY_PRINT));
}