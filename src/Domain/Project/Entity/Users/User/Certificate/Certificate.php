<?php


namespace App\Domain\Project\Entity\Users\User\Certificate;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Certificate
 * @package App\Domain\Entity\Certificate
 *
 * @ORM\Entity()
 * @ORM\Table(name="user_certificates")
 */
class Certificate
{

   /**
    * @ORM\Id
    * @ORM\Column(type="user_certificate_id")
    */
   protected Id $id;


   /**
    * @ORM\Column(type="user_certificate_scope")
    */
   protected Scope $scope;

   public function __construct(Id $id, Scope $scope)
   {
      $this->id = $id;
      $this->scope = $scope;
   }

   public function getId(): Id
   {
      return $this->id;
   }

   public function getScope(): Scope
   {
      return $this->scope;
   }

}
