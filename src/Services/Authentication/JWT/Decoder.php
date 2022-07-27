<?php


namespace App\Services\Authentication\JWT;

use Firebase\JWT\JWT;
use Webmozart\Assert\Assert;

class Decoder
{

   /**
    * @var string
    */
   private $secret;

   public function __construct($secret)
   {
      Assert::notEmpty($secret, 'secret is not defined');
      $this->secret = hash('sha256', $secret);
   }

   /**
    * @param $token
    * @return array
    */
   public function decode($token): array
   {
      return (array)JWT::decode($token, $this->secret, ['HS256']);
   }
}
