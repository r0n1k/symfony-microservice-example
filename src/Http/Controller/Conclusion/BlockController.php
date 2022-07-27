<?php

namespace App\Http\Controller\Conclusion;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Repository\Conclusion\Paragraph\Block\BlockRepository;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Create;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Resort;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\Delete;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetKind;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetState;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetExecutor;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\SetCustomValue;
use App\Domain\Project\UseCase\Conclusion\Paragraph\Block\RemoveCustomValue;
use App\Http\Controller\ApiController;
use App\Http\Formatter\CustomJsonResponse;
use App\Http\Formatter\UnformattedResponse;
use App\Http\Services\DTOBuilder\DTOBuilder;
use App\Services\Authentication\Voter\ConclusionAccess;
use App\Services\Authentication\Voter\ParagraphAccess;
use App\Services\SiteEnvResolver;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @noinspection PhpUnused
 */

class BlockController extends ApiController
{

    /**
     * @OA\Get(
     *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block/{block_id}",
     *    summary="Получать блока",
     *    tags={"Conclusion Block"},
     *
     *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionId")
     *    ),
     *    @OA\Parameter(name="paragraph_id", in="path", description="ID раздела", required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionParagraphId")
     *    ),
     *    @OA\Parameter(name="block_id", in="path", description="ID блока", required=true,
     *       @OA\Schema(type="integer"),
     *    ),
     *
     *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
     *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionBlock")),
     *    })),
     * )
     * @Route(
     *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block/{block_id}",
     *    name="conclusion.paragraph.block.get",
     *    methods={"GET"}
     * )
     * @Entity("conclusion",  options={"id" = "conclusion_id"})
     * @Entity("paragraph",   options={"id" = "paragraph_id"})
     * @Entity("block",       options={"id" = "block_id"})
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpUnused
     *
     * @param SetKind\Handler $handler
     * @param Block           $block
     * @param Paragraph       $paragraph
     * @param Conclusion      $conclusion
     *
     * @return mixed
     */
    public function fetch(
        SetKind\Handler $handler,
        Block $block,
        Paragraph $paragraph,
        Conclusion $conclusion
    )
    {
        // $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на получение блока');

        return $block;
    }


   /**
    * @OA\Post(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block",
    *    summary="Создать блок",
    *    tags={"Conclusion Block"},
    *
    *    @OA\Parameter(
    *       name="conclusion_id",
    *       in="path",
    *       description="ID заключения",
    *       required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *    @OA\Parameter(
    *       name="paragraph_id",
    *       in="path",
    *       description="ID раздела",
    *       required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionParagraphId")
    *    ),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/CreateConclusionBlockDTO")),
    *
    *    @OA\Response(response="201", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionBlock")),
    *    })),
    * )
    *
    * @Route(path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block", methods={"POST"},
    *    name="conclusion.paragraph.block.create"
    * )
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    * @Entity("paragraph", options={"id" = "paragraph_id"})
    *
    * @noinspection PhpUnusedParameterInspection
    * @noinspection PhpUnused
    *
    * @param Create\Handler $handler
    * @param Paragraph $paragraph
    * @param Conclusion $conclusion
    * @param DTOBuilder $builder
    * @return mixed
    */
   public function createBlock(
      Create\Handler $handler,
      Paragraph $paragraph,
      Conclusion $conclusion,
      DTOBuilder $builder
   )
   {
      $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

      /** @var Create\DTO $dto */
      $dto = $builder->buildValidDTO(
         Create\DTO::class,
         ['paragraph_id'],
         ['paragraph_id' => $paragraph->getId()->getValue()]
      );
      return new UnformattedResponse($handler->handle($dto), 201);
   }

