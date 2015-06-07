<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add a center to class person
 * 
 */
class Version20150607231010 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     *
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        if ($container === NULL) {
            throw new \RuntimeException('Container is not provided. This migration '
                    . 'need container to set a default center');
        }
        
        $this->container = $container;
    }
    
    public function getDescription()
    {
        return 'Add a center on the person entity. The default center is the first '
        . 'recorded.';
    }
    
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $centers = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:Center')
                ->findAll();
        $defaultCenterId = $centers[0]->getId();

        $this->addSql('ALTER TABLE person ADD center_id INT');
        $this->addSql('UPDATE person SET center_id = :id', array('id' => $defaultCenterId));
        $this->addSql('ALTER TABLE person '
                . 'ADD CONSTRAINT FK_person_center FOREIGN KEY (center_id) '
                . 'REFERENCES centers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE person ALTER center_id SET NOT NULL');
        $this->addSql('CREATE INDEX IDX_person_center ON person (center_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE Person DROP CONSTRAINT FK_person_center');
        $this->addSql('DROP INDEX IDX_person_center');
        $this->addSql('ALTER TABLE Person DROP center_id');
    }



}
