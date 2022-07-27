<?php


namespace App\Domain\Project\Service;


use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;

class DictionaryValueFetcher
{

   /**
    * @var DictionaryRepository
    */
   private DictionaryRepository $dictionaries;

   public function __construct(DictionaryRepository $dictionaries)
   {
      $this->dictionaries = $dictionaries;
   }

   public function fetchByProjectAndKey(Project $project, Key $key)
   {
      $dict = $this->dictionaries->findByProjectAndKey($project, $key);
      if (!$dict) {
         throw new \DomainException("Dictionary with key $key not found in project {$project->getId()}");
      }
      return $dict->getValue();
   }

}
