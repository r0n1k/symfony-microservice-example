<?php


namespace App\Domain\Project\Entity\Users\User;

use Webmozart\Assert\Assert;
use OpenApi\Annotations as OA;

class Role
{

   /**
    * @var string
    * @OA\Schema(schema="UserRole", type="string", enum={
    *    "client",
    *    "verifier",
    *    "admin",
    *    "expert",
    *    "project_manager",
    * })
    */
   protected $value;

   public const CLIENT = 'client';
   public const VERIFIER = 'verifier';
   public const ADMIN = 'admin';
   public const EXPERT = 'expert';
   public const PROJECT_MANAGER = 'project_manager';

   public function __construct(string $role)
   {
      Assert::notEmpty($role);
      Assert::oneOf($role, [
         self::CLIENT,
         self::VERIFIER,
         self::ADMIN,
         self::EXPERT,
         self::PROJECT_MANAGER,
      ]);

      $this->value = $role;
   }

    public static function admin()
    {
       return new self(self::ADMIN);
    }

   public static function expert()
   {
      return new self(self::EXPERT);
   }

    public static function client()
    {
        return new self(self::CLIENT);
    }


    public function getValue(): ?string
   {
      return $this->value;
   }

   public function __toString()
   {
      return $this->value ?: '';
   }

   public function isAdmin(): bool
   {
      return $this->value === self::ADMIN;
   }

   public function isProjectManager(): bool
   {
      return $this->value === self::PROJECT_MANAGER;
   }

    public function isClient()
    {
       return $this->value === self::CLIENT;
    }
}
