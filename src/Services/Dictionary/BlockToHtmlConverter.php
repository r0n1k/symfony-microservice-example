<?php


namespace App\Services\Dictionary;


use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\FilePath;
use App\Services\ServicesUrlManager;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/** @noinspection PhpUnused */
class BlockToHtmlConverter implements BlockToHtmlConverterInterface
{

   /**
    * @var Client
    */
   private Client $client;
   /**
    * @var ServicesUrlManager
    */
   private ServicesUrlManager $urlManager;

   public function __construct(Client $client, ServicesUrlManager $urlManager)
   {
      $this->client = $client;
      $this->urlManager = $urlManager;
   }

   public function convert(Block $block): HtmlDTO
   {
      Assert::true($block->getKind()->isText(), 'Cannot get html for non-text block');
      Assert::isInstanceOf($block->getFilePath(), FilePath::class);

      /** @noinspection NullPointerExceptionInspection */
      $path = $block->getFilePath()->getPath();
      $url = $this->urlManager->conclusionsServiceUrl() . '/reports/html';

      $key = Uuid::uuid4()->toString();

      $data = [
         'path' => $path,
         'key' => $key,
      ];

      $response = $this->client->post($url, [
         RequestOptions::JSON => $data,
         RequestOptions::TIMEOUT => 600,
      ]);

      $decodedResponse = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

      return new HtmlDTO($decodedResponse['html'], $decodedResponse['preview_html']);
   }
}