    /**
     * @OA\Patch(
     *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/blocks/resort",
     *    summary="Пересортировать блоки",
     *    tags={"Conclusion Block"},
     *
     *    @OA\Parameter(
     *       name="conclusion_id",
     *       in="path",
     *       description="ID заключения",
     *       required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionId")
     *    ),
     *    @OA\Parameter(
     *       name="paragraph_id",
     *       in="path",
     *       description="ID раздела",
     *       required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionParagraphId")
     *    ),
     *
     *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ResortConclusionBlockDTO")),
     *
     *    @OA\Response(response="200", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     * )
     *
     * @Route(path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/blocks/resort", methods={"PATCH"},
     *    name="conclusion.paragraph.blocks.resort"
     * )
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("paragraph", options={"id" = "paragraph_id"})
     *
     * @noinspection PhpUnused
     *
     * @param Resort\Handler $handler
     * @param Paragraph $paragraph
     * @param Conclusion $conclusion
     * @param DTOBuilder $builder
     */
    public function resortBlock(
        Resort\Handler $handler,
        Paragraph $paragraph,
        Conclusion $conclusion,
        DTOBuilder $builder
    )
    {
        $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

        /** @var Resort\DTO $dto */
        $dto = $builder->buildValidDTO(
            Resort\DTO::class,
            ['paragraph_id'],
            ['paragraph_id' => $paragraph->getId()->getValue()]
        );

        $handler->handle($dto);
    }

    /**
     * @OA\Post(
     *    path="/conclusion/{conclusion_id}/blocks/delete",
     *    summary="Удалить блоки",
     *    tags={"Conclusion Block"},
     *
     *    @OA\Parameter(
     *       name="conclusion_id",
     *       in="path",
     *       description="ID заключения",
     *       required=true,
     *       @OA\Schema(ref="#/components/schemas/ConclusionId")
     *    ),
     *
     *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/DeleteBlocksDTO")),
     *
     *    @OA\Response(response="200", description="ok", @OA\JsonContent(ref="#/components/schemas/ApiResponse")),
     * )
     *
     * @Route(
     *     path="/conclusion/{conclusion_id}/blocks/delete",
     *     methods={"POST"},
     *     name="conclusion.paragraph.block.delete"
     * )
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     *
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param Delete\Handler $handler
     * @param DTOBuilder $builder
     *
     * @return array
     */
    public function deleteBlocks(
       Conclusion $conclusion,
       Delete\Handler $handler,
       DTOBuilder $builder
   )
   {
      // $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

      $dto = $builder->buildValidDTO(Delete\DTO::class);

      return $handler->handle($dto);
   }

   /**
    * @OA\Patch(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block/{block_id}/kind",
    *    summary="Изменить тип блока",
    *    tags={"Conclusion Block"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *    @OA\Parameter(name="paragraph_id", in="path", description="ID раздела", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionParagraphId")
    *    ),
    *    @OA\Parameter(name="block_id", in="path", description="ID блока", required=true,
    *       @OA\Schema(type="integer"),
    *    ),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ConclusionBlockSetKindDTO")),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionBlock")),
    *    })),
    * )
    *
    * @Route(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block/{block_id}/kind",
    *    name="conclusion.paragraph.block.kind",
    *    methods={"PATCH"}
    * )
    *
    * @Entity("conclusion",  options={"id" = "conclusion_id"})
    * @Entity("paragraph",   options={"id" = "paragraph_id"})
    * @Entity("block",       options={"id" = "block_id"})
    *
    * @noinspection PhpUnusedParameterInspection
    * @noinspection PhpUnused
    *
    * @param SetKind\Handler $handler
    * @param Block $block
    * @param Paragraph $paragraph
    * @param Conclusion $conclusion
    * @param DTOBuilder $builder
    * @return mixed
    */
   public function setKind(
      SetKind\Handler $handler,
      Block $block,
      Paragraph $paragraph,
      Conclusion $conclusion,
      DTOBuilder $builder
   )
   {
      $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

      /** @var SetKind\DTO $dto */
      $dto = $builder->buildValidDTO(SetKind\DTO::class, ['block_id'], ['block_id' => $block->getId()->getValue()]);
      return $handler->handle($dto);
   }

   /**
    * @OA\Patch(
    *    path="/conclusion/{conclusion_id}/blocks/state",
    *    summary="Изменить статус блоков",
    *    tags={"Conclusion Block"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/ChangeConclusionBlockStateDTO")),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionBlock")),
    *    })),
    * )
    *
    * @Route(path="/conclusion/{conclusion_id}/blocks/state",
    *    name="conclusion.paragraph.blocks.state", methods={"PATCH"}
    * )
    *
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    *
    * @noinspection PhpUnusedParameterInspection
    *
    * @param SetState\Handler $handler
    * @param Conclusion $conclusion
    * @param DTOBuilder $builder
    * @return mixed
    */
   public function setState(
      SetState\Handler $handler,
      Conclusion $conclusion,
      DTOBuilder $builder
   )
   {
      // $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

      /** @var SetState\DTO $dto */
      $dto = $builder->buildValidDTO(SetState\DTO::class);

      return $handler->handle($dto);
   }

