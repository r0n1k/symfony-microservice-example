<?php


namespace App\Domain\Project\Repository\Conclusion\Pdf\Signature;

use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Domain\Project\Entity\Conclusion\Pdf\Signature\Signature;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class SignatureRepository
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
      $this->repo = $em->getRepository(Signature::class);
      $this->em = $em;
   }

   public function add(Signature $pdf)
   {
      $this->em->persist($pdf);
   }

   public function remove(Signature $pdf)
   {
      $this->em->remove($pdf);
   }

   public function removeAllByPdf(Pdf $pdf)
   {
       $this->em->createQueryBuilder()
           ->delete(Signature::class, 's')
           ->where("s.pdf = {$pdf->getId()}")
           ->getQuery()->execute();
   }
}
