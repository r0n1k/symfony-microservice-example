<?php


namespace App\Domain\Project\Entity\Users\User;

use Webmozart\Assert\Assert;
use OpenApi\Annotations as OA;

class Email
{

   /**
    * @var string
    * @OA\Schema(schema="UserEmail", type="string", format="email")
    */
   protected string $email;

   public function __construct(string $email)
   {
      Assert::email($email);
      $this->email = $email;
   }

   public function getValue(): string
   {
      return $this->email;
   }

   public function __toString()
   {
      return $this->email;
   }
}