   /**
    * @OA\Patch(
    *    path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block/{block_id}/executor",
    *    summary="Изменить исполнителя блока",
    *    tags={"Conclusion Block"},
    *
    *    @OA\Parameter(name="conclusion_id", in="path", description="ID заключения", required=true,
    *       @OA\Schema(ref="#/components/schemas/ConclusionId")
    *    ),
    *    @OA\Parameter(name="paragraph_id", in="path", description="ID раздела", required=true,
    *        @OA\Schema(ref="#/components/schemas/ConclusionParagraphId")
    *    ),
    *    @OA\Parameter(name="block_id", in="path", description="ID блока", required=true,
    *       @OA\Schema(type="integer")
    *    ),
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/SetBlockExecutorDTO")),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *       @OA\Schema(type="object", @OA\Property(property="data", ref="#/components/schemas/ConclusionBlock")),
    *    })),
    * )
    *
    * @Route(path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block/{block_id}/executor",
    *    name="conclusion.paragraph.block.executor", methods={"PATCH"}
    * )
    *
    * @Entity("block", options={"id" = "block_id"})
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    * @Entity("paragraph", options={"id" = "paragraph_id"})
    *
    * @noinspection PhpUnusedParameterInspection
    * @noinspection PhpUnused
    *
    * @param SetExecutor\Handler $handler
    * @param Block $block
    * @param Paragraph $paragraph
    * @param Conclusion $conclusion
    * @param DTOBuilder $builder
    * @return mixed
    */
   public function setExecutor(
      SetExecutor\Handler $handler,
      Block $block,
      Paragraph $paragraph,
      Conclusion $conclusion,
      DTOBuilder $builder
   )
   {
      $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

      /** @var SetExecutor\DTO $dto */
      $dto = $builder->buildValidDTO(SetExecutor\DTO::class, ['block_id'], ['block_id' => $block->getId()->getValue()]);
      return $handler->handle($dto);
   }


   /**
    * @Route(path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block/{block_id}/set-custom-value",
    *    name="conclusion.paragraph.block.set-custom-value", methods={"PUT"}
    * )
    *
    * @Entity("block", options={"id" = "block_id"})
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    * @Entity("paragraph", options={"id" = "paragraph_id"})
    *
    * @noinspection PhpUnusedParameterInspection
    * @noinspection PhpUnused

    * @param SetCustomValue\Handler $handler
    * @param Block $block
    * @param Paragraph $paragraph
    * @param Conclusion $conclusion
    * @param DTOBuilder $builder
    * @return Block
    */
   public function addCustomValue(
      SetCustomValue\Handler $handler,
      Block $block,
      Paragraph $paragraph,
      Conclusion $conclusion,
      DTOBuilder $builder
   )
   {
      $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

      /** @var SetCustomValue\DTO $dto */
      $dto = $builder->buildValidDTO(SetCustomValue\DTO::class, ['block_id'], ['block_id' => $block->getId()->getValue()]);
      return $handler->handle($dto);
   }

   /**
    * @Route(path="/conclusion/{conclusion_id}/paragraph/{paragraph_id}/block/{block_id}/remove-custom-value/{key}",
    *    name="conclusion.paragraph.block.remove-custom-value", methods={"DELETE"}
    * )
    *
    * @Entity("block", options={"id" = "block_id"})
    * @Entity("conclusion", options={"id" = "conclusion_id"})
    * @Entity("paragraph", options={"id" = "paragraph_id"})
    *
    * @noinspection PhpUnusedParameterInspection
    * @noinspection PhpUnused
    *
    * @param RemoveCustomValue\Handler $handler
    * @param Block $block
    * @param Paragraph $paragraph
    * @param Conclusion $conclusion
    * @param DTOBuilder $builder
    * @param string $key
    *
    * @return Block
    */
   public function removeCustomValue(
      RemoveCustomValue\Handler $handler,
      Block $block,
      Paragraph $paragraph,
      Conclusion $conclusion,
      DTOBuilder $builder,
      string $key
   )
   {
      $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

      /** @var RemoveCustomValue\DTO $dto */
      $dto = $builder->buildValidDTOFromArray(RemoveCustomValue\DTO::class, [
         'block_id' => $block->getId()->getValue(),
         'key' => $key,
      ]);
      return $handler->handle($dto);
   }

