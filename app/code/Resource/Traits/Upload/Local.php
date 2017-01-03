<?php

namespace Seahinet\Resource\Traits\Upload;

use Seahinet\Resource\Model\Collection\Resource as Collection;

trait Local
{

    /**
     * @param \Seahinet\Lib\Http\UploadedFile $file
     * @return Resource
     * @throws Exception
     */
    public function moveFile($file)
    {
        $newName = $file->getClientFilename();
        $type = substr($file->getClientMediaType(), 0, strpos($file->getClientMediaType(), '/') + 1);
        $path = BP . static::$options['path'] . $type;
        if (!is_dir($path)) {
            mkdir($path, static::$options['dir_mode'], true);
        }
        $md5 = md5($file->getStream()->getContents());
        $collection = new Collection;
        $collection->where(['md5' => $md5])->limit(1);
        if (count($collection)) {
            $newName = $collection[0]['real_name'];
            if (!file_exists($path . $newName)) {
                $file->moveTo($path . $newName);
            }
        } else {
            while (file_exists($path . $newName)) {
                $newName = preg_replace('/(\.[^\.]+$)/', random_int(0, 9) . '$1', $newName);
                if (strlen($newName) >= 120) {
                    throw new Exception('The file is existed.');
                }
            }
            $file->moveTo($path . $newName);
        }
        $this->setData(['md5' => $md5, 'real_name' => $newName, 'size' => (int) $file->getSize()]);
        return $this;
    }

}
