<?php
namespace App\Tests\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\Common\Service\BlockFilePathResolverInterface;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\FilePath;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\Role;
use App\Domain\Project\Entity\Users\User\User;
use App\Tests\Helper\MockClasses\TestFilePathResolver;
use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Module\Doctrine2;
use Codeception\Module\REST;
use Codeception\Module\Symfony;
use Codeception\TestInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Kernel;

class Functional extends Module
{

   /**
    * @var string
    */
   public string $latestPath = '';
   /**
    * @var TestFilePathResolver
    */
   private TestFilePathResolver $filePathResolver;

   public function __construct(ModuleContainer $moduleContainer, $config = null)
   {
      if (!$moduleContainer->hasModule('Doctrine2')) {
         throw new RuntimeException('Functional helper requires module Doctrine2 to be configured');
      }
      parent::__construct($moduleContainer, $config);
   }

   public function _before(TestInterface $test)
   {
      /** @var REST $rest */
      $rest = $this->getModule('REST');
      $rest->haveHttpHeader('Content-Type', 'application/json');
      $this->latestPath = '';
      /** @var Symfony $symfony */
      $symfony = $this->getModule('Symfony');
      $this->filePathResolver = $symfony->grabService(BlockFilePathResolverInterface::class);
      $this->filePathResolver->setTester($this);
   }

   public function _setLatestResolvedBlockFilePath($path) {
      $this->latestPath = $path;
   }

   public function getLatestDocumentCreatedPath()
   {
      return $this->latestPath;
   }

   public function resolveFilePath(Block $block): FilePath
   {
      return $this->filePathResolver->resolve($block);
   }

   /**
    * @param \App\Domain\Project\Entity\Users\User\User $user
    * @param \App\Domain\Project\Entity\Project\Project $project
    * @param string $role
    * @return ProjectUserAssignment
    * @throws ModuleException
    * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
    */
   public function assignUserToProject(User $user, Project $project, $role = 'expert'): ProjectUserAssignment
   {
      $userAssignment = new ProjectUserAssignment($project, $user, new Role($role));

      /** @var Doctrine2 $doctrineModule */
      $doctrineModule = $this->getModule('Doctrine2');

      $doctrineModule->haveInRepository($user);
      $doctrineModule->haveInRepository($project);

      return $userAssignment;
   }

   /**
    * @return Kernel
    * @throws ModuleException
    */
   public function getSymfonyKernel(): Kernel
   {
      /** @var Symfony $symfony */
      $symfony = $this->getModule('Symfony');

      return $symfony->kernel;
   }

   public function grabRoute($routeName, $params = []): string {
      /** @var Symfony $symfony */
      $symfony = $this->getModule('Symfony');

      $router = $symfony->grabService('router');
      if (!$router->getRouteCollection()->get($routeName)) {
         $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
      }
      return $router->generate($routeName, $params);
   }


}

