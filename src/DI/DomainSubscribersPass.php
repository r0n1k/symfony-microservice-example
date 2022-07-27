<?php

namespace App\DI;


use App\Services\DomainEvents\AppDomainEventDispatcher;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DomainSubscribersPass implements CompilerPassInterface
{

   /**
    * @inheritDoc
    */
   public function process(ContainerBuilder $container)
   {
      $domainSubscriberIds = array_keys($container->findTaggedServiceIds('domain.subscriber'));
      $domainSubscribers = array_map(static function($id) {
         return new Reference($id);
      }, $domainSubscriberIds);

      $definition = $container->getDefinition(AppDomainEventDispatcher::class);
      $definition->addMethodCall('addDomainSubscribers', [$domainSubscribers, 'invoke']);
      $container->setDefinition(AppDomainEventDispatcher::class, $definition);
   }
}
