<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240424141818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dor_preorder ADD choice_livraison INT DEFAULT NULL, ADD choice_meeting INT DEFAULT NULL, ADD user_rt INT DEFAULT NULL, CHANGE ov ov VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BF103E9AEA34913 ON dor_user (reference)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `DOR_PREORDER` DROP choice_livraison, DROP choice_meeting, DROP user_rt, CHANGE ov ov VARCHAR(200) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_1BF103E9AEA34913 ON `DOR_USER`');
    }
}
