<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150212173934 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("ALTER TABLE person RENAME proxyHistoryOpenState TO proxyAccompanyingPeriodOpenState;");
        $this->addSql("ALTER TABLE person_history_file RENAME TO accompanying_period;");
        $this->addSql("ALTER SEQUENCE person_history_file_id_seq RENAME TO accompanying_period_id_seq;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    	$this->addSql("ALTER TABLE accompanying_period RENAME TO person_history_file;");
        $this->addSql("ALTER TABLE person RENAME proxyAccompanyingPeriodOpenState TO proxyHistoryOpenState;");
        $this->addSql("ALTER SEQUENCE accompanying_period_id_seq RENAME TO person_history_file_id_seq;");
    }
}
