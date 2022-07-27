<?php


namespace App\Http\Services\Realtime;


use App\Services\SiteEnvResolver;
use App\Services\ServicesUrlManager;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/** @noinspection PhpUnused */
class WebsocketClient implements WebsocketClientInterface
{
   /**
    * @var Client
    */
   private Client $client;
   /**
    * @var string
    */
   private string $wsHost;
   /**
    * @var Request|null
    */
   private ?Request $request;
   /**
    * @var string|null
    */
   private ?string $host;
   /**
    * @var LoggerInterface
    */
   private LoggerInterface $logger;

   /**
    * WebsocketClient constructor.
    * @param Client $client
    * @param ServicesUrlManager $urlManager
    * @param RequestStack $requestStack
    * @param LoggerInterface $logger
    * @param SiteEnvResolver $resolver
    */
   public function __construct(Client $client,
                               ServicesUrlManager $urlManager,
                               RequestStack $requestStack,
                               LoggerInterface $logger,
                               SiteEnvResolver $resolver)
   {
      $this->client = $client;
      $this->wsHost = $urlManager->websocketUrl();
      $this->request = $requestStack->getMasterRequest();
      $this->host = $resolver->resolve();
      $this->logger = $logger;
   }

   public function sendEvent(string $eventName, ?array $data = [], ?array $userIds = null): void
   {
      $this->failOnNoRequest();
      $url = $this->wsHost . '/emit';
      $body = [
         'event_name' => $eventName,
         'data' => $data,
         'xhost' => $this->host,
      ];
      if ($senderId = $this->getSenderId()) {
         $body['sender_id'] = $senderId;
      }
      if ($userIds !== null) {
         $body['user_ids'] = $userIds;
      }

      try {
         $this->client->post($url, [
            RequestOptions::JSON => $body,
         ]);
      } catch (\Throwable $exception) {
         $this->logger->error("Error making curl request to websocket", ['exception' => $exception]);
      }
   }

   private function failOnNoRequest()
   {
      if (!$this->request instanceof Request) {
         throw new RuntimeException('Request object is not available');
      }
   }

   private function getSenderId()
   {
      return $this->request->headers->get('SockId', null);
   }
}
