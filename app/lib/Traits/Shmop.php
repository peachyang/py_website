<?php

namespace Seahinet\Lib\Traits;

use Seahinet\Lib\Bootstrap;

trait Shmop
{

    /**
     * Flush system config in shared memory
     * 
     * @return bool
     */
    protected function flushShmop()
    {
        if (extension_loaded('shmop')) {
            $ftok = function_exists('ftok') ? 'ftok' : function($pathname, $proj) {
                $st = @stat($pathname);
                if (!$st) {
                    return -1;
                }
                $key = sprintf("%u", (($st['ino'] & 0xffff) | (($st['dev'] & 0xff) << 16) | (($proj & 0xff) << 24)));
                return $key;
            };
            $shmid = @shmop_open($ftok(BP . 'app/lib/Bootstrap.php', 'R'), 'w', 0644, Bootstrap::SHMOP_SIZE);
            if ($shmid) {
                shmop_delete($shmid);
                shmop_close($shmid);
            }
            return true;
        }
        return false;
    }

}
