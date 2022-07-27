<?php


namespace App\Domain\Project\Entity\Dictionary;

use App\Domain\Common\DomainEventDispatcher;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Project\Event\Dictionary\DictionaryCreated;
use App\Domain\Project\Event\Dictionary\DictionaryDeleted;
use App\Domain\Project\Event\Dictionary\DictionaryUpdated;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use App\Services\EntityLogger\Annotation as Logger;

/**
 * Class ConclusionBlockDictionary
 * @package App\Domain\Entity\ConclusionBlock
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="dictionary", uniqueConstraints={
 *    @ORM\UniqueConstraint(name="unique_keys", columns={"key", "project_id", "block_id"}),
 * })
 */
class Dictionary
{

   /**
    * @ORM\Id()
    * @var Id
    * @ORM\GeneratedValue(strategy="NONE")
    * @ORM\Column(type="dictionary_id")
    */
   protected Id $id;

   /**
    * @var Key
    * @ORM\Column(type="dictionary_key")
    * @Logger\Versioned
    */
   protected Key $key;

   /**
    * @var string
    * @ORM\Column(type="text", nullable=true)
    * @Logger\Versioned
    */
   protected ?string $value = null;

   /**
    * @var string|null
    * @ORM\Column(type="text", nullable=true)
    * @Logger\Versioned
    */
   protected ?string $name = null;


   /**
    * @var Project
    * @ORM\ManyToOne(targetEntity="App\Domain\Project\Entity\Project\Project")
    * @ORM\JoinColumn(name="project_id", nullable=false, referencedColumnName="id")
    */
   protected Project $project;

   /**
    * @var Block|null
    * @ORM\ManyToOne(targetEntity="App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block")
    * @ORM\JoinColumn(name="block_id", nullable=true, referencedColumnName="id")
    */
   protected ?Block $block = null;

   /**
    * Dictionary constructor.
    * @param Id $id
    * @param Key $key
    * @param Project $project
    * @param Block|null $block
    * @param string|null $name
    * @param string|null $value
    */
   public function __construct(Id $id,
                               Key $key,
                               Project $project,
                               ?Block $block = null,
                               ?string $name = null,
                               ?string $value = null)
   {
      if (!$block && $value === null) {
         throw new DomainException("Project's dictionary should has a value");
      }
      $this->id = $id;
      $this->key = $key;
      $this->name = $name;
      $this->value = $value;
      $this->project = $project;
      $this->block = $block;

      DomainEventDispatcher::instance()->dispatch(new DictionaryCreated($this));
   }

   public function getId(): Id
   {
      return $this->id;
   }

   public function getKey(): Key
   {
      return $this->key;
   }

   public function getValue(): ?string
   {
      return $this->value;
   }

   public function setValue(?string $value): self
   {
      $this->value = $value;
      DomainEventDispatcher::instance()->dispatch(new DictionaryUpdated($this));
      return $this;
   }

   public function getName(): ?string
   {
      return $this->name;
   }

   public function setName(?string $name): self
   {
      $this->name = $name;
      DomainEventDispatcher::instance()->dispatch(new DictionaryUpdated($this));
      return $this;
   }

   public function getProject(): Project
   {
      return $this->project;
   }

   public function getBlock(): ?Block
   {
      return $this->block;
   }

   /**
    * @ORM\PreRemove()
    * @noinspection PhpUnused
    */
   public function dictionaryDelete()
   {
      DomainEventDispatcher::instance()->dispatch(new DictionaryDeleted($this));
   }
}
