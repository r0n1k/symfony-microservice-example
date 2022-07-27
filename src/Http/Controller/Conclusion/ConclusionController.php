<?php
namespace App\Http\Controller\Conclusion;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Domain\Project\Entity\Conclusion\Pdf\Signature\Signature;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;
use App\Domain\Project\Repository\Conclusion\Pdf\PdfRepository;
use App\Domain\Project\Repository\Conclusion\Pdf\Signature\SignatureRepository;
use App\Domain\Project\UseCase\Conclusion\AllowToClient;
use App\Domain\Project\UseCase\Conclusion\Create;
use App\Domain\Project\UseCase\Conclusion\Delete;
use App\Domain\Project\UseCase\Conclusion\Rename;
use App\Domain\Project\UseCase\Conclusion\SetComment;
use App\Domain\Project\UseCase\Conclusion\DownloadArchive;
use App\Domain\Project\UseCase\Conclusion\SetFileTypeState;
use App\Domain\Project\UseCase\Conclusion\SetState;
use App\Domain\Project\UseCase\Project\Upsert\Handler as ProjectUpsertHandler;
use App\Http\Controller\ApiController;
use App\Http\Formatter\UnformattedResponse;
use App\Http\Services\DTOBuilder\DTOBuilder;
use App\Services\Authentication\ServiceAccount;
use App\Services\Authentication\UserIdentity;
use App\Services\Authentication\Voter\ConclusionAccess;
use App\Services\Onlyoffice\FinalDocxGenerator;
use App\Services\Onlyoffice\PdfByDocxGeneratorInterface;
use App\Services\Project\ProjectFetcherInterface;
use App\Services\Resumable\ResumableFacade;
use App\Services\SignatureChecker\CryptoService;
use App\Services\SiteEnvResolver;
use GuzzleHttp\Client;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Webmozart\Assert\Assert;


/**
 * @noinspection PhpUnused
 */

class ConclusionController extends ApiController
{

   /**
    * @OA\Post(
    *    path="/project/{project_id}/conclusion",
    *    summary="Создаёт новое заключение. Либо новую ревизию заключения.",
    *    tags={"Conclusion"},
    *
    *    @OA\Parameter(name="project_id", in="path", description="ID проекта", required=true,
    *       @OA\Schema(type="string", format="uuid"),
    *    ),
    *
    *    @OA\RequestBody(
    *       @OA\JsonContent(ref="#/components/schemas/CreateConclusionDTO"),
    *    ),
    *
    *    @OA\Response(response="404", description="Проект не найден"),
    *
    *    @OA\Response(response="201", description="ok",
    *       @OA\JsonContent(allOf={
    *          @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *          @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/Conclusion")),
    *       })
    *    ),
    * )
    *
    * @Route(name="conclusion.create", format="json", path="/project/{project_id}/conclusion", methods={"POST"})
    *
    * @Entity("project", options={"id" = "project_id"})
    *
    * @param Create\Handler $handler
    * @param Project $project
    * @param DTOBuilder $builder
    * @return UnformattedResponse
    */
   public function create(Create\Handler $handler, Project $project, DTOBuilder $builder)
   {
      $identity = $this->getUser();
      if ($identity instanceof ServiceAccount) {
         throw new HttpException(403, 'Access denied');
      }

      if (!$identity instanceof UserIdentity) {
         throw new HttpException(403, 'Not authorized');
      }

      /** @var Create\DTO $dto */
      $dto = $builder->buildValidDTO(
         Create\DTO::class,
         [
            'project_id',
            'author_id',
         ],
         [
            'project_id' => $project->getId()->getValue(),
            'author_id' => $identity->getId(),
         ]
      );

      $conclusion = $handler->handle($dto);
      return new UnformattedResponse($conclusion, 201);
   }

