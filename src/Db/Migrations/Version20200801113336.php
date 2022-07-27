<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\Project\UseCase\Project\Upsert\Handler;
use App\Services\Project\ProjectFetcherInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200801113336 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var ProjectFetcherInterface $projectFetcher */
        $projectFetcher = $this->container->get(ProjectFetcherInterface::class);
        /** @var Handler $projectUpsert */
        $projectUpsert = $this->container->get(Handler::class);

        $projects = $em->getConnection()->fetchAll('select project_id from pd_project_conclusions group by project_id');
        foreach ($projects as $project){
            $fetchedProject = $projectFetcher->fetch($project['project_id']);
            $projectUpsert->handle($fetchedProject);
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}
