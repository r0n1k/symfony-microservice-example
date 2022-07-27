<?php
namespace App\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\Project\Entity\Users\User\Role;
use App\Domain\Project\Entity\Users\User\User;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Module\REST;
use Firebase\JWT\JWT;

class JWTAuthenticator extends Module implements DependsOnModule
{

   /**
    * @var DataFactory
    */
   public DataFactory $dataFactory;
   /**
    * @var REST
    */
   public REST $rest;

   public function __construct(ModuleContainer $moduleContainer, $config = null)
   {
      if (!$moduleContainer->hasModule('REST')) {
         throw new \RuntimeException('REST module is required for JWTAuthenticator module');
      }
      parent::__construct($moduleContainer, $config);
   }

   public function _depends()
   {
      return [REST::class => 'REST required', DataFactory::class => 'DataFactory required'];
   }

   /** @noinspection PhpUnused */
   public function _inject(REST $rest, DataFactory $dataFactory)
   {
      $this->rest = $rest;
      $this->dataFactory = $dataFactory;
   }

   protected function generateJWTToken(User $user)
   {
      return JWT::encode([
         'user_id' => $user->getId()->getValue(),
         'full_name' => $user->getFullName()->getValue(),
         'role' => $user->getRole()->getValue(),
         'email' => $user->getEmail()->getValue(),
      ], hash('sha256', $_ENV['SECRET']));
   }

   public function amLoggedInAs(User $user)
   {
      $rest = $this->rest;
      $jwt = $this->generateJWTToken($user);
      $rest->haveHttpHeader('Authentication', $jwt);
   }

   public function amLoggedInWithRole(Role $role)
   {
      /** @var User $user */
      $user = $this->dataFactory->have(User::class, ['role' => $role]);
      $this->amLoggedInAs($user);

      return $user;
   }

   /**
    * @return User
    */
   public function amLoggedIn(): User
   {
      $rest = $this->rest;
      $dataFactory = $this->dataFactory;

      /** @var User $user */
      $user = $dataFactory->have(User::class);
      $jwt = $this->generateJWTToken($user);
      $rest->haveHttpHeader('Authentication', $jwt);

      return $user;
   }

}
