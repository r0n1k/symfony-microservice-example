<?php


namespace App\Tests\_support\Helper\Factories;


use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Id;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Tests\Helper\DataFactory;
use Faker\Factory;

class BlockFactory
{

   public static function build(DataFactory $factory, $data = [])
   {
      $faker = Factory::create();

      /** @var Paragraph $paragraph */
      $paragraph = $data['paragraph'] ?? $factory->make(Paragraph::class);
      $kind = $data['kind'] ?? new Kind($faker->randomElement([Kind::TEXT, Kind::DICT]));
      $state = $data['state'] ?? new State($faker->randomElement([State::WAITING_TO_START]));
      /** @var BlockRepository $blockRepo */
      $blockRepo = $factory->_getContainer()->get(BlockRepository::class);
      $id = $blockRepo->nextId();

      return new Block($id, $kind, $paragraph, $state);
   }

}
