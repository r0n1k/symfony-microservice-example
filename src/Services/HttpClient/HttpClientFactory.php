<?php


namespace App\Services\HttpClient;


use App\Services\SiteEnvResolver;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;

/** @noinspection PhpUnused */

class HttpClientFactory
{

   private ?string $host;

   public function __construct(SiteEnvResolver $resolver)
   {
      $this->host = $resolver->resolve();
   }

   public function make(): Client
   {
      $add_header = static function($header, $value) {
         return static function (callable $handler) use ($header, $value) {
            return static function (
               RequestInterface $request,
               array $options
            ) use ($handler, $header, $value) {
               /** @noinspection CallableParameterUseCaseInTypeContextInspection */
               $request = $request->withHeader($header, $value);
               return $handler($request, $options);
            };
         };
      };

      $stack = new HandlerStack();
      $stack->setHandler(new CurlHandler());
      if ($this->host) {
         $stack->push($add_header('X-Host', $this->host));
      }
      return new Client(['handler' => $stack]);
   }

}
