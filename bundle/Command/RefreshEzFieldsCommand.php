<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function count;
use function sprintf;
use function array_key_exists;
use function array_pop;
use function array_map;
use function array_unique;
use function json_encode;
use function json_decode;

class RefreshEzFieldsCommand extends Command
{
    private const DEFAULT_CHUNK_SIZE = 200;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $provider;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var bool
     */
    private $force = false;

    /**
     * @var array
     */
    private $contentIdsFilter = [];

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value[]
     */
    private $remoteResources = [];

    public function __construct(EntityManagerInterface $entityManager, RemoteMediaProvider $provider)
    {
        $this->entityManager = $entityManager;
        $this->provider = $provider;

        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->setName('netgen:ngremotemedia:refresh:ez_fields')
            ->setDescription('Refresh all NGRM fields in eZ to fix missing attributes and metadata')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'This will use the first found resource and empty fields with non-existing resources'
            )
            ->addOption(
                'dry-run',
                'dr',
                InputOption::VALUE_NONE,
                'This will only display the actions that will be performed'
            )
            ->addOption(
                'content-ids',
                'cids',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'List of content IDs to process (default: all)'
            )->addOption(
                'chunk-size',
                'cs',
                InputOption::VALUE_OPTIONAL,
                'The size of the chunk of attributes to fetch (and size of chunk of resource to get from remote media in one API request)',
                self::DEFAULT_CHUNK_SIZE
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->force = $input->getOption('force');
        $this->dryRun = $input->getOption('dry-run');
        $this->contentIdsFilter = $input->getOption('content-ids');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('chunk-size');
        $offset = 0;
        $count = 1;
        $attributesCount = $this->getAttributesCount();

        $output->writeln('Found ' . $attributesCount . ' entries. Starting...');

        if ($this->dryRun === true) {
            $output->writeln('<comment>Using dry-run option: the database won\'t be changed.</comment>');
        }

        do {
            $attributes = $this->loadAttributes($limit, $offset);
            $this->fillRemoteResources($attributes);

            foreach ($attributes as $attribute) {
                $output->writeln(
                    PHP_EOL . sprintf(
                        'Processing attribute with ID \'%d\' for content with ID \'%d\' and version \'%d\'. [ %d / %d ]',
                        $attribute['id'],
                        $attribute['contentobject_id'],
                        $attribute['version'],
                        $count,
                        $attributesCount
                    )
                );

                $this->processAttribute($attribute);
                $count++;
            }

            $offset += $limit;
        } while (count($attributes) > 0);
    }

    private function processAttribute(array $attribute): void
    {
        $decodedData = json_decode($attribute['data_text'], true);

        if ($decodedData === null || $decodedData === '') {
            $this->updateAttribute($attribute, new Value());
            $this->output->writeln(' -> <info>SUCCESS</info> the field was missing any data - it has been filled with empty value.');

            return;
        }

        $localValue = new Value($decodedData);

        if ($localValue->resourceId === null) {
            $this->updateAttribute($attribute, new Value());
            $this->output->writeln(' -> <info>SUCCESS</info> the field is empty - it has been filled with empty value.');

            return;
        }

        $resourceType = $this->resolveResourceType($localValue);
        $resources = $this->getResourcesByResourceId($localValue->resourceId);

        if (count($resources) === 0 || ($resourceType !== null && !array_key_exists($resourceType, $resources))) {
            $this->output->writeln(sprintf(' -> remote resource of type \'%s\' with resourceId: \'%s\' hasn\'t been found', $resourceType, $localValue->resourceId));

            if ($this->force === true) {
                $this->updateAttribute($attribute, new Value());
                $this->output->writeln(' -> <info>FORCED SUCCESS</info> the field has been set to empty.');

                return;
            }

            $this->output->writeln(' -> <question>SKIPPED</question> field has been skipped (resolve this manually or use --force to set those fields to empty)');

            return;
        }

        if ($resourceType !== null && array_key_exists($resourceType, $resources)) {
            $this->updateAttribute($attribute, $resources[$resourceType]);
            $this->output->writeln(sprintf(' -> <info>SUCCESS</info> remote resource of type \'%s\' with resourceId \'%s\' has been found and field has been updated', $resourceType, $localValue->resourceId));

            return;
        }

        if ($resourceType === null && count($resources) === 1) {
            $this->updateAttribute($attribute, array_pop($resources));
            $this->output->writeln(sprintf(' -> <info>SUCCESS</info> remote resource of type \'%s\' with resourceId \'%s\' has been found and field has been updated', $resourceType, $localValue->resourceId));

            return;
        }

        $this->output->writeln(' -> multiple resources with same resourceId have been found:');

        foreach ($resources as $resource) {
            $this->output->writeln(sprintf('   * [%s] %s', $resource->resourceType, $resource->resourceId));
        }

        if ($this->force === true) {
            $resource = array_pop($resources);
            $this->updateAttribute($attribute, $resource);
            $this->output->writeln(sprintf(' -> <info>FORCED SUCCESS</info> field has been updated with the first found resource of type \'%s\' with resourceId \'%s\'', $resource->resourceType, $resource->resourceId));

            return;
        }

        $this->output->writeln(' -> <question>SKIPPED</question> field has been skipped. Resolve this manually or use --force to automatically select the first found resource');
    }