   /**
    * @OA\Get(
    *    path="/conclusion/{conclusion_id}",
    *    summary="Получить заключение",
    *    tags={"Conclusion"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/Conclusion")),
    *    })),
    * )
    *
    * @Route(name="conclusion.get", path="/conclusion/{conclusion_id}", methods={"GET"})
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    *
    * @param Conclusion              $conclusion
    * @param ProjectFetcherInterface $fetcher
    * @param ProjectUpsertHandler    $handler
    * @return Conclusion
    */
   public function fetch(
      Conclusion $conclusion,
      ProjectFetcherInterface $fetcher,
      ProjectUpsertHandler $handler
   )
   {
      $this->denyAccessUnlessGranted(ConclusionAccess::VIEW, $conclusion, 'Нет доступа на просмотр заключения');

      $project_id = $conclusion->getProject()->getId()->getValue();
      $handler->handle($fetcher->fetch($project_id));
      return $conclusion;
   }


   /**
    * @OA\Patch(
    *    path="/conclusion/{conclusion_id}/rename",
    *    summary="Переименовать заключение",
    *    tags={"Conclusion"},
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ConclusionRenameDTO")),
    *
    *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/Conclusion")),
    *    }))
    * )
    *
    * @Route(path="/conclusion/{conclusion_id}/rename", methods={"PATCH"}, name="conclusion.rename")
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    *
    * @param Conclusion $conclusion
    * @param DTOBuilder $builder
    * @param Rename\Handler $handler
    * @return mixed
    */
   public function rename(Conclusion $conclusion, DTOBuilder $builder, Rename\Handler $handler)
   {
      /** @var Rename\DTO $dto */
      $dto = $builder->buildValidDTO(
         Rename\DTO::class,
         ['conclusion_id'],
         ['conclusion_id' => (string)$conclusion->getId()]
      );

      return $handler->handle($dto);
   }


   /**
    * @OA\Delete(
    *    path="/conclusion/{conclusion_id}",
    *    summary="Удалить заключение",
    *    tags={"Conclusion"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *
    *    @OA\Response(response="204", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
    * )
    *
    * @Route(path="/conclusion/{conclusion_id}", methods={"DELETE"}, name="conclusion.delete")
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    *
    * @param Conclusion $conclusion
    * @param Delete\Handler $handler
    * @return mixed
    */
   public function delete(Conclusion $conclusion, Delete\Handler $handler)
   {
      $dto = new Delete\DTO();
      $dto->conclusion_id = (string)$conclusion->getId();

      $handler->handle($dto);
      return new UnformattedResponse(null, 204);
   }


   /**
    * @OA\Put(
    *    path="/conclusion/{conclusion_id}/state",
    *    summary="Изменить статус у заключения",
    *    tags={"Conclusion"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *
    *    @OA\RequestBody(@OA\JsonContent(type="object",
    *       @OA\Property(property="state", type="string", description="Новое состояние"),
    *    )),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
    * )
    *
    * @Route(path="/conclusion/{conclusion_id}/state", methods={"PUT"}, name="conclusion.state")
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    *
    * @param Conclusion $conclusion
    * @param SetState\Handler $handler
    * @param DTOBuilder $builder
    * @return mixed
    */
   public function setState(Conclusion $conclusion, SetState\Handler $handler, DTOBuilder $builder)
   {
      $dto = $builder->buildValidDTO(SetState\DTO::class,
         ['conclusion_id'],
         ['conclusion_id' => $conclusion->getId()->getValue()]
      );
      return $handler->handle($dto);
   }


