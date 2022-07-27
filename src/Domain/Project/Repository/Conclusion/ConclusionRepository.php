<?php


namespace App\Domain\Project\Repository\Conclusion;

use App\Domain\Common\EntityNotFoundException;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Template\Entity\Template;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ObjectRepository;

class ConclusionRepository
{

   /**
    * @var ObjectRepository
    */
   private $repo;
   /**
    * @var EntityManagerInterface
    */
   private $em;

   public function __construct(EntityManagerInterface $em)
   {
      $this->repo = $em->getRepository(Conclusion::class);
      $this->em = $em;
   }

   public function add(Conclusion $conclusion)
   {
      $this->em->persist($conclusion);
   }

   public function remove(Conclusion $conclusion)
   {
      $this->em->remove($conclusion);
   }

   public function find($conclusion_id): ?Conclusion
   {
      /** @var Conclusion|null $conclusion */
      $conclusion = $this->repo->findOneBy(['id' => $conclusion_id]);

      return $conclusion;
   }

    public function get(string $conclusion_id): Conclusion
    {
       $conclusion = $this->find($conclusion_id);
       if (!$conclusion instanceof Conclusion) {
          throw new EntityNotFoundException("Conclusion with id $conclusion_id not found");
       }

       return $conclusion;
    }

    public function findLatestForProject(Project $project): ?Conclusion
    {
       $qb = $this->em->createQueryBuilder()
          ->select('c')
          ->from(Conclusion::class, 'c')
          ->join('c.project', 'p')
          ->where('p.id = :project_id')
          ->orderBy(new OrderBy('c.revision', 'desc'))
          ->setMaxResults(1)
          ->setParameter(':project_id', (string)$project->getId());

       return $qb->getQuery()->getOneOrNullResult();
    }

   /**
    * @param Template $template
    * @return Conclusion[]
    */
    public function findForTemplate(Template $template): array
    {
       return $this->repo->findBy(['templateId' => $template->getId()]);
    }

   /**
    * @param Project $project
    * @return iterable|Conclusion[]
    */
    public function findAllForProject(Project $project): Iterable
    {
       return $this->em->createQueryBuilder()
          ->select('c')
          ->from(Conclusion::class, 'c')
          ->leftJoin('c.project', 'p')
          ->where('p.id = :projectId')
          ->setParameter(':projectId', $project->getId()->getValue())
          ->getQuery()
          ->getResult();
    }

   /**
    * @param Conclusion $conclusion
    * @return Conclusion
    * @noinspection PhpDocMissingThrowsInspection
    */
    public function getPrevious(Conclusion $conclusion): Conclusion
    {
       /** @noinspection PhpUnhandledExceptionInspection */
       return $this->em->createQueryBuilder()
          ->select('c')
          ->from(Conclusion::class, 'c')
          ->leftJoin('c.project', 'p')
          ->where('p.id = :projectId')
          ->andWhere('c.revision = :prevRevision')
          ->setParameter(':projectId', $conclusion->getProject()->getId())
          ->setParameter(':prevRevision', $conclusion->getRevision()->prev())
          ->getQuery()
          ->getSingleResult();
    }

    public function findForProjectAccessibleToClient(Project $project)
    {
       return $this->em->createQueryBuilder()
          ->select('c')
          ->from(Conclusion::class, 'c')
          ->leftJoin('c.project', 'p')
          ->where('p.id = :projectId')
          ->andWhere('c.accessibleToClient = true')
          ->setParameter(':projectId', $project->getId()->getValue())
          ->getQuery()
          ->getResult();
    }

}
