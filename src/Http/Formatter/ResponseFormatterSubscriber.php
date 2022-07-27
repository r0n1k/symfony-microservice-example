<?php

namespace App\Http\Formatter;

use App\Http\Formatter\Objects\Trace;
use App\Http\Services\DTOBuilder\InvalidDTOException;
use App\Services\ErrorsCollection\ErrorsCollection;
use DomainException;
use InvalidArgumentException;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ResponseFormatterSubscriber implements EventSubscriberInterface
{
   /**
    * @var ErrorsCollection
    */
   private ErrorsCollection $errors;
   /**
    * @var LoggerInterface
    */
   private LoggerInterface $logger;
   /**
    * @var GeneralFormatter
    */
   private GeneralFormatter $formatter;

   public function __construct(
      LoggerInterface $logger,
      GeneralFormatter $formatter,
      ErrorsCollection $errors)
   {
      $this->errors = $errors;
      $this->logger = $logger;
      $this->formatter = $formatter;
   }

   public static function getSubscribedEvents()
   {
      return [
         'kernel.view' => 'handleEvent',
         'kernel.exception' => 'handleEvent',
      ];
   }

   /**
    * @param RequestEvent $event
    * @noinspection PhpUnused
    */
   public function handleEvent(RequestEvent $event)
   {
      if ($event instanceof ViewEvent) {
         $result = $event->getControllerResult();
         $statusCode = 200;
         $state = 'ok';
      } elseif ($event instanceof ExceptionEvent) {
         $result = $event->getThrowable();
         $statusCode = 500;
         $state = 'error';
      } else {
         return;
      }
      $errors = [];

      if ($result instanceof CustomJsonResponse) {
          $event->setResponse(new JsonResponse($result->getResponse(), $result->getStatusCode()));
          return;
      } else if ($result instanceof UnformattedResponse) {
         $data = $result->getData();
         $statusCode = $result->getStatusCode();

      } elseif ($result instanceof InvalidDTOException) {
         $statusCode = 400;
         $data = $result->getViolations();

      } elseif ($result instanceof Throwable) {
         if ($result instanceof HttpException) {
            $statusCode = $result->getStatusCode();
         } elseif ($result instanceof DomainException || $result instanceof InvalidArgumentException) {
            $statusCode = 400;
         }
         $errors[] = $error = get_class($result) . ': ' . $result->getMessage();
         $this->logger->error('Got error: ' . $error);
         $stacktrace = $this->formatter->format(new Trace($result->getTrace()));
         $data = null;

      } else {
         $data = $result;
      }

      $formatted = $this->formatter->format($data);
      if ($statusCode >= 400) {
         $state = 'error';
      }

      foreach ($this->errors as $key => $value) {
         $errors[$key] = $value;
         $this->logger->error('Got error: ' . $value);
      }

      /**
       * @OA\Schema(schema="ApiResponse",
       *    @OA\Property(property="state", type="string", enum={"ok", "error"}, description="Ok or failed request"),
       *    @OA\Property(property="data"),
       *    @OA\Property(property="errors", nullable=true, type="array", @OA\Items(type="string")),
       * )
       */
      $responseBody = [
         'state' => $state,
         'data' => $formatted,
         'errors' => $errors,
      ];

      if (isset($stacktrace)) {
         $responseBody['stacktrace'] = $stacktrace;
         /** @noinspection JsonEncodingApiUsageInspection */
         $this->logger->error('Stacktrace is: ' . json_encode($stacktrace));
      }

      $event->setResponse(new JsonResponse($responseBody, $statusCode));
   }

}
