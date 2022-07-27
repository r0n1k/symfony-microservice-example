<?php
/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion\Paragraph;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\Role;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Event\Conclusion\ConclusionCreated;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphChanged;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphCreated;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Create\DTO;
use App\Tests\FunctionalTester;

class CreateCest
{
   /** @var Project */
   private Project $project;
   /** @var User */
   private User $user;
   /** @var Conclusion */
   private Conclusion $conclusion;
   /**
    * @var string
    */
   private string $url;

   public function _before(FunctionalTester $I)
   {
      /** @var User $user */
      $this->user = $user = $I->amLoggedIn();
      $this->conclusion = $conclusion = $I->have(Conclusion::class);
      $this->project = $project = $conclusion->getProject();
      $I->assignUserToProject($user, $project, new Role('main_expert'));

      $this->url = $I->grabRoute('conclusion.paragraph.create', [
         'conclusion_id' => $this->conclusion->getId(),
      ]);
   }

   public function createWithValidData(FunctionalTester $I)
   {
      $dto = [
         'title' => $I->faker()->text(20),
         'order' => 0,
      ];

      $I->haveHttpHeader('Content-Type', 'application/json');
      $I->sendPOST($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeRequestAndResponseAreValid();
      $I->seeResponseCodeIs(201);

      $I->assertCount(1, $this->conclusion->getParagraphs());
      $I->seeDispatchedDomainEvent(ParagraphCreated::class);
   }

   public function createWithInvalidData(FunctionalTester $I)
   {
      $dto = new DTO();
      $dto->parent_id = -1;
      $dto->title = $I->faker()->text(20);
      $dto->order = 0;

      $I->sendPOST($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(400);
      $I->seeRequestAndResponseAreValid();
      $I->dontSeeDispatchedDomainEvent(ParagraphCreated::class);
   }

   public function createChild(FunctionalTester $I)
   {
      /** @var Paragraph $parent */
      $parent = $I->have(Paragraph::class, ['conclusion' => $this->conclusion]);

      $dto = new DTO();
      $dto->title = $I->faker()->text(20);
      $dto->parent_id = $parent->getId()->getValue();
      $dto->order = 0;

      $I->sendPOST($this->url, json_encode($dto, JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(201);
      $I->seeRequestAndResponseAreValid();
      $I->seeDispatchedDomainEvent(ParagraphCreated::class);
      $I->seeDispatchedDomainEvent(ParagraphChanged::class);

      $parent = $I->grabEntityFromRepository(Paragraph::class, ['id' => $parent->getId()]);
      $I->assertCount(1, $parent->getChildren());
   }
}
