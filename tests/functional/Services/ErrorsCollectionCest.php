<?php
/** @noinspection PhpUnused */

namespace App\Tests\Services;

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

class ErrorsCollectionCest
{
   /**
    * @var ErrorsCollection
    */
   private ErrorsCollection $errors;
   /**
    * @var EventDispatcher
    */
   private EventDispatcher $dispatcher;

   public function _before(FunctionalTester $I)
   {
      $this->errors = $errors = $I->grabService(ErrorsCollection::class);
      $errors->clear();

      $dispatcher = new EventDispatcher();
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
   public function testErrorsFormatting(FunctionalTester $I)
   {
      $event = new ViewEvent($I->getSymfonyKernel(), new Request(), HttpKernelInterface::SUB_REQUEST, null);

      $errorMessage = $I->faker()->text(32);
      $this->errors->add($errorMessage);

      $this->dispatcher->dispatch($event, 'kernel.view');
      $response = $event->getResponse();
      $I->assertInstanceOf(JsonResponse::class, $response);

      $decodedResponse = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
      $jsonType = new JsonType($decodedResponse);
      $matches = $jsonType->matches([
         'state' => 'string:=ok',
         'errors' => [
            0 => 'string:=' . $errorMessage,
         ],
      ]);
      $I->assertTrue($matches);
   }
}
