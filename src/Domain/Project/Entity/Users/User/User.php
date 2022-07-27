<?php


namespace App\Domain\Project\Entity\Users\User;

use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Users\User\Certificate\Certificate;
use App\Domain\Project\Entity\Conclusion\Conclusion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment;

/**
 * Class User
 * @package App\Domain\Entity\User
 *
 * @ORM\Entity()
 */
class User
{

   /**
    * @ORM\Id()
    * @ORM\Column(type="user_id")
    */
   protected Id $id;

   /**
    * @var Certificate[]|ArrayCollection
    * @ORM\ManyToMany(
    *    targetEntity="\App\Domain\Project\Entity\Users\User\Certificate\Certificate",
    *    cascade={"persist"}
    * )
    * @ORM\JoinTable(name="user_user_certificate", joinColumns={
    *    @ORM\JoinColumn(name="certificate_id", referencedColumnName="id"),
    * }, inverseJoinColumns={
    *    @ORM\JoinColumn(name="user_id", referencedColumnName="id"),
    * })
    */
   protected $certificates;

   /**
    * @var ProjectUserAssignment[]|ArrayCollection
    * @ORM\OneToMany(
    *    targetEntity="\App\Domain\Project\Entity\Users\ProjectUserAssignment\ProjectUserAssignment",
    *    mappedBy="user",
    *    cascade={"persist"}
    * )
    */
   protected $projects;


   /**
    * @var Role
    * @ORM\Column(type="user_role")
    */
   protected Role $role;

   /**
    * @var Email
    * @ORM\Column(type="user_email")
    */
   protected Email $email;

   /**
    * @var FullName
    * @ORM\Column(type="user_fullname")
    */
   protected FullName $fullName;

   /**
    * @var Conclusion[]|ArrayCollection
    * @ORM\OneToMany(targetEntity="\App\Domain\Project\Entity\Conclusion\Conclusion", mappedBy="author")
    */
   protected $conclusions;

   /**
    * @var Block[]|ArrayCollection
    * @ORM\OneToMany(targetEntity="\App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block", mappedBy="executor", orphanRemoval=false, cascade={"persist"})
    */
   protected $conclusionBlocks;

   public function __construct(Id $id, FullName $fullName, Email $email, Role $role)
   {
      $this->id = $id;
      $this->fullName = $fullName;
      $this->email = $email;
      $this->role = $role;
      $this->certificates = new ArrayCollection();
      $this->projects = new ArrayCollection();
      $this->conclusionBlocks = new ArrayCollection();
      $this->conclusions = new ArrayCollection();
   }

   /**
    * @return Collection|Certificate[]
    */
   public function getCertificates(): Collection
   {
      return $this->certificates;
   }

   public function getId(): Id
   {
      return $this->id;
   }

   public function getRole(): Role
   {
      return $this->role;
   }

   public function setRole(Role $role): self
   {
      $this->role = $role;

      return $this;
   }

   public function getEmail()
   {
      return $this->email;
   }

   public function setEmail(Email $email): self
   {
      $this->email = $email;

      return $this;
   }

   public function getFullName(): FullName
   {
      return $this->fullName;
   }

   public function setFullName(FullName $fullName): self
   {
      $this->fullName = $fullName;

      return $this;
   }

   /**
    * @return Collection|ProjectUserAssignment[]
    */
   public function getProjects(): Collection
   {
      return $this->projects;
   }

   /**
    * @return Collection|Block[]
    */
   public function getConclusionBlocks(): Collection
   {
      return $this->conclusionBlocks;
   }

   /**
    * @return Collection|Conclusion[]
    */
   public function getConclusions(): Collection
   {
      return $this->conclusions;
   }

   public function addProject(ProjectUserAssignment $project)
   {
      if (!$this->projects->contains($project)) {
         $this->projects->add($project);
      }

      return $this;
   }

   public function addCertificate(Certificate $certificate)
   {
      if (!$this->certificates->contains($certificate)) {
         $this->certificates->add($certificate);
      }
   }

   public function removeProject(ProjectUserAssignment $assignment)
   {
      if ($this->projects->contains($assignment)) {
         $this->projects->removeElement($assignment);
      }

      return $this;
   }
}
