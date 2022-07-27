<?php namespace App\Tests;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Order;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Conclusion\Paragraph\Title;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;
use App\Tests\FunctionalTester;

class ConclusionParagraphOrderingCest
{

   /**
    * @var ParagraphRepository
    */
   private $paragraphRepo;

   public function _before(FunctionalTester $I)
   {
      $this->paragraphRepo = $I->grabService(ParagraphRepository::class);
   }

   public function testOrdering(FunctionalTester $I)
    {
       /** @var Conclusion $conclusion */
       $conclusion = $I->have(Conclusion::class);

       $paragraph1 = $conclusion->addParagraph($this->paragraphRepo->nextId(), Title::of("1"), Order::of(3));
       $paragraph2 = $conclusion->addParagraph($this->paragraphRepo->nextId(), Title::of("2"), Order::of(2));
       $paragraph3 = $conclusion->addParagraph($this->paragraphRepo->nextId(), Title::of("3"), Order::of(1));

       $ids = $conclusion->getParagraphs()
          ->map(static function ($paragraph) { return $paragraph->getId()->getValue(); })
          ->getValues();

       $I->assertEquals([
          $paragraph3->getId()->getValue(),
          $paragraph2->getId()->getValue(),
          $paragraph1->getId()->getValue(),
       ], $ids);


       $paragraph1->setOrder(Order::of(1));
       $paragraph2->setOrder(Order::of(2));
       $paragraph3->setOrder(Order::of(3));

       $ids = $conclusion->getParagraphs()
          ->map(static function ($paragraph) { return $paragraph->getId()->getValue(); })
          ->getValues();

       $I->assertEquals([
          $paragraph1->getId()->getValue(),
          $paragraph2->getId()->getValue(),
          $paragraph3->getId()->getValue(),
       ], $ids);
    }
}
