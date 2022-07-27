<?php


namespace App\Domain\Project\Entity\Users\ProjectUserAssignment;

use App\Domain\Project\Entity\Project\Project;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Project\Entity\Users\User\User;

/**
 * Class ProjectUser
 * @package App\Domain\Entity\Project
 *
 * @ORM\Entity()
 */
class ProjectUserAssignment
{
   /**
    * @var int
    * @ORM\Id()
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\SequenceGenerator(sequenceName="project_user_assignment_id_seq")
    */
   protected int $id;

   /**
    * @var Project
    * @ORM\ManyToOne(targetEntity="\App\Domain\Project\Entity\Project\Project", inversedBy="users", cascade={"persist"})
    */
   protected Project $project;

   /**
    * @var User
    * @ORM\ManyToOne(targetEntity="\App\Domain\Project\Entity\Users\User\User", inversedBy="projects", cascade={"persist"})
    */
   protected User $user;

   /**
    * @var Role
    * @ORM\Column(type="project_user_assignment_role")
    */
   protected Role $role;

   public function __construct(Project $project, User $user, Role $role)
   {
      $this->project = $project;
      $this->user = $user;
      $this->role = $role;

      $this->project->addUser($this);
      $this->user->addProject($this);
   }

   public function getId(): ?int
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

   public function getProject(): Project
   {
       return $this->project;
   }

   public function getUser(): User
   {
       return $this->user;
   }
}
