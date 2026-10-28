<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Migrations\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version030001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql(
            <<<'EOT'
            ALTER TABLE ngrm_remote_resource MODIFY url TEXT NOT NULL, MODIFY original_filename TEXT NOT NULL, MODIFY remote_id varchar(768) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
            EOT
        );

        $this->addSql(
            <<<'EOT'
            CREATE INDEX ngrm_remote_resource_location_source_idx ON ngrm_remote_resource_location (source)
            EOT
        );
    }

    /**
     * Fails if values longer than 255 characters exist in the narrowed columns (expected).
     */
    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql(
            <<<'EOT'
            DROP INDEX ngrm_remote_resource_location_source_idx ON ngrm_remote_resource_location
            EOT
        );

        $this->addSql(
            <<<'EOT'
            ALTER TABLE ngrm_remote_resource MODIFY url varchar(255) NOT NULL, MODIFY original_filename varchar(255) NOT NULL, MODIFY remote_id varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
            EOT
        );
    }
}