    /**
     * @Route(path="/conclusion/{conclusion_id}/block/{block_id}/upload-html", methods={"POST"}, name="conclusion.block.upload-html")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("block", options={"id" = "block_id"})
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param Block $block
     * @param Request $request
     * @param BlockRepository $repository
     * @param SiteEnvResolver $hostResolver
     * @param Flusher $flusher
     *
     * @return Block
     * @throws \Exception
     */
    public function addHtml(
        Conclusion $conclusion,
        Block $block,
        Request $request,
        BlockRepository $repository,
        SiteEnvResolver $hostResolver,
        Flusher $flusher
    )
    {
        $paragraph = $block->getParagraph();

        $this->denyAccessUnlessGranted(ParagraphAccess::EDIT_BLOCK, $paragraph, 'Нет доступа на редактирование блока');

        $host = $hostResolver->resolve();
        $storagePath = '/var/www/html/storage/';
        // TODO file path resolver
        $filePath = "{$host}/{$conclusion->getProject()->getId()}/conclusions/{$conclusion->getId()}" .
            "/paragraphs/{$paragraph->getId()->getValue()}/blocks/{$block->getId()->getValue()}/html/";
        /** @var UploadedFile $signatureFile */
        $htmlFile = $request->files->get('file');
        $htmlName = Uuid::uuid4()->toString() . '.html';
        $htmlFile->move($storagePath . $filePath, $htmlName);
        $block->setHtml($filePath . $htmlName);
        $repository->add($block);
        $flusher->flush();

        return $block;
    }

    /**
     * @Route(path="/conclusion/{conclusion_id}/block/{block_id}/download-html", methods={"GET"}, name="conclusion.block.download-html")
     *
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("block", options={"id" = "block_id"})
     *
     * @param Conclusion $conclusion
     * @param Block $block
     */
    public function getHtml(Conclusion $conclusion, Block $block)
    {
        $this->denyAccessUnlessGranted(ConclusionAccess::VIEW, $conclusion, 'Нет доступа на просмотр заключения');
        $storagePath = '/var/www/html/storage/';
        $filePath = $storagePath . $block->getHtml();
        $filename = basename($filePath);
        $mime = mime_content_type($filePath);
        header('Content-Type: ' . ($mime ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        readfile($filePath);
        exit(0);
    }

    /**
     * @Route(path="/conclusion/{conclusion_id}/block/{block_id}/upload-html-image", methods={"POST"}, name="conclusion.block.upload-html-image")
     * @Entity("conclusion", options={"id" = "conclusion_id"})
     * @Entity("block", options={"id" = "block_id"})
     * @noinspection PhpUnused
     *
     * @param Conclusion $conclusion
     * @param Block $block
     * @param Request $request
     * @param SiteEnvResolver $hostResolver
     * @param Flusher $flusher
     *
     * @return CustomJsonResponse
     * @throws \Exception
     */
    public function addImageToBlockHtml(
        Conclusion $conclusion,
        Block $block,
        Request $request,
        SiteEnvResolver $hostResolver
    )
    {
        $host = $hostResolver->resolve();
        $storagePath = '/var/www/html/storage/';
        // TODO file path resolver
        $filePath = "{$host}/{$conclusion->getProject()->getId()}/conclusions/{$conclusion->getId()}" .
            "/paragraphs/{$block->getParagraph()->getId()->getValue()}/blocks/{$block->getId()->getValue()}/html/images/";
        /** @var UploadedFile $htmlImage */
        $htmlImage = $request->files->get('upload');
        $htmlImageName = Uuid::uuid4()->toString() . '.' . $htmlImage->getClientOriginalExtension();
        $htmlImage->move($storagePath . $filePath, $htmlImageName);
        $block->setHtml($filePath . $htmlImageName);

        $protocol = $host === 'localhost'
            ? 'http'
            : 'https';

        return new CustomJsonResponse(
            [
                'url' => "{$protocol}://{$host}/api/conclusions-backend/download-file/{$filePath}{$htmlImageName}"
            ]
        );
    }
}
