<?php

/**
 * @noinspection PhpUnused
 */
namespace App\Tests\Helper;

/*
 * This file is part of the Codeception ApiValidator Module project
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 *
 */

use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use Codeception\Module\REST;
use Codeception\TestInterface;
use ElevenLabs\Api\Decoder\Adapter\SymfonyDecoderAdapter;
use ElevenLabs\Api\Factory\SwaggerSchemaFactory;
use ElevenLabs\Api\Schema;
use ElevenLabs\Api\Validator\MessageValidator;
use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JsonSchema\Validator;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\Serializer\Encoder\ChainDecoder;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Class ApiValidator
 * @package Codeception\Module
 */
class ApiValidator extends Module implements DependsOnModule
{

   protected $config = [
      'schema' => ''
   ];

   protected $dependencyMessage = <<<EOF
Example configuring REST as backend for ApiValidator module.
--
modules:
    enabled:
        - ApiValidator:
            depends: [REST, PhpBrowser]
            schema: '../../web/api/documentation/swagger.yaml'
--
EOF;

   public $isFunctional = false;

   /**
    * @var InnerBrowser
    */
   protected $connectionModule;

   /**
    * @var REST
    */
   public $rest;

   /**
    * @var MessageValidator
    */
   protected $swaggerMessageValidator;

   /**
    * @var Schema
    */
   protected $swaggerSchema;
   /**
    * @var AbstractBrowser
    */
   private $client;
   /**
    * @var array
    */
   private $params;
   /**
    * @var string
    */
   private $response;

   /**
    * @param TestInterface $test
    */
   public function _before(TestInterface $test)
   {
      $this->client = &$this->rest->client;
      $this->resetVariables();
   }

   protected function resetVariables()
   {
      $this->params = [];
      $this->response = '';
      $this->connectionModule->headers = [];
   }

   /**
    * @return array
    */
   public function _depends()
   {
      return [REST::class => $this->dependencyMessage, InnerBrowser::class => 'Inner browser not configured'];
   }

   /**
    * @param REST $rest
    * @param InnerBrowser $connection
    */
   public function _inject(REST $rest, InnerBrowser $connection)
   {
      $this->rest = $rest;
      $this->connectionModule = $connection;

      if ($this->connectionModule instanceof Framework) {
         $this->isFunctional = true;
      }

      $jsonSchemaValidator = new Validator();
      $decoder = new SymfonyDecoderAdapter(
         new ChainDecoder([
            new JsonDecode(),
            new XmlEncoder()
         ])
      );
      $this->swaggerMessageValidator = new MessageValidator($jsonSchemaValidator, $decoder);
      if ($this->config['schema']) {
         $schema = 'file://' . codecept_root_dir($this->config['schema']);
         if (!file_exists($schema)) {
            throw new RuntimeException("{$schema} not found!");
         }
         $this->swaggerSchema = (new SwaggerSchemaFactory())->createSchema($schema);
      }
   }

   /**
    * @param string $schema
    * @throws Exception
    */
   public function haveOpenAPISchema($schema)
   {
      if (!file_exists($schema)) {
         throw new RuntimeException("{$schema} not found!");
      }
      $this->swaggerSchema = (new SwaggerSchemaFactory())->createSchema($schema);

   }

   /**
    * @param $schema
    * @throws Exception
    */
   public function haveSwaggerSchema($schema)
   {
      $this->haveOpenAPISchema($schema);
   }

   /**
    *
    */
   public function seeRequestIsValid()
   {
      $request = $this->getPsr7Request();
      $violations = $this->validateRequestAgainstSchema($request);
      $uri = $request->getUri();
      Assert::assertEmpty($violations, "Request to $uri is invalid: ".json_encode($violations, JSON_THROW_ON_ERROR, 512));
   }

   /**
    *
    */
   public function seeResponseIsValid()
   {
      $request = $this->getPsr7Request();
      $response = $this->getPsr7Response();
      $violations = $this->validateResponseAgainstSchema($request, $response);
      $uri = $request->getUri();
      Assert::assertEmpty($violations, "Response from $uri: ".json_encode($violations, JSON_THROW_ON_ERROR, 512));
   }

   /**
    *
    */
   public function seeRequestAndResponseAreValid()
   {
      $this->seeRequestIsValid();
      $this->seeResponseIsValid();
   }

   /**
    * @param RequestInterface $request
    * @return array|\ElevenLabs\Api\Validator\ConstraintViolation[]
    */
   public function validateRequestAgainstSchema(RequestInterface $request)
   {
      $uri = parse_url($request->getUri())['path'];
      $uri = '/' . ltrim($uri, '/');

      $requestDefinition = $this->swaggerSchema->getRequestDefinition(
         $this->swaggerSchema->findOperationId($request->getMethod(), $uri)
      );

      $this->swaggerMessageValidator->validateRequest($request, $requestDefinition);
      if ($this->swaggerMessageValidator->hasViolations()) {
         codecept_debug($this->swaggerMessageValidator->getViolations());
      }
      $result = [];
      foreach ($this->swaggerMessageValidator->getViolations() as $violation) {
         $result[] = $violation->toArray();
      }
      return $result;
   }

   /**
    * @param RequestInterface $request
    * @param ResponseInterface $response
    * @return array|\ElevenLabs\Api\Validator\ConstraintViolation[]
    */
   public function validateResponseAgainstSchema(RequestInterface $request, ResponseInterface $response)
   {
      $uri = parse_url($request->getUri())['path'];
      $uri = '/' . ltrim($uri, '/');

      $requestDefinition = $this->swaggerSchema->getRequestDefinition(
         $this->swaggerSchema->findOperationId($request->getMethod(), $uri)
      );

      $headers = $response->getHeaders();
      $headers['content-type'] = str_replace('; charset=utf-8', '', $headers['content-type']);
      $response = new Response(
         $response->getStatusCode(),
         $headers,
         $response->getBody()->__toString()
      );

      $this->swaggerMessageValidator->validateResponse($response, $requestDefinition);
      if ($this->swaggerMessageValidator->hasViolations()) {
         codecept_debug($this->swaggerMessageValidator->getViolations());
      }
      $result = [];
      foreach ($this->swaggerMessageValidator->getViolations() as $violation) {
         $result[] = $violation->toArray();
      }
      return $result;
   }

   /**
    * @return RequestInterface
    */
   public function getPsr7Request()
   {
      $internalRequest = $this->rest->client->getInternalRequest();
      $headers = $this->connectionModule->headers;

      return new Request(
         $internalRequest->getMethod(),
         $internalRequest->getUri(),
         $headers,
         $internalRequest->getContent()
      );
   }

   /**
    * @return ResponseInterface
    */
   public function getPsr7Response()
   {
      $internalResponse = $this->rest->client->getInternalResponse();
      return new Response(
         $internalResponse->getStatusCode(),
         $internalResponse->getHeaders(),
         $internalResponse->getContent()
      );
   }
}
