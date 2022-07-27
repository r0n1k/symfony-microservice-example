<?php


namespace App\Services\Project;


use App\Domain\Project\UseCase\Project\Upsert\DTO;
use App\Http\Services\DTOBuilder\DTOBuilder;
use App\Services\ServicesUrlManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Fetcher
 * @package App\Services\Project
 */
class ProjectFetcher implements ProjectFetcherInterface
{
   /**
    * @var string
    */
   private string $elexpBackendUrl;
   /**
    * @var DTOBuilder
    */
   private DTOBuilder $builder;
   /**
    * @var Client
    */
   private Client $client;

   public function __construct(ServicesUrlManager $urlManager, DTOBuilder $builder, Client $client)
   {
      $this->elexpBackendUrl = $urlManager->elexpApiUrl();
      $this->builder = $builder;
      $this->client = $client;
   }

   public function fetch(string $project_id): DTO
   {
      $client = $this->client;
      try {
         $url = "{$this->elexpBackendUrl}/internal/project/$project_id";
         $response = $client->get($url);

         $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)['data'];
         /** @var DTO $dto */
         $dto = $this->builder->buildValidDTOFromArray(DTO::class, $data);
         return $dto;
      } catch (RequestException $e) {
         if ($e->getResponse() !== null) {
            $statusCode = $e->getResponse()->getStatusCode();
            throw new HttpException($statusCode, $e->getMessage());
         }

         throw $e;
      }
   }
}