    private function fillRemoteResources(array $attributes): void
    {
        $resourceIds = array_map(function ($attribute){
            $data = json_decode($attribute['data_text'], true);

            return $data['resourceId'];
        }, $attributes);

        $resourceIds = array_unique($resourceIds);
        $this->remoteResources = [];
        $nextCursor = null;

        $query = Query::createResourceIdsSearchQuery($resourceIds, 500);

        do {
            $searchResult = $this->provider->searchResources($query);

            foreach ($searchResult->getResults() as $result) {
                $this->remoteResources[] = Value::createFromCloudinaryResponse($result);
            }

            $query->setNextCursor($searchResult->getNextCursor());
        } while ($nextCursor !== null);
    }

    private function getResourcesByResourceId(string $resourceId): array
    {
        $resources = [];

        foreach ($this->remoteResources as $resource) {
            if ($resource->resourceId === $resourceId) {
                $resources[$resource->resourceType] = $resource;
            }
        }

        return $resources;
    }

    private function resolveResourceType(Value $value): ?string
    {
        if ($value->resourceType !== null) {
            return $value->resourceType;
        }

        if (array_key_exists('resource_type', $value->metaData) && $value->metaData['resource_type'] !== null) {
            return $value->metaData['resource_type'];
        }

        if ($value->mediaType === 'image' || $value->mediaType === 'video') {
            return $value->mediaType;
        }

        if ($value->mediaType === 'other' && array_key_exists('format', $value->metaData) && $value->metaData['format'] === 'pdf') {
            return 'image';
        }

        return null;
    }

    private function updateAttribute(array $attribute, Value $value): void
    {
        if ($this->dryRun === true) {
            return;
        }

        $oldDataText = $attribute['data_text'];
        $newDataText = json_encode($value);

        if ($oldDataText === $newDataText) {
            return;
        }

        $sql = "UPDATE ezcontentobject_attribute
                SET data_text=:data_text
                WHERE id=:id AND version=:version";

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('data_text', $newDataText);
        $query->bindValue('id', $attribute['id']);
        $query->bindValue('version', $attribute['version']);
        $query->execute();
    }

    private function loadAttributes(int $limit, int $offset): array
    {
        $sql = "SELECT
                id, version, data_text, contentobject_id
                FROM ezcontentobject_attribute
                WHERE data_type_string=:data_type_string";

        if (count($this->contentIdsFilter) > 0) {
            $sql .= " AND contentobject_id IN (".implode(',', $this->contentIdsFilter).")";
        }

        $sql .= " LIMIT :offset,:limit";

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('data_type_string', 'ngremotemedia');
        $query->bindValue('limit', $limit, ParameterType::INTEGER);
        $query->bindValue('offset', $offset, ParameterType::INTEGER);
        $query->execute();

        return $query->fetchAll();
    }

    private function getAttributesCount(): int
    {
        $sql = "SELECT COUNT(id) AS count
                FROM ezcontentobject_attribute
                WHERE data_type_string=:data_type_string";

        if (count($this->contentIdsFilter) > 0) {
            $sql .= " AND contentobject_id IN (".implode(',', $this->contentIdsFilter).")";
        }

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('data_type_string', 'ngremotemedia');
        $query->execute();

        return (int) $query->fetch()['count'];
    }
}
