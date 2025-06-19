<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512173004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE extracted_entity (id SERIAL PRIMARY KEY NOT NULL, document_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, type VARCHAR(100) NOT NULL, CONSTRAINT FK_E25C1BFDC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E25C1BFDC33F7837 ON extracted_entity (document_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE relation (id SERIAL PRIMARY KEY NOT NULL, from_entity_id INT NOT NULL, to_entity_id INT NOT NULL, document_id INT NOT NULL, label VARCHAR(255) NOT NULL, CONSTRAINT FK_6289474991B9E8E7 FOREIGN KEY (from_entity_id) REFERENCES extracted_entity (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_62894749B086AA2C FOREIGN KEY (to_entity_id) REFERENCES extracted_entity (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_62894749C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6289474991B9E8E7 ON relation (from_entity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_62894749B086AA2C ON relation (to_entity_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_62894749C33F7837 ON relation (document_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE extracted_entity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE relation
        SQL);
    }
}