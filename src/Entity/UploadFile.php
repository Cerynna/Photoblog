<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class UploadFile
{

    private $id;

    private $file;

    public function getId()
    {
        return $this->id;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file[] = $file;

        return $this;
    }
}
