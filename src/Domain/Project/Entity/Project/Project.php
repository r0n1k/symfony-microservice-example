<?php


namespace App\Domain\Project\Entity\Project;

use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Project
 * @package App\Domain\Project\Entity
 *
 * @ORM\Entity()
 */
class Project
{

   /**
    * @ORM\Id()
    * @ORM\Column(type="project_id")
    * @ORM\GeneratedValue(strategy="NONE")
    * @var Id
    */
   protected Id $id;

   /**
    * @ORM\Column(type="project_name")
    * @var Name
    */
   protected Name $name;

   /**
    * @var ProjectUserAssignment[]|ArrayCollection
    * @ORM\OneToMany(
    *    targetEntity="\App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment",
    *    mappedBy="project",
    *    cascade={"persist"}
    * )
    */
   protected $users;

   /**
    * @var State
    * @ORM\Column(type="project_state")
    */
   protected State $state;


   public function __construct(Id $id, Name $name, State $state)
   {
      $this->users = new ArrayCollection();
      $this->id = $id;
      $this->name = $name;
      $this->state = $state;
   }

   public function setId(Id $id): Project
   {
      $this->id = $id;

      return $this;
   }

   public function getId(): Id
   {
      return $this->id;
   }

   public function getName(): Name
   {
      return $this->name;
   }

   public function setName(Name $name): self
   {
      $this->name = $name;

      return $this;
   }

   public function getState(): State
   {
      return $this->state;
   }

   public function setState(State $state): self
   {
      $this->state = $state;

      return $this;
   }

   /**
    * @return Collection|ProjectUserAssignment[]
    */
   public function getUsers(): Collection
   {
      return $this->users;
   }

   public function addUser(ProjectUserAssignment $userAssignment)
   {
      if (!$this->users->contains($userAssignment)) {
         $this->users->add($userAssignment);
      }

      return $this;
   }

   public function removeUser(ProjectUserAssignment $assignment)
   {
      if ($this->users->contains($assignment)) {
         $this->users->removeElement($assignment);
      }

      return $this;
   }

}
