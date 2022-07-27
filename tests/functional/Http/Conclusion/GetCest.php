<?php /** @noinspection JsonEncodingApiUsageInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnused */

namespace App\Tests\Http\Conclusion;

use App\Domain\Common\Service\TemplateBootstrapper;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Kind;
use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Template\Repository\TemplateRepository;
use App\Tests\FunctionalTester;
use JsonException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use App\Tests\Helper\MockClasses\TestProjectUpsertHandler;
use App\Domain\Project\UseCase\Project\Upsert\Handler;

class GetCest
{
   /**
    * @var string
    */
   private $url;

   public function _before(FunctionalTester $I)
   {
      /** @var ContainerInterface $container */
      $container = $I->grabService(ContainerInterface::class);
      $container->set(Handler::class, $container->get(TestProjectUpsertHandler::class));
      /**
       * @var Conclusion $conclusion
       */
      $conclusion = $I->have(Conclusion::class);

      $templateId = $I->haveTemplate([
         'Name' => 'test template',
         'Paragraphs' => [
            [
               'Name' => 'P1',
               'Children' => [
                  [
                     'Name' => 'P1.1',
                     'Children' => [
                        [
                           'Name' => 'P1.1.1',
                           'Children' => [
                              [
                                 'Name' => 'P1.1.1.1',
                                 'BlockKind' => 'text',
                              ]
                           ]
                        ]
                     ]
                  ]
               ],
            ],
         ]
      ]);

      $templates = $I->getSymfonyKernel()->getContainer()->get(TemplateRepository::class);
      $template = $templates->get($templateId);
      /** @var TemplateBootstrapper $mapper */
      $mapper = $I->grabService(TemplateBootstrapper::class);
      $mapper->bootstrap($template, $conclusion);
      $I->amLoggedInWithRole(Role::admin());
      $I->haveInRepository($conclusion);
      $this->url = $I->grabRoute('conclusion.get', ['conclusion_id' => $conclusion->getId()]);
   }

   /**
    * @param FunctionalTester $I
    * @throws JsonException
    */
   public function getConclusion(FunctionalTester $I)
   {
      $I->sendGET($this->url);
      $I->seeResponseCodeIs(200);
      $I->seeResponseMatchesJsonType([
         'id' => 'string',
         'author' => ['id' => 'integer'],
         'kind' => 'string:=' . Kind::GENERATOR,
         'created_at' => 'integer',
      ], '$.data');

      $data = json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR)['data'];
      $paragraphs = $data['paragraphs'];
      $I->assertCount(4, $paragraphs);
   }

   public function notFound(FunctionalTester $I)
   {
      $I->sendGET($I->grabRoute('conclusion.get', ['conclusion_id' => Uuid::uuid4()]));
      $I->seeResponseCodeIs(404);
   }
}
