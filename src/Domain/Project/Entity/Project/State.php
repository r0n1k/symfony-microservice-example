<?php


namespace App\Domain\Project\Entity\Project;

use Webmozart\Assert\Assert;
use OpenApi\Annotations as OA;

/**
 * Class ProjectState
 * @package App\Domain\Project\Entity\Project
 */
class State
{

   public const DEFAULT = 'default';
   public const SUBMITTED = 'submitted';
   public const RESOLVING_VERIFIER_ISSUES = 'resolving_submitted_issues';
   public const EXPERTS_WIP = 'experts_wip';
   public const CONTRACT_SIGNING = 'contract_signing';           // подписание договора
   public const RESOLVING_EXPERTISE_ISSUES = 'resolving_expertises';       // устранение замечаний
   public const PREPARE_TO_EGRZ = 'prepare_to_egrz';            // подготовка к передаче в ЕГРЗ
   public const TRANSFER_TO_EGRZ = 'transfer_to_egrz';           // передача в ЕГРЗ
   public const REPLY_IN_PROGRESS = 'reply_in_progress';           // ожидание ответа ЕГРЗ
   public const POSITIVE_ANSWER = 'positive_answer';            // положительный ответ ЕГРЗ
   public const NEGATIVE_ANSWER = 'negative_answer';            // отрицательный ответ ЕГРЗ
   public const COMPLETED = 'completed';                  // заключение выдано
   public const COMPLETED_POSITIVE = 'completed_positive';         // выдано положительное заключение
   public const COMPLETED_NEGATIVE = 'completed_negative';         // выдано отрицательное заключение
   public const COMPLETED_FIRED = 'completed_fired';            // договор расторгнут
   public const DELETED = 'deleted';                    // проект удалён
   public const CLIENT_DELETED = 'client_deleted';             // проект удалён клиентом

   /**
    * @OA\Schema(schema="ProjectState", type="string", enum={
    *    "default",
    *    "submitted",
    *    "resolving_submitted_issues",
    *    "experts_wip",
    *    "contract_signing",
    *    "resolving_expertises",
    *    "prepare_to_egrz",
    *    "transfer_to_egrz",
    *    "reply_in_progress",
    *    "positive_answer",
    *    "negative_answer",
    *    "completed",
    *    "completed_positive",
    *    "completed_negative",
    *    "completed_fired",
    *    "deleted",
    *    "client_deleted",
    * })
    * @var string
    */
   protected $value;

   public function __construct(string $state)
   {
      Assert::notEmpty($state);
      Assert::oneOf($state, [
         self::DEFAULT,
         self::SUBMITTED,
         self::RESOLVING_VERIFIER_ISSUES,
         self::EXPERTS_WIP,
         self::CONTRACT_SIGNING,
         self::RESOLVING_EXPERTISE_ISSUES,
         self::PREPARE_TO_EGRZ,
         self::TRANSFER_TO_EGRZ,
         self::REPLY_IN_PROGRESS,
         self::POSITIVE_ANSWER,
         self::NEGATIVE_ANSWER,
         self::COMPLETED,
         self::COMPLETED_POSITIVE,
         self::COMPLETED_NEGATIVE,
         self::COMPLETED_FIRED,
         self::DELETED,
         self::CLIENT_DELETED,
      ]);

      $this->value = $state;
   }

   public function getValue(): ?string
   {
      return $this->value;
   }

   public function __toString()
   {
      return $this->value ?? '';
   }
}
