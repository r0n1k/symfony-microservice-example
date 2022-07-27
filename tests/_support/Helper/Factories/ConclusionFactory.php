<?php
namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Id;
use App\Domain\Project\Entity\Conclusion\Kind;
use App\Domain\Project\Entity\Conclusion\Revision;
use App\Domain\Project\Entity\Conclusion\TemplateId;
use App\Domain\Project\Entity\Conclusion\Title;
use App\Domain\Project\Entity\Project\Project;
use App\Domain\Template\Entity\Template;
use App\Domain\Project\Entity\Users\User\User;
use App\Tests\Helper\DataFactory;
use Faker\Factory;

class ConclusionFactory
{

   public static function build(DataFactory $factory, $data = [])
   {
      $faker = Factory::create();


      /** @var Template|null $template */
      $template = $data['template'] ?? null;

      $id = Id::next();
      $title = $data['title'] ?? new Title($faker->name);
      $user = $data['user'] ?? $factory->make(User::class);
      /** @var Project $project */
      $project = $data['project'] ?? $factory->have(Project::class);
      $kind = $data['kind'] ?? new Kind(Kind::GENERATOR);
      $revision = new Revision($faker->randomNumber());
      $templateId = $template ? new TemplateId($template->getId()->getValue()) : null;

      return new Conclusion($id, $title, $user, $project, $kind, $revision, $templateId, null);
   }

}
