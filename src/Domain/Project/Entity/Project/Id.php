<?php


namespace App\Domain\Project\Entity\Project;

use Exception;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;
use OpenApi\Annotations as OA;

class Id
{
   /**
    * @var string uuid
    * @OA\Schema(schema="ProjectId", type="string", format="uuid")
    */
   private $value;

   public function __construct($uuid)
   {
      Assert::notEmpty($uuid);
      Assert::uuid($uuid);
      $this->value = $uuid;
   }

   /**
    * @return Id
    * @throws Exception
    */
   public static function next()
   {
      return new self(Uuid::uuid4()->toString());
   }

   /**
    * @return string uuid
    */
   public function getValue()
   {
      return $this->value;
   }

   public function __toString()
   {
      return $this->value;
   }
}
