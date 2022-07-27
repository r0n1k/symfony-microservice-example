<?php /** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

namespace App\Tests\Http\Project;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Entity\Users\User\User;
use App\Domain\Project\Repository\Dictionary\DictionaryRepository;
use App\Domain\Project\UseCase\Project\Upsert\DictionaryDTO;
use App\Domain\Project\UseCase\Project\Upsert\DTO;
use App\Domain\Project\UseCase\Project\Upsert\Handler;
use App\Domain\Project\UseCase\Project\Upsert\UserDTO;
use App\Tests\FunctionalTester;
use App\Tests\Helper\MockClasses\TestProjectUpsertHandler;
use Doctrine\Common\Collections\ArrayCollection;
use JsonException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UpdateCest
 * @package App\Tests\Http\Project
 *
 * @see Handler
 * @see ProjectController::execute()
 */
class UpsertCest
{
   private DictionaryRepository $dictionaryRepo;

   public function _before(FunctionalTester $I)
    {
       $this->dictionaryRepo = $I->grabService(DictionaryRepository::class);
    }

   protected function generateProjectDTO(FunctionalTester $I)
   {
      $faker = $I->faker();
      $dto = new DTO();
      $dto->project_id = $faker->uuid;
      $dto->project_name = $faker->text(32);
      $dto->project_state = 'default';
      $dto->users = [
         $userDTO = new UserDTO(),
      ];

      $userDTO->id = (int)$faker->randomNumber();
      $userDTO->role = 'expert';
      $userDTO->assignment_type = 'main_expert';
      $userDTO->full_name = $faker->name;
      $userDTO->email = $faker->email;
      $userDTO->certificates = [];

      $dto->dictionaries = new DictionaryDTO();
      $dto->dictionaries->declarant = [
         'key.a' => 'value.a',
         'b' => 'value.b',
      ];

      return (array)$dto;
   }


   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function updateProject(FunctionalTester $I)
   {
      $dto = $this->generateProjectDTO($I);
      $projectId = $dto['project_id'];

      $I->amLoggedIn();

      $createUrl = $I->grabRoute('project.create');
      $updateUrl = $I->grabRoute('project.update', ['project_id' => $projectId]);

      $I->sendPOST($createUrl, json_encode($dto, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(201);

      /** @var Project $project */
      $project = $I->grabEntityFromRepository(Project::class, ['id' => $projectId]);
      $I->assertCount(2, $I->grabEntitiesFromRepository(Dictionary::class, ['project' => $project]));

      $I->sendPOST($createUrl, json_encode($dto, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(409);
      $I->assertCount(2, $I->grabEntitiesFromRepository(Dictionary::class, ['project' => $project]));

      $I->sendPUT($updateUrl, json_encode($dto, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);
      $I->assertCount(2, $I->grabEntitiesFromRepository(Dictionary::class, ['project' => $project]), 'Dictionaries are not duplicated');


      $dictionaries = $this->dictionaryRepo->findByProject($project);
      $I->assertCount(2, $dictionaries);
      /** @var Dictionary $dictionaryItem */
      $dictionaryItem = (new ArrayCollection($dictionaries))->filter(static function (Dictionary $item) {
         return $item->getValue() === 'value.a';
      })->first();

      $I->assertEquals('declarant.key.a', $dictionaryItem->getKey()->getValue());
   }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function removesUnusedAssignments(FunctionalTester $I)
   {
      /** @var User $userA */
      $userA = $I->have(User::class, ['role' => Role::admin()]);
      /** @var User $userB */
      $userB = $I->have(User::class);
      /** @var User $userC */
      $userC = $I->have(User::class);

      $I->amLoggedInAs($userA);

      $dto = $this->generateProjectDTO($I);
      $dto['users'] = [
         [
            'id' => $userA->getId()->getValue(),
            'email' => (string)$userA->getEmail(),
            'role' => (string)$userA->getRole(),
            'assignment_type' => 'expert',
            'full_name' => (string)$userA->getFullName(),
            'certificates' => [],
         ],
         [
            'id' => $userB->getId()->getValue(),
            'email' => (string)$userB->getEmail(),
            'role' => (string)$userB->getRole(),
            'assignment_type' => 'expert',
            'full_name' => (string)$userB->getFullName(),
            'certificates' => [],
         ],
         [
            'id' => $userC->getId()->getValue(),
            'email' => (string)$userC->getEmail(),
            'role' => (string)$userC->getRole(),
            'assignment_type' => 'expert',
            'full_name' => (string)$userC->getFullName(),
            'certificates' => [],
         ],
      ];

      $createUrl = $I->grabRoute('project.create');
      $I->sendPOST($createUrl, json_encode($dto, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(201);

      /** @var Project $project */
      $project = $I->grabEntityFromRepository(Project::class, ['id' => $dto['project_id']]);
      $I->assertCount(3, $project->getUsers());

      $dto['users'] = [
         [
            'id' => $userA->getId()->getValue(),
            'email' => (string)$userA->getEmail(),
            'role' => (string)$userA->getRole(),
            'assignment_type' => 'expert',
            'full_name' => (string)$userA->getFullName(),
            'certificates' => [],
         ],
         [
            'id' => $userC->getId()->getValue(),
            'email' => (string)$userC->getEmail(),
            'role' => (string)$userC->getRole(),
            'assignment_type' => 'expert',
            'full_name' => (string)$userC->getFullName(),
            'certificates' => [],
         ]
      ];
      $updateUrl = $I->grabRoute('project.update', [
         'project_id' => $dto['project_id'],
      ]);
      $I->sendPUT($updateUrl, json_encode($dto, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(200);

      /** @var Project $project */
      $project = $I->grabEntityFromRepository(Project::class, ['id' => $dto['project_id']]);
      $I->assertCount(2, $project->getUsers());
   }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function errorOnNonExistentProject(FunctionalTester $I)
   {
      $I->amLoggedIn();
      $dto = $this->generateProjectDTO($I);
      $projectId = $dto['project_id'];
      $I->sendPUT("/project/{$projectId}", json_encode($dto, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512));
      $I->seeResponseCodeIs(404);
      $response = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR);

      $I->assertNotEmpty($response['errors']);
      $I->assertNull($response['data']);
   }

}
