<?php


namespace App\Services\Authentication\JWT;

use App\Domain\Project\Entity\Users\User\Role;
use App\Services\Authentication\ServiceAccount;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Throwable;
use Webmozart\Assert\Assert;

class Authenticator extends AbstractGuardAuthenticator
{

   /**
    * @var Decoder
    */
   private Decoder $decoder;
   /**
    * @var UserUpserter
    */
   private UserUpserter $upserter;

   public function __construct(Decoder $decoder, UserUpserter $upserter)
   {
      $this->decoder = $decoder;
      $this->upserter = $upserter;
   }

   /**
    * @inheritDoc
    */
   public function start(Request $request, AuthenticationException $authException = null)
   {
      return new Response('No credentials passed', 401);
   }

   /**
    * @inheritDoc
    */
   public function supports(Request $request)
   {
      return true;
   }

   /**
    * @inheritDoc
    */
   public function getCredentials(Request $request)
   {
      $token = $request->headers->get('Authentication');
      try {
         $roles = [
            Role::ADMIN,
            Role::PROJECT_MANAGER,
            Role::EXPERT,
            Role::VERIFIER,
            Role::CLIENT,
         ];
         Assert::notEmpty($token, 'Credentials are not passed');
         $credentials = $this->decoder->decode($token);
         if ($credentials['role'] !== 'service') {
            Assert::integer($credentials['user_id'], 'Wrong user id');
            Assert::stringNotEmpty($credentials['full_name'], 'Wrong full name');
            Assert::oneOf($credentials['role'], $roles, 'Wrong role');
            Assert::email($credentials['email'], 'Wrong email');
         }
         return $credentials;
      } catch (Throwable $e) {
         throw new AuthenticationException("JWT parse error: {$e->getMessage()}");
      }
   }

   /**
    * @inheritDoc
    */
   public function getUser($credentials, UserProviderInterface $userProvider)
   {
      if ($credentials['role'] === 'service') {
         return new ServiceAccount();
      }
      $this->upserter->upsert($credentials);
      return $userProvider->loadUserByUsername($credentials['user_id']);
   }

   /**
    * @inheritDoc
    */
   public function checkCredentials($credentials, UserInterface $user)
   {
      return true;
   }

   /**
    * @inheritDoc
    */
   public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
   {
      return new Response("Not authorized. {$exception->getMessage()}", 401);
   }

   /**
    * @inheritDoc
    */
   public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
   {
      return null;
   }

   /**
    * @inheritDoc
    */
   public function supportsRememberMe()
   {
      return false;
   }
}
