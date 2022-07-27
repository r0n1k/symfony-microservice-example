<?php


namespace App\Tests\_support\Helper\Factories;


use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Tests\Helper\DataFactory;
use Faker\Factory;

class DictionaryFactory
{

   public static function build(DataFactory $factory, $data = [])
   {
      $faker = Factory::create();

      /** @var Paragraph $paragraph */
      $project = $data['project'] ?? $factory->make(Project::class);
      $dictionaryRepo = $factory->_getContainer()->get(DictionaryRepository::class);
      $id = $dictionaryRepo->nextId();
      $key = $data['key'] ?? new Key($faker->text(32));
      $block = $data['block'] ?? null;
      $name = $data['name'] ?? null;
      $value = $data['value'] ?? null;

      if (is_string($key)) {
         $key = new Key($key);
      }

      return new Dictionary($id, $key, $project, $block, $name, $value);
   }

}