    /**
     * @OA\Put(
     *    path="/conclusion/{conclusion_id}/file-type-state",
     *    summary="Изменить статус файла у заключения",
     *    tags={"Conclusion"},
     *
     *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionId")
     *    ),
     *
     *    @OA\RequestBody(@OA\JsonContent(type="object",
     *       @OA\Property(property="file_type_state", type="string", description="Новое состояние"),
     *    )),
     *
     *    @OA\Response(response="200", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     * )
     *
     * @Route(path="/conclusion/{conclusion_id}/file-type-state", methods={"PUT"}, name="conclusion.file_type_state")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     *
     * @param Conclusion $conclusion
     * @param SetFileTypeState\Handler $handler
     * @param DTOBuilder $builder
     * @return mixed
     */
    public function setFileTypeState(Conclusion $conclusion, SetFileTypeState\Handler $handler, DTOBuilder $builder)
    {
        $dto = $builder->buildValidDTO(SetFileTypeState\DTO::class,
            ['conclusion_id'],
            ['conclusion_id' => $conclusion->getId()->getValue()]
        );
        return $handler->handle($dto);
    }


    /**
     * @OA\Patch(
     *    path="/conclusion/{conclusion_id}/comment",
     *    summary="Изменить комментарий",
     *    tags={"Conclusion"},
     *
     *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionId")
     *    ),
     *
     *    @OA\RequestBody(@OA\JsonContent(type="object",
     *       @OA\Property(property="comment", type="string", description="Комментарий"),
     *    )),
     *
     *    @OA\Response(response="200", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     * )
     *
     * @Route(path="/conclusion/{conclusion_id}/comment", methods={"PATCH"}, name="conclusion.comment")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     *
     * @param Conclusion $conclusion
     * @param SetFileTypeState\Handler $handler
     * @param DTOBuilder $builder
     * @return mixed
     */
    public function setComment(Conclusion $conclusion, SetComment\Handler $handler, DTOBuilder $builder)
    {
        $dto = $builder->buildValidDTO(SetComment\DTO::class,
            ['conclusion_id'],
            ['conclusion_id' => $conclusion->getId()->getValue()]
        );
        return $handler->handle($dto);
    }


    /**
     * @OA\Get(
     *    path="/conclusion/{conclusion_id}/print_form",
     *    summary="Получить печатную форму",
     *    tags={"Conclusion"},
     *
     *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionId")
     *    ),
     *
     *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *       @OA\Schema(type="object", @OA\Property(property="data", type="object",
     *          @OA\Property(property="saved_path", type="string", description="Путь в конклюжен сервисе")
     *       )),
     *    }))
     * )
     *
     * @Route(path="/conclusion/{conclusion_id}/print_form", methods={"GET"}, name="conclusion.generate")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     *
     * @noinspection PhpUnused
     * @param Conclusion $conclusion
     * @param FinalDocxGenerator $generator
     * @param ConclusionRepository $repository
     * @param Flusher $flusher
     * @return mixed
     */
   public function generateDocx(Conclusion $conclusion,
                                FinalDocxGenerator $generator,
                                ConclusionRepository $repository,
                                Flusher $flusher)
   {
      $generationResult = $generator->generate($conclusion);

      $conclusion->setPrintFormKey($generationResult->key);
      $repository->add($conclusion);
      $flusher->flush();

      return [
         'saved_path' => $generationResult->savedPath,
         'key' => $generationResult->key,
      ];
   }

    /**
     * @OA\Get(
     *    path="/conclusion/{conclusion_id}/pdf",
     *    summary="Получить pdf",
     *    tags={"Conclusion"},
     *
     *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionId")
     *    ),
     *
     *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *       @OA\Schema(type="object", @OA\Property(property="data", type="object",
     *          @OA\Property(property="saved_path", type="string", description="Путь в конклюжен сервисе")
     *       )),
     *    }))
     * )
     * @Route(path="/conclusion/{conclusion_id}/pdf", methods={"GET"}, name="conclusion.generate-pdf")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     *
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param PdfByDocxGeneratorInterface $generator
     * @param ConclusionRepository $repository
     * @param Flusher $flusher
     *
     * @return Pdf
     */
   public function generatePdf(Conclusion $conclusion,
                               PdfByDocxGeneratorInterface $generator,
                               ConclusionRepository $repository,
                               Flusher $flusher)
   {
      $result = $generator->generate($conclusion);

      $pdf = $conclusion->addPdf($result);
      $repository->add($conclusion);
      $flusher->flush();

      return $pdf;
   }

