<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Migrations\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version030100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql(
            <<<'EOT'
            ALTER TABLE ngrm_remote_resource_location ADD alt_text_override TEXT DEFAULT NULL, ADD caption_override TEXT DEFAULT NULL
            EOT
        );
    }

    /**
     * Drops the overrides stored in the columns.
     */
    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql(
            <<<'EOT'
            ALTER TABLE ngrm_remote_resource_location DROP alt_text_override, DROP caption_override
            EOT
        );
    }
}
