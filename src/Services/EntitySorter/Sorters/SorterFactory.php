<?php

namespace App\Services\EntitySorter\Sorters;

use App\Services\EntitySorter\Annotation\Sorted;
use App\Services\EntitySorter\Repository\HierarchySorterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;

class SorterFactory
{
    /*private EntityManagerInterface $em;
    private HierarchySorterRepository $hierarchySorterRepository;

    public function __construct(EntityManagerInterface $em, HierarchySorterRepository $hierarchySorterRepository)
    {
        $this->em = $em;
        $this->hierarchySorterRepository = $hierarchySorterRepository;
    }*/

    private HierarchySorter $hierarchySorter;

    public function __construct(HierarchySorter $hierarchySorter)
    {
        $this->hierarchySorter = $hierarchySorter;
    }

    public function make(Sorted $sortedAnnotation): ISorter
    {
        $parentColumnNotSet = empty($sortedAnnotation->parent_property);
        if($parentColumnNotSet){
            return new SimpleSorter();
        } else {
            return $this->hierarchySorter;
        }
    }
}
