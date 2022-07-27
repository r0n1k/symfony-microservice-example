<?php


namespace App\Http\Controller\Certificates;


use App\Domain\Project\Repository\Certificate\CertificateRepository;

class Controller
{

   public function getCertificates(CertificateRepository $certificates)
   {
      return $certificates->all();
   }

}
