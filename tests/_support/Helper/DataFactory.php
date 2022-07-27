<?php


namespace App\Tests\Helper;


use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Template\Entity\Template;
use App\Domain\Project\Entity\Users\User\User;
use App\Tests\_support\Helper\Factories\BlockFactory;
use App\Tests\_support\Helper\Factories\CertificateFactory;
use App\Tests\_support\Helper\Factories\DictionaryFactory;
use App\Tests\_support\Helper\Factories\ParagraphFactory;
use App\Tests\_support\Helper\Factories\TemplateFactory;
use App\Tests\Helper\Factories\ConclusionFactory;
use App\Tests\Helper\Factories\ProjectFactory;
use App\Tests\Helper\Factories\UserFactory;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DataFactory extends Module implements DependsOnModule
{

   /**
    * @var Module\Doctrine2
    */
   private Module\Doctrine2 $doctrine;

   protected array $mappings = [
      Project::class => ProjectFactory::class,
      User::class => UserFactory::class,
      Conclusion::class => ConclusionFactory::class,
      Block::class => BlockFactory::class,
      Paragraph::class => ParagraphFactory::class,
      Template::class => TemplateFactory::class,
      Dictionary::class => DictionaryFactory::class,
      Certificate::class => CertificateFactory::class,
   ];
   /**
    * @var DomainEvents
    */
   private DomainEvents $domainEvents;

   /**
    * @param array $settings
    * @throws ModuleException
    */
   public function _beforeSuite($settings = [])
   {
      /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
      $this->doctrine = $this->getModule('Doctrine2');
   }

   /**
    * @return ContainerInterface
    * @throws ModuleException
    */
   public function _getContainer(): ContainerInterface
   {
      /** @var Module\Symfony $symfony */
      $symfony = $this->getModule('Symfony');

      return $symfony->_getContainer();
   }

   public function make($classOrEntity, $data = [])
   {
      if (is_string($classOrEntity)) {
         $entity = $this->buildEntity($classOrEntity, $data);
      } else {
         $entity = $classOrEntity;
      }
      $this->domainEvents->clearForEntity($entity);
      return $entity;
   }

   public function have($classOrEntity, $data = [])
   {
      $entity = $this->make($classOrEntity, $data);
      $this->doctrine->haveInRepository($entity);
      $this->domainEvents->clearForEntity($entity);
      return $entity;
   }


   protected function buildEntity($class, $data)
   {
      if (!isset($this->mappings[$class])) {
         throw new \RuntimeException("Cannot build $class, no mapping");
      }
      return $this->mappings[$class]::build($this, $data);
   }

   public function _inject(DomainEvents $events) {
      $this->domainEvents = $events;
   }

   /**
    * @inheritDoc
    */
   public function _depends()
   {
      return [DomainEvents::class => 'DomainEvents needed'];
   }
}
