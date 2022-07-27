<?php


namespace App\Services\Onlyoffice;


use App\Services\ServicesUrlManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Webmozart\Assert\Assert;

class DocumentCreator implements DocumentCreatorInterface
{

   /**
    * @var string
    */
   private string $onlyofficeServiceUrl;
   /**
    * @var Client
    */
   private Client $client;

   public function __construct(ServicesUrlManager $urlManager, Client $client)
   {
      $this->onlyofficeServiceUrl = $urlManager->conclusionsServiceUrl();
      $this->client = $client;
   }

   /**
    * @inheritDoc
    */
   public function create($path)
   {
      Assert::notEmpty($path);

      try {
         $response = $this->client->request('POST', "{$this->onlyofficeServiceUrl}/create", [
            RequestOptions::JSON => [
               'path' => $path,
            ],
         ]);
      } catch (RequestException $exception) {
         if (($response = $exception->getResponse()) && $response->getStatusCode() !== 409) {
            throw $exception;
         }
      }

      if (!$response instanceof ResponseInterface || $response->getStatusCode() !== 201) {
         throw new RuntimeException("Error creation onlyoffice document.");
      }
   }
}
