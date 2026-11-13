<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Migrations\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version030000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql(
            <<<'EOT'
            CREATE TABLE ngrm_remote_resource (
              id int NOT NULL AUTO_INCREMENT,
              remote_id varchar(255) NOT NULL,
              type varchar(100) NOT NULL,
              url varchar(255) NOT NULL,
              name varchar(255) NOT NULL,
              original_filename varchar(255) NOT NULL,
              md5 varchar(64) NOT NULL,
              version varchar(100) DEFAULT NULL,
              folder longtext COMMENT '(DC2Type:object)',
              visibility varchar(100) NOT NULL,
              size int NOT NULL,
              alt_text text,
              caption text,
              tags longtext COMMENT '(DC2Type:array)',
              metadata longtext COMMENT '(DC2Type:array)',
              context longtext COMMENT '(DC2Type:array)',
              created_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              PRIMARY KEY (id),
              UNIQUE KEY UNIQ_4B1F1DB42A3E9C94 (remote_id)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci
            EOT
        );

        $this->addSql(
            <<<'EOT'
            CREATE TABLE ngrm_remote_resource_location (
              id int NOT NULL AUTO_INCREMENT,
              remote_resource_id int DEFAULT NULL,
              source varchar(255) DEFAULT NULL,
              crop_settings longtext NOT NULL COMMENT '(DC2Type:array)',
              watermark_text text,
              created_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              PRIMARY KEY (id),
              KEY IDX_76616F4846147E03 (remote_resource_id),
              CONSTRAINT FK_76616F4846147E03 FOREIGN KEY (remote_resource_id)
                REFERENCES ngrm_remote_resource (id) ON DELETE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci
            EOT
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE ngrm_remote_resource_location');
        $this->addSql('DROP TABLE ngrm_remote_resource');
    }
}
