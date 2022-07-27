<?php


namespace App\Domain\Project\Entity\Users\ProjectUserAssignment;

use OpenApi\Annotations as OA;
use Webmozart\Assert\Assert;

class Role
{
   public const EXPERT = 'expert';
   public const MAIN_EXPERT = 'main_expert';

   /**
    * @var string
    * @OA\Schema(schema="UserAssignmentRole", type="string", enum={"expert", "main_expert"})
    */
   protected string $value;

   public function __construct(string $role)
   {
      Assert::oneOf($role, [self::EXPERT, self::MAIN_EXPERT]);
      $this->value = $role;
   }

   public function getValue()
   {
      return $this->value;
   }

   public function __toString()
   {
      return $this->value ?: '';
   }

   public function isMainExpert(): bool
   {
      return $this->value === self::MAIN_EXPERT;
   }

}
