<?php


namespace App\Domain\Template\Repository;


use App\Domain\Common\EntityNotFoundException;
use App\Domain\Template\Entity\Template;
use App\Domain\Template\TemplateFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use DomainException;

class TemplateRepository
{

   /**
    * @var EntityManagerInterface
    */
   private $em;
   /**
    * @var ObjectRepository
    */
   private $repo;
   /**
    * @var TemplateFileRepository
    */
   private $fileRepo;

   public function __construct(EntityManagerInterface $em, TemplateFileRepository $fileRepo)
   {
      $this->repo = $em->getRepository(Template::class);
      $this->em = $em;
      $this->fileRepo = $fileRepo;
   }

   public function find(string $id)
   {
      return $this->fileRepo->find($id) ?? $this->repo->find($id);
   }

   /**
    * @return Template[]
    */
   public function findAll(): array
   {
      /** @var Template[] $result */
      $result = array_merge($this->repo->findAll(), $this->fileRepo->findAll());
      return $result;
   }

   public function get(string $id): Template
   {
      $template = $this->find($id);
      if (!$template instanceof Template) {
         throw new EntityNotFoundException("Template with id `${id}` is not found");
      }
      return $template;
   }

   public function add(Template $template)
   {
      if ($template->getIsBasic()) {
         throw new DomainException('Cannot persist basic templates');
      }

      $this->em->persist($template);
   }

    public function remove(Template $template)
    {
       if ($template->getIsBasic()) {
          throw new DomainException('Cannot remove basic templates');
       }

       $this->em->remove($template);
    }

}
