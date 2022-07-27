<?php

namespace App\Domain\Project\Entity\Conclusion;

use Webmozart\Assert\Assert;


/**
 * Class ConclusionFileTypeState
 * @package App\Domain\Entity\Conclusion
 *
 * Статус файла заключения
 */
class FileTypeState
{
    public const PREP_POSITIVE_OPINION = 'prep_positive_opinion';
    public const POSITIVE_OPINION = 'positive_opinion';
    public const PREP_NEGATIVE_OPINION = 'prep_negative_opinion';
    public const NEGATIVE_OPINION = 'negative_opinion';

    /**
     * @OA\Schema(schema="ConclusionFileTypeState", type="string", enum={
     *    "prep_positive_opinion",
     *    "positive_opinion",
     *    "prep_negative_opinion",
     *    "negative_opinion"
     * })
     */
    protected string $fileTypeState;

    public function __construct(string $fileTypeState)
    {
        Assert::oneOf(
            $fileTypeState,
            [
                self::PREP_POSITIVE_OPINION,
                self::POSITIVE_OPINION,
                self::PREP_NEGATIVE_OPINION,
                self::NEGATIVE_OPINION,
            ]
        );

        $this->fileTypeState = $fileTypeState;
    }

    public function getValue()
    {
        return $this->fileTypeState;
    }

    public function __toString()
    {
        return $this->fileTypeState;
    }
}
