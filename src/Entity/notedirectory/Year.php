<?php
namespace App\Entity\notedirectory;

use ArrayObject;

class Year extends ArrayObject
{
    /**
     * @var int
     */
    public $year;
    /**
     * @var Programs
     */
    public $programs;
}