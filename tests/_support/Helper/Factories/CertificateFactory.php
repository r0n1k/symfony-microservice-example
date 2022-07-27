<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpIllegalPsrClassPathInspection */


namespace App\Tests\_support\Helper\Factories;


use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Users\User\Certificate\Scope;
use App\Domain\Project\Repository\Certificate\CertificateRepository;
use App\Tests\Helper\DataFactory;
use Faker\Factory;

class CertificateFactory
{

   public static function build(DataFactory $factory, $data = [])
   {
      $faker = Factory::create();

      /** @var CertificateRepository $certificateRepository */
      $certificateRepository = $factory->_getContainer()->get(CertificateRepository::class);
      $id = $certificateRepository->nextId();
      $scope = new Scope($faker->text(16));

      return new Certificate($id, $scope);
   }

}
