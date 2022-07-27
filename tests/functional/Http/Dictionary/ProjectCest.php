<?php /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

namespace App\Tests\Http\Project;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\Role;
use App\Tests\FunctionalTester;

class ProjectCest
{
   private Project $project;
   /**
    * @var Dictionary
    */
   private Dictionary $dictionaryItem;

   public function _before(FunctionalTester $I)
   {
      /** @var Project project */
      $this->project = $I->have(Project::class);
      $this->dictionaryItem = $I->have(Dictionary::class, ['project' => $this->project, 'value' => 'test']);
      $I->amLoggedInWithRole(Role::admin());
   }

   public function testChangeExists(FunctionalTester $I)
   {
      $dictKey = $this->dictionaryItem->getKey();
      $dictValue = $this->dictionaryItem->getValue();
      $url = $I->grabRoute('dictionary.project.upsert', [
         'project_id' => $this->project->getId(),
         'dictionary_key' => $dictKey,
      ]);

      $I->seeInRepository(Dictionary::class, ['key' => $dictKey, 'value' => $dictValue]);
      $I->sendPUT($url, ['value' => $newDictValue = 'value2']);
      $I->seeResponseCodeIs(200);
      $I->dontSeeInRepository(Dictionary::class, ['key' => $dictKey, 'value' => $dictValue]);
      $I->seeInRepository(Dictionary::class, ['key' => $dictKey, 'value' => $newDictValue]);
   }

   public function testCreateNew(FunctionalTester $I)
   {
      $url = $I->grabRoute('dictionary.project.upsert', [
         'project_id' => $this->project->getId(),
         'dictionary_key' => $dictKey = 'project.dictionary.second_item',
      ]);

      $I->dontSeeInRepository(Dictionary::class, ['key' => $dictKey]);
      $I->sendPUT($url, ['value' => $dictValue = 'value3']);
      $I->seeResponseCodeIs(200);
      $I->seeInRepository(Dictionary::class, ['key' => $dictKey, 'value' => $dictValue]);
   }

   public function testDelete(FunctionalTester $I)
   {
      $url = $I->grabRoute('dictionary.project.delete', [
         'project_id' => $this->project->getId(),
         'dictionary_key' => $this->dictionaryItem->getKey(),
      ]);

      $I->seeInRepository(Dictionary::class, ['key' => $this->dictionaryItem->getKey()]);
      $I->sendDELETE($url);
      $I->seeResponseCodeIs(200);
      $I->dontSeeInRepository(Dictionary::class, ['key' => $this->dictionaryItem->getKey()]);
   }
}
