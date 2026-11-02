<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Migrations\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The tables belong to netgen/remote-media-ibexa and netgen/layouts-remote-media and are created even if those packages are not installed.
 */
final class Version030100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql(
            <<<'EOT'
            CREATE TABLE IF NOT EXISTS ngrm_remote_media_content_link (
              id int NOT NULL AUTO_INCREMENT,
              remote_resource_location_id int DEFAULT NULL,
              field_id int NOT NULL,
              version int NOT NULL,
              PRIMARY KEY (id),
              UNIQUE KEY field_id_version_idx (field_id, version),
              UNIQUE KEY UNIQ_4E6FA05BB29166B0 (remote_resource_location_id),
              CONSTRAINT FK_4E6FA05BB29166B0 FOREIGN KEY (remote_resource_location_id)
                REFERENCES ngrm_remote_resource_location (id) ON DELETE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci
            EOT
        );

        $this->addSql(
            <<<'EOT'
            CREATE TABLE IF NOT EXISTS nglayouts_remote_media_item (
              id int NOT NULL AUTO_INCREMENT,
              remote_resource_location_id int DEFAULT NULL,
              value varchar(768) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
              created_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              PRIMARY KEY (id),
              UNIQUE KEY UNIQ_7BAC206E1D775834 (value),
              UNIQUE KEY UNIQ_7BAC206EB29166B0 (remote_resource_location_id),
              CONSTRAINT FK_7BAC206EB29166B0 FOREIGN KEY (remote_resource_location_id)
                REFERENCES ngrm_remote_resource_location (id) ON DELETE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci
            EOT
        );

        $this->addSql(
            <<<'EOT'
            ALTER TABLE nglayouts_remote_media_item MODIFY value varchar(768) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
            EOT
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE IF EXISTS ngrm_remote_media_content_link');
        $this->addSql('DROP TABLE IF EXISTS nglayouts_remote_media_item');
    }
}
