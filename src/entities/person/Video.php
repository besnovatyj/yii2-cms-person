<?php

namespace Besnovatyj\Person\entities\person;

class Video
{
    public $srcString;

    public function __construct($srcString)
    {
        $this->srcString = $srcString;
    }
}
