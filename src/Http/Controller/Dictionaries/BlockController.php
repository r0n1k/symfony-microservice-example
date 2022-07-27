<?php

namespace App\Http\Controller\Dictionaries;


use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\UseCase\Dictionary\SetBlocksDictionaries\DTO;
use App\Domain\Project\UseCase\Dictionary\SetBlocksDictionaries\Handler;
use App\Http\Formatter\Objects\DictionaryCollection;
use App\Http\ReadModel\BlockDictionariesFetcher;
use App\Http\Services\DTOBuilder\DTOBuilder;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\Routing\Annotation\Route;

/** @noinspection PhpUnused */

class BlockController
{

   /**
    * @var BlockDictionariesFetcher
    */
   private BlockDictionariesFetcher $fetcher;

   public function __construct(BlockDictionariesFetcher $fetcher)
   {
      $this->fetcher = $fetcher;
   }

   /**
    * @OA\Put(
    *    path="/dictionary/block/{block_id}/set-dictionaries",
    *    tags={"Dictionaries"},
    *    summary="Указать перечень словарей для блока",
    *
    *    @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/SetBlocksDictionariesDTO")),
    *
    *    @OA\Response(response="200", description="ok", @OA\JsonContent(allOf={
    *       @OA\Schema(ref="#/components/schemas/ApiResponse"),
    *    }))
    * )
    *
    * @Route(path="/dictionary/block/{block_id}/set-dictionaries", name="dictionary.block.set-dictionaries")
    * @Entity("block", options={"id" = "block_id"})
    *
    * @noinspection PhpUnused
    *
    * @param DTOBuilder $builder
    * @param Handler $handler
    * @param Block $block
    * @return DictionaryCollection
    */
   public function setBlocksDictionaries(DTOBuilder $builder, Handler $handler, Block $block)
   {
      /** @var DTO $dto */
      $dto = $builder->buildValidDTO(DTO::class, ['block_id'], ['block_id' => $block->getId()->getValue()]);
      $handler->handle($dto);

      return $this->fetcher->fetch($block);
   }

}
