<?php


namespace App\Domain\Project\Repository\Conclusion\Pdf;

use App\Domain\Common\EntityNotFoundException;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Template\Entity\Template;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ObjectRepository;

class PdfRepository
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
      $this->repo = $em->getRepository(Pdf::class);
      $this->em = $em;
   }

   public function add(Pdf $pdf)
   {
      $this->em->persist($pdf);
   }

   public function remove(Pdf $pdf)
   {
      $this->em->remove($pdf);
   }
}
