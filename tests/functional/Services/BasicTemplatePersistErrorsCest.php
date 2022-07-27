<?php /** @noinspection PhpUnused */

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace App\Tests\Services;
use App\Domain\Template\Entity\Id;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\Entity\TemplateParagraph\Title as ParagraphTitle;
use App\Domain\Template\Entity\Title;
use App\Tests\FunctionalTester;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

class BasicTemplatePersistErrorsCest
{
   /**
    * @var EntityManagerInterface
    */
   private $em;

   public function _before(FunctionalTester $I)
    {
       $this->em = $I->grabService(EntityManagerInterface::class);
    }

    public function testErrors(FunctionalTester $I)
    {
       $basicTemplate = new Template(Id::next(), Title::of('title'), true);

       $I->expectThrowable(LogicException::class, function () use ($basicTemplate) {
          $this->em->persist($basicTemplate);
       });

       $basicParagraph = $basicTemplate->addParagraph(ParagraphTitle::of('title'));

       $I->expectThrowable(LogicException::class, function () use ($basicParagraph) {
          $this->em->persist($basicParagraph);
       });

       $basicCertificate = $basicParagraph->addCertificate('certificate');

       $I->expectThrowable(LogicException::class, function () use ($basicCertificate) {
          $this->em->persist($basicCertificate);
       });

       $basicDictionary = $basicParagraph->addDictionary('dictionarykey');

       $I->expectThrowable(LogicException::class, function () use ($basicDictionary) {
          $this->em->persist($basicDictionary);
       });
    }
}
