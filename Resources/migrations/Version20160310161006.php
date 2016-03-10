<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add a many-to-many relationship between person and addresses
 */
class Version20160310161006 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE chill_person_persons_to_addresses ('
              . 'person_id INT NOT NULL, '
              . 'address_id INT NOT NULL, '
              . 'PRIMARY KEY(person_id, address_id))');
        $this->addSql('CREATE INDEX IDX_4655A196217BBB47 '
              . 'ON chill_person_persons_to_addresses (person_id)');
        $this->addSql('CREATE INDEX IDX_4655A196F5B7AF75 '
              . 'ON chill_person_persons_to_addresses (address_id)');
        $this->addSql('ALTER TABLE chill_person_persons_to_addresses '
              . 'ADD CONSTRAINT FK_4655A196217BBB47 '
              . 'FOREIGN KEY (person_id) '
              . 'REFERENCES Person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE chill_person_persons_to_addresses '
              . 'ADD CONSTRAINT FK_4655A196F5B7AF75 '
              . 'FOREIGN KEY (address_id) '
              . 'REFERENCES chill_main_address (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE chill_person_persons_to_addresses');
       
    }
}
