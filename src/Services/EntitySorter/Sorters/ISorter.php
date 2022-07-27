<?php

namespace App\Services\EntitySorter\Sorters;

interface ISorter
{
    public function add(object $entity);

    public function update(object $entity);

    public function delete(object $entity);
}
