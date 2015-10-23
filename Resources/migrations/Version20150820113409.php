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
 * Migration for adapting the Person Bundle to the 'cahier de charge' :
 *  - update of accompanyingPerid
 */
class Version20150820113409 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE accompanying_period RENAME COLUMN date_opening TO openingdate;');
        $this->addSql('ALTER TABLE accompanying_period RENAME COLUMN date_closing TO closingdate;');
        $this->addSql('ALTER TABLE accompanying_period RENAME COLUMN memo TO remark;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() != 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE accompanying_period RENAME COLUMN openingdate TO date_opening;');
        $this->addSql('ALTER TABLE accompanying_period RENAME COLUMN closingdate TO date_closing;');
        $this->addSql('ALTER TABLE accompanying_period RENAME COLUMN remark TO memo;');
    }
}
