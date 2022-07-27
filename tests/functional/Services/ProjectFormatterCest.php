<?php
/** @noinspection PhpUnused */

namespace App\Tests\Services;

use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\Role;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Users\User\Certificate\Id as CertId;
use App\Domain\Project\Entity\Users\User\Certificate\Scope;
use App\Domain\Project\Entity\Users\User\User;
use App\Http\Formatter\GeneralFormatter;
use App\Services\ErrorsCollection\ErrorsCollection;
use App\Http\Formatter\Formatters\Project\ProjectFormatter;
use App\Http\Formatter\Formatters\User\Certificate\CertificateFormatter;
use App\Http\Formatter\Formatters\User\UserFormatter;
use App\Http\Formatter\ResponseFormatterSubscriber;
use App\Tests\FunctionalTester;
use Codeception\Util\JsonType;
use Doctrine\Common\Annotations\AnnotationReader;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ProjectFormatterCest
{
   /**
    * @var EventDispatcher
    */
   private $dispatcher;
   /**
    * @var ErrorsCollection
    */
   private $errors;

   public function _before(FunctionalTester $I)
   {
      $dispatcher = new EventDispatcher();
      $errors = $I->grabService(ErrorsCollection::class);
      $errors->clear();
      $logger = $I->grabService(LoggerInterface::class);
      $generalFormatter = new GeneralFormatter($dispatcher, $errors, new AnnotationReader());
      $listener = new ResponseFormatterSubscriber($logger, $generalFormatter, $errors);
      $dispatcher->addListener('kernel.view', [$listener, 'handleEvent']);
      $dispatcher->addListener('formatter.format_entity', [new ProjectFormatter(), 'handle']);
      $dispatcher->addListener('formatter.format_entity', [new UserFormatter(), 'handle']);
      $dispatcher->addListener('formatter.format_entity', [new CertificateFormatter(), 'handle']);

      $this->dispatcher = $dispatcher;
   }

   // tests
   public function testFormatterEvent(FunctionalTester $I)
   {

      /**
       * @var User $user
       */
      $user = $I->make(User::class);
      /**
       * @var Project $project
       */
      $project = $I->make(Project::class);

      $userAssignment = new ProjectUserAssignment($project, $user, new Role(Role::EXPERT));

      $certId = new CertId($I->faker()->randomNumber());
      $certificate = new Certificate($certId, new Scope('test'));

      $user->addCertificate($certificate);

      $data = [$project];

      $event = new ViewEvent($I->getSymfonyKernel(), new Request(), HttpKernelInterface::SUB_REQUEST, $data);
      $this->dispatcher->dispatch($event, 'kernel.view');

      $response = $event->getResponse();
      $I->assertInstanceOf(JsonResponse::class, $response);
      $decodedResponse = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
      $I->assertArrayHasKey('data', $decodedResponse);
      $I->assertArrayHasKey('state', $decodedResponse);
      $I->assertEquals('ok', $decodedResponse['state']);
      $jsonType = new JsonType($decodedResponse['data']);

      $matches = $jsonType->matches([
         'id' => 'string:=' . $project->getId(),
         'name' => 'string:=' . $project->getName(),
         'state' => 'string:=' . $project->getState(),
         'userAssignments' => [
            [
               'role' => 'string:=' . $userAssignment->getRole(),
               'user' => [
                  'id' => 'integer:=' . $user->getId(),
                  'full_name' => 'string:=' . $user->getFullName(),
                  'email' => 'string:=' . $user->getEmail(),
                  'role' => 'string:=' . $user->getRole(),
                  'certificates' => [
                     [
                        'scope' => 'string:=' . $certificate->getScope(),
                     ]
                  ]
               ],
            ]
         ]
      ]);
      $I->assertTrue($matches);
   }
}
