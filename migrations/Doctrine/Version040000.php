<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Migrations\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version040000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql(
            <<<'EOT'
            ALTER TABLE ngrm_remote_resource_location ADD position INT DEFAULT NULL
            EOT
        );
    }

    /**
     * Drops the order of the locations stored in the column.
     */
    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on MySQL.');

        $this->addSql(
            <<<'EOT'
            ALTER TABLE ngrm_remote_resource_location DROP position
            EOT
        );
    }
}