    /**
     * @Route(path="/conclusion/{conclusion_id}/upload-pdf", methods={"POST"}, name="conclusion.upload-pdf")
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @noinspection PhpUnused
     *
     * @param Request $request
     * @param Conclusion $conclusion
     * @param ResumableFacade $resumable
     * @param ConclusionRepository $repository
     * @param Flusher $flusher
     * @param SiteEnvResolver $siteEnvResolver
     *
     * @param Client $httpClient
     * @return Pdf|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
   public function uploadPdf(
       Request $request,
       Conclusion $conclusion,
       ResumableFacade $resumable,
       ConclusionRepository $repository,
       Flusher $flusher,
       SiteEnvResolver $siteEnvResolver,
        Client $httpClient
   )
   {
       $clientFileName = $request->get('fileName');
       $host = $siteEnvResolver->resolve() ?? 'localhost';
       $storagePath = '/var/www/html/storage';
       $uuid = Uuid::uuid4()->toString();
       $uploadDir = "{$storagePath}/{$host}/{$conclusion->getProject()->getId()}/conclusions/{$conclusion->getId()}";

       $extension = $request->get('extension');
       Assert::stringNotEmpty($extension, 'Extension not provided');

       $filePath = $resumable->getUploadedFile($uploadDir, "$uuid.$extension");

       if ($filePath === null) {
           return null;
       }
       $filePath = str_replace("$storagePath/", '', $filePath);

       $pdf = $conclusion->addPdf($filePath, $clientFileName);
       $repository->add($conclusion);
       $flusher->flush();

       if($conclusion->getIsLocal()){
           $body = [
               'uploader_id' => $conclusion->getAuthor()->getId()->getValue(),
               'is_local' => true,
               'project_id' => $conclusion->getProject()->getId()->getValue()
           ];
            $r = $httpClient->request('POST', 'http://api/external/notify', [
                'json' => $body
            ]);
       }

       return $pdf;
   }


    /**
     * @Route(path="/conclusion/{conclusion_id}/pdf/{pdf_id}/download-archive", methods={"GET"}, name="conclusion.download-archive")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("pdf", options={"id" = "pdf_id"})
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param Pdf $pdf
     * @param DTOBuilder $builder
     * @param DownloadArchive\Handler $handler
     */
    public function downloadArchive(Conclusion $conclusion, Pdf $pdf, DTOBuilder $builder, DownloadArchive\Handler $handler)
    {
        $this->denyAccessUnlessGranted(ConclusionAccess::VIEW, $conclusion, 'Нет доступа на просмотр заключения');
        $dto = $builder->buildValidDTO(DownloadArchive\DTO::class,
            ['conclusion_id', 'pdf_id'],
            [
                'conclusion_id' => $conclusion->getId()->getValue(),
                'pdf_id' => $pdf->getId()
            ]
        );
        $handler->handle($dto);
    }


    /**
     * @Route(path="/conclusion/{conclusion_id}/pdf/{pdf_id}/download", methods={"GET"}, name="conclusion.download-pdf")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("pdf", options={"id" = "pdf_id"})
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param Pdf $pdf
     */
   public function getPdf(Conclusion $conclusion, Pdf $pdf)
   {
       $this->denyAccessUnlessGranted(ConclusionAccess::VIEW, $conclusion, 'Нет доступа на просмотр заключения');
       $storagePath = '/var/www/html/storage/';
       $filePath = $storagePath . $pdf->getPath();
       $filename = $pdf->getFileName() ?? basename($pdf->getPath());
       $mime = mime_content_type($filePath);
       header('Content-Type: ' . ($mime ?? 'application/octet-stream'));
       header('Content-Disposition: attachment; filename="' . $filename . '"');

       readfile($filePath);
       exit(0);
   }


