<?php

/* 
 * Chill is a software for social workers
 * 
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>, <info@champs-libres.coop>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration for adding maritalstatus to person
 */
class Version20150812110708 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE marital_status (
            id character varying(10) NOT NULL,
            name json NOT NULL,
            CONSTRAINT marital_status_pkey PRIMARY KEY (id));');
        $this->addSql('ALTER TABLE person ADD COLUMN maritalstatus_id character varying(10)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT fk_person_marital_status FOREIGN KEY (maritalstatus_id) REFERENCES marital_status (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE NO ACTION');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE person DROP CONSTRAINT fk_person_marital_status;');
        $this->addSql('ALTER TABLE person DROP COLUMN maritalstatus_id;');
        $this->addSql('DROP TABLE marital_status;');
    }
}
