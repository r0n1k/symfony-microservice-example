<?php


namespace App\Http\Services\DTOBuilder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DTOBuilderArgumentResolver implements ArgumentValueResolverInterface
{

   /**
    * @var DTOBuilder
    */
   private $builder;

   public function __construct(DTOBuilder $builder)
   {
      $this->builder = $builder;
   }

   /**
    * @inheritDoc
    */
   public function supports(Request $request, ArgumentMetadata $argument)
   {
      return $argument->getType() === DTOBuilder::class;
   }

   /**
    * @inheritDoc
    */
   public function resolve(Request $request, ArgumentMetadata $argument)
   {
      yield $this->builder->setRequest($request);
   }
}
