<?php

namespace Seahinet\Resource\Traits;

use Imagine\Image\Box;

trait Resize
{

    /**
     * @var \Imagine\Image\AbstractImagine
     */
    protected $imagine = null;

    /**
     * 
     * @param string $file
     * @param int $width
     * @param int $height
     * @return \Imagine\Image\AbstractImage
     */
    protected function resize($file, $width, $height)
    {
        if (is_null($this->imagine)) {
            $this->imagine = $this->getContainer()->get('imagine');
        }
        $image = $this->imagine->open($file);
        $image->resize(new Box($width, $height ? $height : $width));
        return $image;
    }

}