    /**
     * @Route(path="/conclusion/{conclusion_id}/pdf/{pdf_id}/change-state/{newState}", methods={"PUT"}, name="conclusion.change-pdf-state")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("pdf", options={"id" = "pdf_id"})
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param Pdf $pdf
     * @param PdfRepository $pdfRepository
     * @param Flusher $flusher
     *
     * @return Pdf
     * @throws \ErrorException
     */
    public function changePdfState(
        Conclusion $conclusion,
        Pdf $pdf,
        PdfRepository $pdfRepository,
        Flusher $flusher,
        string $newState
    )
    {
        $this->denyAccessUnlessGranted(ConclusionAccess::VIEW, $conclusion, 'Нет доступа на просмотр заключения');
        if(!in_array($newState, ['default', 'archived'])){
            throw new \ErrorException("State must be default or archived '{$newState}' given");
        }
        $pdf->setState($newState);
        $pdfRepository->add($pdf);
        $flusher->flush();
        return $pdf;
    }

    /**
     * @Route(path="/conclusion/{conclusion_id}/pdf/{pdf_id}", methods={"DELETE"}, name="conclusion.remove-pdf")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("pdf", options={"id" = "pdf_id"})
     *
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param Pdf $pdf
     * @param PdfRepository $repository
     * @param Flusher $flusher
     */
    public function removePdf(
        Conclusion $conclusion,
        Pdf $pdf,
        PdfRepository $repository,
        Flusher $flusher
    )
    {
        $repository->remove($pdf);
        $conclusion->removePdf($pdf->getId());
        $flusher->flush();

        return new UnformattedResponse(null, 204);
    }

    /**
     * @Route(path="/conclusion/{conclusion_id}/pdf/{pdf_id}/sig", methods={"POST"}, name="conclusion.add-signature")
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("pdf", options={"id" = "pdf_id"})
     * @noinspection PhpUnused
     *
     * @param Request             $request
     * @param Conclusion          $conclusion
     * @param SignatureRepository $repository
     * @param Pdf                 $pdf
     * @param CryptoService       $cryptoService
     * @param SiteEnvResolver     $resolver
     * @param Flusher             $flusher
     *
     * @return Pdf
     */
    public function addSignature(Request $request,
                                 Conclusion $conclusion,
                                 SignatureRepository $repository,
                                 Pdf $pdf,
                                 CryptoService $cryptoService,
                                 SiteEnvResolver $resolver,
                                 Flusher $flusher)
    {
        $host = $resolver->resolve();
        $storagePath = '/var/www/html/storage/';
        $filePath = "{$host}/{$conclusion->getProject()->getId()}/{$conclusion->getId()}/";
        /** @var UploadedFile $signatureFile */
        $signatureFile = $request->files->get('file');
        $sigName = "sig.".$signatureFile->getClientOriginalExtension();
        $signatureFile->move($storagePath . $filePath, $sigName);
        $sigData = $cryptoService->verifySignature($storagePath . $pdf->getPath(), $storagePath . $filePath . $sigName);
        $repository->removeAllByPdf($pdf);
        $signature = new Signature($pdf, $filePath . $sigName, $sigData);
        $repository->add($signature);
        $flusher->flush();
        return $pdf;
    }

    /**
     * @Route(path="/conclusion/{conclusion_id}/allow_to_client", methods={"PUT"}, name="conclusion.allow_to_client")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     *
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param DTOBuilder $builder
     * @param AllowToClient\Handler $handler
     * @return Conclusion
     */
    public function allowToClient(Conclusion $conclusion, DTOBuilder $builder, AllowToClient\Handler $handler)
    {
        $dto = $builder->buildValidDTO(
            AllowToClient\DTO::class,
            ['conclusion_id'],
            ['conclusion_id' => $conclusion->getId()]
        );

        return $handler->handle($dto);
    }
}
