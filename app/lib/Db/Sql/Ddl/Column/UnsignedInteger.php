<?php

namespace Seahinet\Lib\Db\Sql\Ddl\Column;

use Zend\Db\Sql\Ddl\Column\Integer;

class UnsignedInteger extends Integer
{

    protected $type = 'INTEGER UNSIGNED';

}
