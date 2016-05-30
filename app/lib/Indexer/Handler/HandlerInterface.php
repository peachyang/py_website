<?php

namespace Seahinet\Lib\Indexer\Handler;

interface HandlerInterface
{

    public function reindex();

    public function select($constraint);

    public function insert($values);

    public function update($values, $constraint);
    
    public function upsert($values, $constraint);

    public function delete($constraint);
}
