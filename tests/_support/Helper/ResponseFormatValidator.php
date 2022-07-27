<?php

namespace App\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Dictionary;
use App\Domain\Project\Entity\Users\User\User;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\ModuleContainer;
use Codeception\Module\REST;
use Codeception\Util\JsonType;
use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;

class ResponseFormatValidator extends \Codeception\Module implements DependsOnModule
{
   /**
    * @var REST
    */
   private $rest;
   /**
    * @var array
    */
   private $userSchema;
   /**
    * @var array
    */
   private $dictionarySchema;
   /**
    * @var array
    */
   private $blockSchema;
   /**
    * @var array
    */
   private $conclusionSchema;

   public function __construct(ModuleContainer $moduleContainer, $config = null)
   {
      if (!$moduleContainer->hasModule('REST')) {
         throw new RuntimeException('REST module is required for JWTAuthenticator module');
      }
      parent::__construct($moduleContainer, $config);

      $this->dictionarySchema = [
         'id' => 'integer',
         'path' => 'string',
         'value' => 'string|null',
      ];

      $this->userSchema = [
         'id' => 'integer',
         'role' => 'string',
         'full_name' => 'string',
         'email' => 'string',
      ];

      $this->blockSchema = [
         'id' => 'integer',
         'kind' => 'string',
//         'executor' => $this->userSchema,
//         'dictionaries' => [$this->dictionarySchema],
//         'file_path' => 'string|null'
      ];

      $this->conclusionSchema = [
         'id' => 'string',
         'revision' => 'integer',
         'project_id' => 'string',
         'author' => ['id' => 'integer'],
         'created_at' => 'integer',
         'template_id' => 'string|null',
      ];
   }

   /**
    * @inheritDoc
    */
   public function _depends()
   {
      return [
         REST::class => 'REST module should be in dependencies of ResponseFormatValidator',
      ];
   }

   public function _inject(REST $rest)
   {
      $this->rest = $rest;
   }

   public function seeResponseIsMatching($class)
   {
      $mapping = [
         Block::class => $this->blockSchema,
         User::class => $this->userSchema,
         Dictionary\Dictionary::class => $this->dictionarySchema,
         Conclusion::class => $this->conclusionSchema,
      ];

      $schema = $mapping[$class] ?? null;
      if ($schema === null) {
         throw new RuntimeException("{$class} data matcher is not defined");
      }

      $this->seeResponseIsMatchingSchema($schema);
   }

   public function seeResponseIsMatchingSchema($schema)
   {
      $matches = (new JsonType($this->_grabResponse()))
         ->matches($schema);

      if ($matches !== true) {
         throw new AssertionFailedError("Failed schema matching: $matches");
      }
   }

   private function _grabResponse()
   {
      return json_decode($this->rest->grabResponse(), true, 512, JSON_THROW_ON_ERROR)['data'];
   }
}
