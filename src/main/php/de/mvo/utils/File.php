<?php
namespace de\mvo\utils;

class File
{
    public static function getType($extension)
    {
        switch (strtolower($extension)) {
            case "doc":
                return "Word 97/2003 Dokument";
            case "docx":
                return "Word 2007+ Dokument";
            case "pdf":
                return "Adobe PDF Dokument";
            case "xls":
                return "Excel 97/2003 Arbeitsblatt";
            case "xlsx":
                return "Excel 2007+ Arbeitsblatt";
            default:
                return $extension . "-Datei";
        }
    }
}