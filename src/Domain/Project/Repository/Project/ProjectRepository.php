<?php


namespace App\Domain\Project\Repository\Project;

use App\Domain\Common\EntityNotFoundException;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Project\Id;
use App\Domain\Project\Entity\Project\Project;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Webmozart\Assert\Assert;

/**
 * Class ProjectRepository
 * @package App\Project
 */
class ProjectRepository
{

   /**
    * @var EntityManagerInterface
    */
   private $em;
   /** @var ObjectRepository */
   private $repo;

   public function __construct(EntityManagerInterface $em)
   {
      $this->repo = $em->getRepository(Project::class);
      $this->em = $em;
   }

   public function find(Id $id): ?Project
   {
      /**
       * @var Project $project
       */
      $project = $this->repo->find($id->getValue());

      return $project;
   }

   public function add(Project $project)
   {
      $this->em->persist($project);
   }

   public function remove(Project $project)
   {
      $this->em->remove($project);
   }

   public function exists(Id $id): bool
   {
      return $this->find($id) instanceof Project;
   }

   public function get(Id $project_id): Project
   {
      $project = $this->find($project_id);
      if (!$project_id) {
         throw new EntityNotFoundException('Project not found');
      }

      return $project;
   }

   public function findAll()
   {
      return $this->repo->findAll();
   }

    public function getByBlock(Block $block): Project
    {
       return $block->getParagraph()->getConclusion()->getProject();
    }
}
