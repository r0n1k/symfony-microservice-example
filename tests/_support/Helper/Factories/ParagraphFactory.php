<?php


namespace App\Tests\_support\Helper\Factories;


use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Title;
use App\Tests\Helper\DataFactory;
use Faker\Factory;

class ParagraphFactory
{
   public static function build(DataFactory $factory, $data = [])
   {
      $faker = Factory::create();

      /** @var Conclusion $conclusion */
      $conclusion = $data['conclusion'] ?? $factory->make(Conclusion::class);
      /** @var ParagraphRepository $paragraphs */
      $paragraphs = $factory->_getContainer()->get(ParagraphRepository::class);
      $id = $paragraphs->nextId();
      $title = $data['title'] ?? new Title($faker->text(32));
      $parent = $data['parent'] ?? null;

      if ($parent) {
         return $parent->addChild($id, $title);
      }

      return new Paragraph($id, $conclusion, $title, null);
   }
}
