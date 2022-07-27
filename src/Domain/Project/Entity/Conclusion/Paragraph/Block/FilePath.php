<?php


namespace App\Domain\Project\Entity\Conclusion\Paragraph\Block;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable()
 */
class FilePath
{

   /**
    * @var string
    * @ORM\Column(type="string", nullable=true)
    */
   protected ?string $path = null;

   /**
    * @var string|null
    * @ORM\Column(type="string", name="path_key", nullable=true)
    */
   protected ?string $key = null;

   public function __construct(string $path, ?string $key = null)
   {
      Assert::notEmpty($path);
      $this->path = $path;
      $this->key = $key;
   }

   public function getPath()
   {
      return $this->path;
   }

   public function getKey()
   {
      return $this->key;
   }

   public function __toString()
   {
      return $this->path ?: '';
   }

}
