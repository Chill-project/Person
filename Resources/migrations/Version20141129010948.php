<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141129010948 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("CREATE SEQUENCE ClosingMotive_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE person_history_file_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE SEQUENCE Person_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $this->addSql("CREATE TABLE ClosingMotive (id INT NOT NULL, name JSON NOT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE TABLE person_history_file (id INT NOT NULL, person_id INT DEFAULT NULL, date_opening DATE NOT NULL, date_closing DATE DEFAULT NULL, memo TEXT NOT NULL, closingMotive_id INT DEFAULT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE INDEX IDX_64A4A621217BBB47 ON person_history_file (person_id);");
        $this->addSql("CREATE INDEX IDX_64A4A621504CB38D ON person_history_file (closingMotive_id);");
        $this->addSql("CREATE TABLE Person (id INT NOT NULL, nationality_id INT DEFAULT NULL, firstName VARCHAR(255) NOT NULL, lastName VARCHAR(255) NOT NULL, date_of_birth DATE DEFAULT NULL, place_of_birth VARCHAR(255) NOT NULL, genre VARCHAR(9) NOT NULL, memo TEXT NOT NULL, email TEXT NOT NULL, proxyHistoryOpenState BOOLEAN NOT NULL, cFData TEXT NOT NULL, phonenumber TEXT DEFAULT NULL, countryOfBirth_id INT DEFAULT NULL, PRIMARY KEY(id));");
        $this->addSql("CREATE INDEX IDX_3370D4403818DA5 ON Person (countryOfBirth_id);");
        $this->addSql("CREATE INDEX IDX_3370D4401C9DA55 ON Person (nationality_id);");
        $this->addSql("CREATE INDEX person_names ON Person (firstName, lastName);");
        $this->addSql("COMMENT ON COLUMN Person.cFData IS '(DC2Type:array)';");
        $this->addSql("CREATE TABLE persons_spoken_languages (person_id INT NOT NULL, language_id VARCHAR(255) NOT NULL, PRIMARY KEY(person_id, language_id));");
        $this->addSql("CREATE INDEX IDX_7201106F217BBB47 ON persons_spoken_languages (person_id);");
        $this->addSql("CREATE INDEX IDX_7201106F82F1BAF4 ON persons_spoken_languages (language_id);");
        $this->addSql("ALTER TABLE person_history_file ADD CONSTRAINT FK_64A4A621217BBB47 FOREIGN KEY (person_id) REFERENCES Person (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE person_history_file ADD CONSTRAINT FK_64A4A621504CB38D FOREIGN KEY (closingMotive_id) REFERENCES ClosingMotive (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE Person ADD CONSTRAINT FK_3370D4403818DA5 FOREIGN KEY (countryOfBirth_id) REFERENCES Country (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE Person ADD CONSTRAINT FK_3370D4401C9DA55 FOREIGN KEY (nationality_id) REFERENCES Country (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE persons_spoken_languages ADD CONSTRAINT FK_7201106F217BBB47 FOREIGN KEY (person_id) REFERENCES Person (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $this->addSql("ALTER TABLE persons_spoken_languages ADD CONSTRAINT FK_7201106F82F1BAF4 FOREIGN KEY (language_id) REFERENCES Language (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
