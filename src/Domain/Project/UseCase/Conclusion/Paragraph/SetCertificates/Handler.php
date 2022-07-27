<?php


namespace App\Domain\Project\UseCase\Conclusion\Paragraph\SetCertificates;


use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Id;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Users\User\Certificate\Scope;
use App\Domain\Project\Repository\Certificate\CertificateRepository;
use App\Domain\Project\Repository\Conclusion\Paragraph\ParagraphRepository;
use DomainException;

class Handler
{

   /**
    * @var ParagraphRepository
    */
   private ParagraphRepository $paragraphs;
   /**
    * @var CertificateRepository
    */
   private CertificateRepository $certificates;
   /**
    * @var Flusher
    */
   private Flusher $flusher;

   public function __construct(ParagraphRepository $paragraphs, CertificateRepository $certificates, Flusher $flusher)
   {
      $this->paragraphs = $paragraphs;
      $this->certificates = $certificates;
      $this->flusher = $flusher;
   }

   public function handle(DTO $dto)
   {
      $paragraph = $this->paragraphs->get(Id::of($dto->paragraph_id));

      $paragraph->removeCertificates();

      foreach ($dto->scopes as $scope) {
         $certificate = $this->certificates->findByScope($scope);
         if (!$certificate instanceof Certificate) {
            throw new DomainException("Сертификат $scope не найден");
//            $certificate = new Certificate($this->certificates->nextId(), Scope::of($scope));
//            $this->certificates->add($certificate);
         }
         $paragraph->addCertificate($certificate);
      }

      $this->flusher->flush();
      return $paragraph;
   }

}
