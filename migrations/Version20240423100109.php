<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240423100109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_1BF103E9E7927C74 ON dor_user');
        $this->addSql('ALTER TABLE dor_user CHANGE reference reference VARCHAR(12) NOT NULL, CHANGE paymentMethod payment_method INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BF103E9AEA34913 ON dor_user (reference)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_1BF103E9AEA34913 ON `DOR_USER`');
        $this->addSql('ALTER TABLE `DOR_USER` CHANGE reference reference VARCHAR(12) DEFAULT NULL, CHANGE payment_method paymentMethod INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BF103E9E7927C74 ON `DOR_USER` (email)');
    }
}
