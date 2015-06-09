<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Chill\MainBundle\Entity\Center;

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
        
        // retrieve center for setting a default center
        $centers = $this->container->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:Center')
                ->findAll();
        
        
        if (count($center) > 0) {
            $defaultCenterId = $centers[0]->getId();
        } else { // if no center, performs other checks
            //check if there are data in person table
            $nbPeople = $this->container->get('doctrine.orm.entity_manager')
                    ->createQuery('SELECT count(p) FROM ChillPersonBundle:Person p')
                    ->getSingleScalarResult();
            
            if ($nbPeople > 0) {
                // we have data ! We have to create a center !
                $center = new Center();
                $center->setName('Auto-created center');
                $this->container->get('doctrine.orm.entity_manager')
                        ->persist($center)
                        ->flush();
                $defaultCenterId = $center->getId();
            }
        } 
        

        $this->addSql('ALTER TABLE person ADD center_id INT');
        
        if (isset($defaultCenterId)) {
            $this->addSql('UPDATE person SET center_id = :id', array('id' => $defaultCenterId));
        }
        
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
