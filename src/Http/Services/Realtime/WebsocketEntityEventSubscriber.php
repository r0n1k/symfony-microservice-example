<?php /** @noinspection PhpUnused */


namespace App\Http\Services\Realtime;


use App\Domain\Common\DomainEvent;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Event\Conclusion\ConclusionChanged;
use App\Domain\Project\Event\Conclusion\ConclusionCreated;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockChanged;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockCreated;
use App\Domain\Project\Event\Conclusion\Paragraph\Block\BlockDeleted;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphChanged;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphCreated;
use App\Domain\Project\Event\Conclusion\Paragraph\ParagraphDeleted;
use App\Http\Formatter\GeneralFormatter;
use App\Services\DomainEvents\Events;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebsocketEntityEventSubscriber implements EventSubscriberInterface
{
   private static $mappings = [
      BlockCreated::class => WebsocketEvents::BLOCK_CREATED,
      BlockChanged::class => WebsocketEvents::BLOCK_CHANGED,
      BlockDeleted::class => WebsocketEvents::BLOCK_DELETED,
      ParagraphCreated::class => WebsocketEvents::PARAGRAPH_CREATED,
      ParagraphChanged::class => WebsocketEvents::PARAGRAPH_CHANGED,
      ParagraphDeleted::class => WebsocketEvents::PARAGRAPH_DELETED,
      ConclusionCreated::class => WebsocketEvents::CONCLUSION_CREATED,
      ConclusionChanged::class => WebsocketEvents::CONCLUSION_CHANGED,
   ];

   /**
    * @var WebsocketClientInterface
    */
   private WebsocketClientInterface $websocketClient;
   /**
    * @var LoggerInterface
    */
   private LoggerInterface $logger;
   /**
    * @var GeneralFormatter
    */
   private GeneralFormatter $formatter;

   public function __construct(WebsocketClientInterface $websocketClient,
                               LoggerInterface $logger,
                               GeneralFormatter $formatter)
   {
      $this->websocketClient = $websocketClient;
      $this->logger = $logger;
      $this->formatter = $formatter;
   }

   /**
    * @inheritDoc
    */
   public static function getSubscribedEvents()
   {
      return [
         Events::DOMAIN_EVENT => 'handle',
      ];
   }

   public function handle(DomainEvent $event): void
   {
      $entity = $event->getEntity();
      if (!isset(self::$mappings[get_class($event)])) {
         $this->logger->warning('Cannot handle domain event ' . get_class($event));
         return;
      }

      $conclusion = $this->getConclusion($entity);
      if (!$conclusion instanceof Conclusion) {
         $this->logger->warning('Cannot find conclusion for entity ' . get_class($entity));
         return;
      }

      $this->send(
         $this->formatter->format($entity),
         $this->getConclusion($entity),
         self::$mappings[get_class($event)]
      );
   }

   private function send($data, Conclusion $conclusion, string $eventName)
   {
      $userIds = $this->getUserIdsProject($conclusion->getProject());
      $this->websocketClient->sendEvent("project/{$conclusion->getProject()->getId()}/conclusion", [
         'event_name' => $eventName,
         'data' => $data,
      ], $userIds);
   }

   private function getUserIdsProject(Project $project): array
   {
      $result = [];
      foreach ($project->getUsers() as $assignment) {
         $result[] = $assignment->getUser()->getId()->getValue();
      }
      return $result;
   }

   private function getConclusion(object $entity): ?Conclusion
   {
      if ($entity instanceof Block) {
         return $entity->getParagraph()->getConclusion();
      }

      if ($entity instanceof Paragraph) {
         return $entity->getConclusion();
      }

      if ($entity instanceof Conclusion) {
         return $entity;
      }

      return null;
   }


}
