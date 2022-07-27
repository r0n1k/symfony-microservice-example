<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph\Block;

use OpenApi\Annotations as OA;
use Webmozart\Assert\Assert;

class State
{

   /**
    * @OA\Schema(schema="ConclusionBlockState", type="string", enum={
    *    "waiting_to_start",
    *    "work_in_progress",
    *    "sent_to_review",
    *    "on_review",
    *    "declined",
    *    "completed",
    * })
    * @var string
    */
   protected $value;

   public const WAITING_TO_START = 'waiting_to_start';
   public const WORK_IN_PROGRESS = 'work_in_progress';
   public const SENT_TO_REVIEW = 'sent_to_review';
   public const ON_REVIEW = 'on_review';
   public const DECLINED = 'declined';
   public const COMPLETED = 'completed';
   public const DELETED = 'deleted';

   public function __construct(string $state)
   {
      Assert::oneOf($state, [
         self::WAITING_TO_START,
         self::WORK_IN_PROGRESS,
         self::SENT_TO_REVIEW,
         self::ON_REVIEW,
         self::DECLINED,
         self::COMPLETED,
         self::DELETED
      ]);
      $this->value = $state;
   }

    public static function initial()
    {
       return new self(self::WAITING_TO_START);
    }

    public static function workInProgress()
    {
       return new self(self::WORK_IN_PROGRESS);
    }

    public static function deleted()
    {
       return new self(self::DELETED);
    }


    public function getValue(): ?string
    {
       return $this->value;
    }

   public function __toString()
   {
      return $this->value ?: '';
   }
}
