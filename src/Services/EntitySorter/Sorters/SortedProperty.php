<?php
namespace App\Services\EntitySorter\Sorters;

class SortedProperty
{
    public function __construct(string $name, $oldValue, $newValue)
    {
        $this->name = $name;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    public $name;
    public $oldValue;
    public $newValue;
}
