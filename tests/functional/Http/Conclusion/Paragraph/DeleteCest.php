<?php /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph;
use App\Domain\Common\Service\TemplateBootstrapper;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphDeleted;
use App\Domain\Template\TemplateFileRepository;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Delete\Handler;
use App\Http\Controller\Conclusion\ParagraphController;
use App\Tests\FunctionalTester;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class DeleteCest
 *
 * @see Handler
 * @see ParagraphController::deleteParagraph()
 */
class DeleteCest
{
   private Conclusion $conclusion;
   private Paragraph $paragraph;
   private Block $block;
   private Paragraph $childParagraph;
   private Block $childBlock;

   public function _before(FunctionalTester $I)
   {
      $this->block = $I->have(Block::class);
      $this->paragraph = $this->block->getParagraph();
      $this->conclusion = $this->paragraph->getConclusion();
      $this->childParagraph = $I->have(Paragraph::class, [
         'conclusion' => $this->conclusion,
         'parent' => $this->paragraph,
      ]);
      /** @var Block $childBlock */
      $childBlock = $I->make(Block::class);
      $this->childBlock = $this->childParagraph->addBlock($childBlock->getId(), $childBlock->getKind(), $childBlock->getState(), $childBlock->getOrder());
      $I->haveInRepository($this->childBlock);
      $I->amLoggedInWithRole(Role::admin());
   }



   public function deleteExistent(FunctionalTester $I)
   {
      $url = $I->grabRoute('conclusion.paragraph.delete', [
         'conclusion_id' => $this->conclusion->getId()->getValue(),
         'paragraph_id' => $this->paragraph->getId()->getValue(),
      ]);

      $I->sendDELETE($url);
      $I->seeResponseCodeIs(200);
      $I->seeDispatchedDomainEvent(ParagraphDeleted::class);
      $I->clearDispatchedDomainEvents();
      $I->seeInRepository(Conclusion::class, ['id' => $this->conclusion->getId()]);
      $I->dontSeeInRepository(Paragraph::class, ['id' => $this->paragraph->getId()]);
      $I->dontSeeInRepository(Paragraph::class, ['id' => $this->childParagraph->getId()]);
      $I->dontSeeInRepository(Block::class, ['id' => $this->block->getId()]);
      $I->dontSeeInRepository(Block::class, ['id' => $this->childBlock->getId()]);
   }

   public function deleteNonExistent(FunctionalTester $I)
   {
      $url = $I->grabRoute('conclusion.paragraph.delete', [
         'conclusion_id' => $this->conclusion->getId()->getValue(),
         'paragraph_id' => '-1',
      ]);
      $I->sendDELETE($url);
      $I->seeResponseCodeIs(404);
      $I->dontSeeDispatchedDomainEvent(ParagraphDeleted::class);
      $I->clearDispatchedDomainEvents();
      $I->seeInRepository(Paragraph::class, ['id' => $this->paragraph->getId()]);
      $I->seeInRepository(Paragraph::class, ['id' => $this->childParagraph->getId()]);
      $I->seeInRepository(Block::class, ['id' => $this->block->getId()]);
      $I->seeInRepository(Block::class, ['id' => $this->childBlock->getId()]);
   }


   public function deleteFromLargeTree(FunctionalTester $I)
   {
      $templateId = $I->haveTemplate([
         'Name' => 'test',
         'Paragraphs' => [
            [
               'Name' => '1',
               'Children' => [
                  [
                     'Name' => '1.1',
                     'Children' => [
                        [
                           'Name' => '1.1.1',
                        ],
                        [
                           'Name' => '1.1.2',
                        ],
                     ]
                  ]
               ],
            ],
            [
               'Name' => '2',
               'Children' => [
                  [
                     'Name' => '2.1',
                     'Children' => [
                        [
                           'Name' => '2.1.1',
                        ]
                     ]
                  ],
                  [
                     'Name' => '2.2',
                     'Children' => [
                        [
                           'Name' => '2.2.1',
                        ]
                     ]
                  ]
               ],
            ],
         ]
      ]);


      $conclusion = $this->conclusion;
      /** @var EntityManagerInterface $em */
      $em = $I->grabService(EntityManagerInterface::class);
      foreach ($conclusion->getParagraphs() as $p) {
         $conclusion->removeParagraph($p->getId());
         $em->remove($p);
      }
      $em->flush();
      $template = ($I->getSymfonyKernel()->getContainer()->get(TemplateFileRepository::class))->find($templateId);

      /** @var TemplateBootstrapper $bootstrapper */
      $bootstrapper = $I->grabService(TemplateBootstrapper::class);
      $bootstrapper->bootstrap($template, $conclusion);
      $I->haveInRepository($conclusion);

      $paragraphs = $I->grabEntitiesFromRepository(Paragraph::class, ['conclusion' => $conclusion]);
      $I->assertCount(9, $paragraphs);

      $paragraph = $I->grabEntityFromRepository(Paragraph::class, ['title' => '2.2.1']);
      $I->assertEquals('2.2.1', $paragraph->getTitle());
      $url = $I->grabRoute('conclusion.paragraph.delete', [
         'conclusion_id' => $conclusion->getId()->getValue(),
         'paragraph_id' => $paragraph->getId()->getValue(),
      ]);
      $I->clearDispatchedDomainEvents();
      $I->sendDELETE($url);
      $I->seeResponseCodeIs(200);
      $paragraphs = $I->grabEntitiesFromRepository(Paragraph::class, ['conclusion' => $conclusion]);
      $I->assertCount(8, $paragraphs);
      $I->seeDispatchedDomainEvent(ParagraphDeleted::class, $paragraph);
   }

}
