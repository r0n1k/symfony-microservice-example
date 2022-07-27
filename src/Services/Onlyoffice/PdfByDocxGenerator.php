<?php


namespace App\Services\Onlyoffice;


use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Services\ServicesUrlManager;
use App\Services\SiteEnvResolver;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class PdfByDocxGenerator implements PdfByDocxGeneratorInterface
{

   /**
    * @var ServicesUrlManager
    */
   private ServicesUrlManager $urlManager;
   /**
    * @var Client
    */
   private Client $client;
   /**
    * @var LoggerInterface
    */
   private LoggerInterface $logger;
   private ?string $host;

   public function __construct(ServicesUrlManager $urlManager,
                               Client $client,
                               SiteEnvResolver $xhostResolver,
                               LoggerInterface $logger)
   {
      $this->urlManager = $urlManager;
      $this->client = $client;
      $this->host = $xhostResolver->resolve();
      $this->logger = $logger;
   }

   public function generate(Conclusion $conclusion): ?string {
      $url = $this->urlManager->conclusionsServiceUrl() . "/reports/generate-pdf";
      $savePath = "{$conclusion->getProject()->getId()}/{$conclusion->getId()}/print_form.pdf";
      $docxPath = "{$conclusion->getProject()->getId()}/{$conclusion->getId()}/print_form.docx";
      if ($this->host) {
         $docxPath = "{$this->host}/$docxPath";
      } else {
         $docxPath = "localhost/$docxPath";
      }

      if ($this->host) {
          $savePath = "{$this->host}/$savePath";
      } else {
          $savePath = "localhost/$savePath";
      }

      $key = Uuid::uuid4()->toString();

      $data = [
         'path' => $docxPath,
         'save_path' => $savePath,
         'key' => $key,
      ];

      $rawResponse = $this->client->post($url, [
         RequestOptions::JSON => $data,
         RequestOptions::TIMEOUT => 600,
      ]);

      if ($rawResponse->getStatusCode() !== 201) {
         $this->logger->error('Error creation print form', [
            'request_data' => $data,
            'response_body' => $rawResponse->getBody()->getContents(),
            'response_code' => $rawResponse->getStatusCode(),
            'response_reason' => $rawResponse->getReasonPhrase(),
         ]);
         return null;
      }

      $respone = json_decode($rawResponse->getBody()->getContents(), true, 512, 0 | JSON_THROW_ON_ERROR);
      if (!isset($respone['saved_path'])) {
         return null;
      }
      return $respone['saved_path'];
   }

}
