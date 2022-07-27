<?php


namespace App\Http\Services\DTOBuilder;

use LogicException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidDTOException extends LogicException
{
   /**
    * @var ConstraintViolationListInterface
    */
   protected $violations;

   public function __construct(ConstraintViolationListInterface $violations)
   {
      parent::__construct('', 0, null);
      $this->violations = $violations;
   }

   public function getViolations(): ConstraintViolationListInterface
   {
      return $this->violations;
   }
}
