<?php


namespace App\Http\Formatter\Formatters\User;

use App\Domain\Project\Entity\Users\User\User;
use App\Http\Formatter\Base\FormatEvent;
use App\Http\Formatter\Base\EntityFormatter;
use OpenApi\Annotations as OA;

/**
 * @noinspection PhpUnused
 */
class UserFormatter extends EntityFormatter
{

   /**
    * @OA\Schema(schema="User", type="object",
    *    @OA\Property(property="id", type="integer", nullable=false),
    *    @OA\Property(property="full_name", type="string", description="Full Name"),
    *    @OA\Property(property="email", ref="#/components/schemas/UserEmail"),
    *    @OA\Property(property="role", ref="#/components/schemas/UserRole"),
    *    @OA\Property(property="certificates", type="array", @OA\Items(ref="#/components/schemas/Certificate"))
    * )
    * @param User $user
    * @return array
    */
   public function format($user)
   {

      return [
         'id' => $user->getId()->getValue(),
         'full_name' => $user->getFullName(),
         'email' => (string)$user->getEmail(),
         'role' => (string)$user->getRole(),
         'certificates' => $user->getCertificates(),
      ];
   }

   /**
    * @inheritDoc
    */
   protected function supports(FormatEvent $event): bool
   {
      return $event->getFormattableData() instanceof User;
   }
}
