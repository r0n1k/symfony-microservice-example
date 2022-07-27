<?php /** @noinspection PhpDeprecationInspection */


namespace App\Http\Formatter;


use App\Http\Formatter\Events\EntityFormatEvent;
use App\Http\Formatter\Events\FormatterEvents;
use App\Http\Formatter\Events\ObjectFormatEvent;
use App\Http\Formatter\Events\TraceFormatEvent;
use App\Http\Formatter\Objects\Trace;
use App\Services\ErrorsCollection\ErrorsCollection;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Proxy\Proxy;
use LogicException;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;
use Traversable;

class GeneralFormatter
{
   protected const UNFORMATTED = '%unformatted_data%';
   /**
    * @var EventDispatcherInterface
    */
   private EventDispatcherInterface $dispatcher;
   /**
    * @var Reader
    */
   private Reader $reader;
   /**
    * @var ErrorsCollection
    */
   private ErrorsCollection $errors;

   public function __construct(EventDispatcherInterface $dispatcher,
                               ErrorsCollection $errors,
                               Reader $reader)
   {
      $this->dispatcher = $dispatcher;
      $this->reader = $reader;
      $this->errors = $errors;
   }

   /**
    * @param $data
    * @return array|bool|float|int|string|null
    */
   public function format($data)
   {
      $result = self::UNFORMATTED;

      if ($data instanceof Trace) {
         $traceFormatEvent = new TraceFormatEvent($data->getStacktrace());
         $this->dispatcher->dispatch($traceFormatEvent, FormatterEvents::FORMAT_TRACE);
         if ($traceFormatEvent->isFormatted()) {
            return $traceFormatEvent->getFormattedData();
         }
         return null;
      }

      if (is_iterable($data) || $data instanceof Traversable) {
         $event = new ObjectFormatEvent($data);
         $this->dispatcher->dispatch($event, FormatterEvents::FORMAT_OBJECT);

         if ($event->isFormatted()) {
            $result = $this->format($event->getFormattedData());
         } else {
            $result = [];
            foreach ($data as $i => $item) {
               $result[$i] = $this->format($item);
            }
         }
      } elseif (is_object($data)) {
         if ($this->isEntity($data)) {
            $event = new EntityFormatEvent($data);
            $this->dispatcher->dispatch($event, FormatterEvents::FORMAT_ENTITY);

            if ($event->isFormatted()) {
               $result = $this->format($event->getFormattedData());
            }
         } elseif ($data instanceof ConstraintViolationListInterface) {
            foreach ($data as $violation) {
               /** @var ConstraintViolationInterface $violation */
               $message = $violation->getMessage();
               $value = $violation->getInvalidValue();
               $root = get_class($violation->getRoot());
               $this->errors->add("Error in value $value of class $root: $message");
            }
            return null;
         } else {
            $event = new ObjectFormatEvent($data);
            $this->dispatcher->dispatch($event, FormatterEvents::FORMAT_OBJECT);

            if ($event->isFormatted()) {
               $result = $this->format($event->getFormattedData());
            } else {
               try {
                  $result = (string)$data;
               } catch (Throwable $e) {
               }
            }
         }
      } elseif (is_scalar($data) || $data === null) {
         $result = $data;
      }

      if ($result === self::UNFORMATTED) {
         $message = 'Cannot format data of type ' .
            gettype($data) .
            (is_object($data) ? ' instance of class ' . get_class($data) : '');

         throw new LogicException($message);
      }

      return $result;
   }



   /**
    * @param $data
    * @return bool
    * @noinspection PhpDocMissingThrowsInspection
    * @noinspection PhpUnhandledExceptionInspection
    */
   private function isEntity($data)
   {
      return $data instanceof Proxy ||
         $this->reader->getClassAnnotation(new ReflectionClass($data), Entity::class) !== null;
   }
}
