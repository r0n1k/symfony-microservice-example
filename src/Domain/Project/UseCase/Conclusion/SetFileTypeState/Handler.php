<?php

namespace App\Domain\Project\UseCase\Conclusion\SetFileTypeState;

use App\Domain\Common\Flusher;
use App\Domain\Project\Entity\Conclusion\FileTypeState;
use App\Domain\Project\Repository\Conclusion\ConclusionRepository;


class Handler
{
    private ConclusionRepository $conclusions;
    private Flusher $flusher;

    public function __construct(ConclusionRepository $conclusions, Flusher $flusher)
    {
        $this->conclusions = $conclusions;
        $this->flusher = $flusher;
    }

    public function handle(DTO $dto)
    {
        $conclusion = $this->conclusions->get($dto->conclusion_id);

        $newState = $dto->file_type_state ? new FileTypeState($dto->file_type_state) : null;
        $conclusion->setFileTypeState($newState);

        $this->flusher->flush();

        return $conclusion;
    }
}
