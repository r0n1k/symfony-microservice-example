<?php


namespace App\Http\Formatter\Formatters;


use App\Domain\Project\Entity\Users\User\Id;
use App\Domain\Project\Repository\Users\User\UserRepository;
use App\Http\Formatter\Base\EntityFormatter;
use App\Http\Formatter\Base\FormatEvent;
use App\Services\EntityLogger\Entity\EntityLog;


/** @noinspection PhpUnused */
class LogEntryFormatter extends EntityFormatter
{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @inheritDoc
     * @param EntityLog $logEntry
     */
    public function format($logEntry)
    {
        $user = null;
        try {
            $user = $this->userRepository->get(new Id((int)$logEntry->getUsername()));
        } catch (\Exception $exception) {}


        return [
            'id' => $logEntry->getId(),
            'object_id' => $logEntry->getObjectId(),
            'action' => $logEntry->getAction(),
            'data' => $logEntry->getData(),
            'version' => $logEntry->getVersion(),
            'logged_at' => ($l = $logEntry->getLoggedAt()) ? $l->getTimestamp() : null,
            'user' => $user,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function supports(FormatEvent $event): bool
    {
        return $event->getFormattableData() instanceof EntityLog;
    }
}
