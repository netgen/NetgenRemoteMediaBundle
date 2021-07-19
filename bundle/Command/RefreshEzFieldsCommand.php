<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\Provider\Cloudinary\Search\Query;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function array_key_exists;
use function array_pop;
use function array_unique;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_numeric;
use function json_decode;
use function json_encode;
use function json_last_error;
use function sprintf;
use function trim;
use const JSON_ERROR_NONE;
use const PHP_EOL;

class RefreshEzFieldsCommand extends Command
{
    const CUSTOMTAG_NAMESPACE = 'http://ez.no/namespaces/ezpublish3/custom/';
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
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\VariationResolver
     */
    private $variationResolver;

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
     * @var int
     */
    private $apiRateLimitThreshold;

    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\Core\FieldType\RemoteMedia\Value[]
     */
    private $remoteResources = [];

    /**
     * @var array
     */
    private $availableVariations = [];

    /**
     * @var array
     */
    private $missingVariations = [];

    public function __construct(EntityManagerInterface $entityManager, RemoteMediaProvider $provider, VariationResolver $variationResolver)
    {
        $this->entityManager = $entityManager;
        $this->provider = $provider;
        $this->variationResolver = $variationResolver;

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
                'This will use the first found resource and empty fields with non-existing resources',
            )
            ->addOption(
                'dry-run',
                'dr',
                InputOption::VALUE_NONE,
                'This will only display the actions that will be performed',
            )
            ->addOption(
                'content-ids',
                'cids',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'List of content IDs to process (default: all)',
            )->addOption(
                'chunk-size',
                'cs',
                InputOption::VALUE_OPTIONAL,
                'The size of the chunk of attributes to fetch (and size of chunk of resource to get from remote media in one API request)',
                self::DEFAULT_CHUNK_SIZE,
            )->addOption(
                'rate-limit-threshold',
                'rtt',
                InputOption::VALUE_OPTIONAL,
                'Percentage of remaining API rate limit below which the command will exit to prevent crashing the media on frontend (default 50%).',
                50,
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->force = $input->getOption('force');
        $this->dryRun = $input->getOption('dry-run');
        $this->contentIdsFilter = $input->getOption('content-ids');
        $this->apiRateLimitThreshold = (int) $input->getOption('rate-limit-threshold');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('chunk-size');
        $offset = 0;
        $count = 1;
        $chunkCount = 0;
        $attributesCount = $this->getAttributesCount();

        $output->writeln('Found ' . $attributesCount . ' entries. Starting...');

        if ($this->dryRun === true) {
            $output->writeln('<comment>Using dry-run option: the database won\'t be changed.</comment>');
        }

        do {
            if ($chunkCount % 100 === 0 && $this->apiRateLimitExceeded()) {
                $output->writeln("<error>There's less than {$this->apiRateLimitThreshold} % API rate limit left. Exiting...</error>");

                return 0;
            }

            $attributes = $this->loadAttributes($limit, $offset);
            $this->fillRemoteResources($attributes);

            foreach ($attributes as $attribute) {
                $output->writeln(
                    PHP_EOL . sprintf(
                        'Processing attribute with ID \'%d\' of type \'%s\' for content with ID \'%d\' and version \'%d\'. [ %d / %d ]',
                        $attribute['id'],
                        $attribute['data_type_string'],
                        $attribute['contentobject_id'],
                        $attribute['version'],
                        $count,
                        $attributesCount,
                    ),
                );

                switch ($attribute['data_type_string']) {
                    case 'ngremotemedia':
                        $this->processNgrmAttribute($attribute);

                        break;

                    case 'ezxmltext':
                        $this->processEzXmlTextAttribute($attribute);

                        break;
                }

                ++$count;
            }

            $offset += $limit;
            ++$chunkCount;
        } while (count($attributes) > 0);

        $this->output->writeln(PHP_EOL . PHP_EOL . 'The script is done. Here are the missing variations that have to be added in the "embedded" group:');

        foreach ($this->missingVariations as $variation) {
            $this->output->writeln(' - ' . $variation);
        }

        return 0;
    }

    private function processNgrmAttribute(array $attribute): void
    {
        $decodedData = json_decode($attribute['data_text'], true);

        if ($decodedData === null || $decodedData === '') {
            $this->updateNgrmAttribute($attribute, new Value());
            $this->updateExternalData($attribute, new Value());
            $this->output->writeln(' -> <info>SUCCESS</info> the field was missing any data - it has been filled with empty value.');

            return;
        }

        $localValue = new Value($decodedData);

        if ($localValue->resourceId === null) {
            $this->updateNgrmAttribute($attribute, new Value());
            $this->updateExternalData($attribute, new Value());
            $this->output->writeln(' -> <info>SUCCESS</info> the field is empty - it has been filled with empty value.');

            return;
        }

        $resourceType = $this->resolveResourceType($localValue);
        $resources = $this->getResourcesByResourceId($localValue->resourceId);

        if (count($resources) === 0 || ($resourceType !== null && !array_key_exists($resourceType, $resources))) {
            $this->output->writeln(sprintf(' -> remote resource of type \'%s\' with resourceId: \'%s\' hasn\'t been found', $resourceType, $localValue->resourceId));

            if ($this->force === true) {
                $this->updateNgrmAttribute($attribute, new Value());
                $this->updateExternalData($attribute, new Value());
                $this->output->writeln(' -> <info>FORCED SUCCESS</info> the field has been set to empty.');

                return;
            }

            $this->output->writeln(' -> <question>SKIPPED</question> field has been skipped (resolve this manually or use --force to set those fields to empty)');

            return;
        }

        if ($resourceType !== null && array_key_exists($resourceType, $resources)) {
            $this->updateNgrmAttribute($attribute, $resources[$resourceType]);
            $this->updateExternalData($attribute, $resources[$resourceType]);
            $this->output->writeln(sprintf(' -> <info>SUCCESS</info> remote resource of type \'%s\' with resourceId \'%s\' has been found and field has been updated', $resourceType, $localValue->resourceId));

            return;
        }

        if ($resourceType === null && count($resources) === 1) {
            $resource = array_pop($resources);
            $this->updateNgrmAttribute($attribute, $resource);
            $this->updateExternalData($attribute, $resource);
            $this->output->writeln(sprintf(' -> <info>SUCCESS</info> remote resource of type \'%s\' with resourceId \'%s\' has been found and field has been updated', $resourceType, $localValue->resourceId));

            return;
        }

        $this->output->writeln(' -> multiple resources with same resourceId have been found:');

        foreach ($resources as $resource) {
            $this->output->writeln(sprintf('   * [%s] %s', $resource->resourceType, $resource->resourceId));
        }

        if ($this->force === true) {
            $resource = array_pop($resources);
            $this->updateNgrmAttribute($attribute, $resource);
            $this->updateExternalData($attribute, $resource);
            $this->output->writeln(sprintf(' -> <info>FORCED SUCCESS</info> field has been updated with the first found resource of type \'%s\' with resourceId \'%s\'', $resource->resourceType, $resource->resourceId));

            return;
        }

        $this->output->writeln(' -> <question>SKIPPED</question> field has been skipped. Resolve this manually or use --force to automatically select the first found resource');
    }

    private function processEzXmlTextAttribute(array $attribute): void
    {
        $doc = new DOMDocument();
        $doc->loadXML($attribute['data_text']);

        $xpath = new DOMXPath($doc);
        $tags = $xpath->query("//custom[@name='ngremotemedia']");

        $this->output->writeln(sprintf(' -> found %d NGRM custom tags in the field', count($tags)));

        /** @var \DOMElement $tag */
        foreach ($tags as $tag) {
            $resourceId = $this->extractAttribute($tag, 'resourceId');
            $resourceType = $this->extractAttribute($tag, 'resourceType');

            if ($resourceId === null) {
                $tag->parentNode->removeChild($tag);

                continue;
            }

            $resources = $this->getResourcesByResourceId($resourceId);

            if (count($resources) === 0 || ($resourceType !== null && !array_key_exists($resourceType, $resources))) {
                $this->output->writeln(sprintf('   -> remote resource of type \'%s\' with resourceId: \'%s\' hasn\'t been found', $resourceType, $resourceId));

                if ($this->force === true) {
                    $tag->parentNode->removeChild($tag);
                    $this->output->writeln('   -> <info>FORCED SUCCESS</info> NGRM tag in eZ XML has been removed from the field.');

                    continue;
                }

                $this->output->writeln('   -> <question>SKIPPED</question> NGRM tag in eZ XML has been skipped (resolve this manually or use --force to remove the tag from the field)');

                continue;
            }

            if ($resourceType !== null && array_key_exists($resourceType, $resources)) {
                $resource = $resources[$resourceType];
                $this->updateEzXmlTextCustomTag($tag, $resource);
                $this->output->writeln(sprintf('   -> <info>SUCCESS</info> remote resource of type \'%s\' with resourceId \'%s\' has been found and NGRM tag in eZ XML field has been updated', $resource->resourceType, $resource->resourceId));

                continue;
            }

            if ($resourceType === null && count($resources) === 1) {
                $resource = array_pop($resources);
                $this->updateEzXmlTextCustomTag($tag, $resource);
                $this->output->writeln(sprintf('   -> <info>SUCCESS</info> remote resource of type \'%s\' with resourceId \'%s\' has been found and NGRM tag in eZ XML field has been updated', $resource->resourceType, $resource->resourceId));

                continue;
            }

            $this->output->writeln('   -> multiple resources with same resourceId have been found:');

            foreach ($resources as $resource) {
                $this->output->writeln(sprintf('      * [%s] %s', $resource->resourceType, $resource->resourceId));
            }

            if ($this->force === true) {
                $resource = array_pop($resources);
                $this->updateEzXmlTextCustomTag($tag, $resource);
                $this->output->writeln(sprintf('   -> <info>FORCED SUCCESS</info> NGRM tag in eZ XML has been updated with the first found resource of type \'%s\' with resourceId \'%s\'', $resource->resourceType, $resource->resourceId));

                continue;
            }

            $this->output->writeln('   -> <question>SKIPPED</question> NGRM tag in eZ XML has been skipped. Resolve this manually or use --force to automatically select the first found resource');
        }

        $this->updateEzXmlTextAttribute($attribute, $doc);
    }

    private function fillRemoteResources(array $attributes): void
    {
        $resourceIds = [];

        foreach ($attributes as $attribute) {
            if ($attribute['data_type_string'] === 'ngremotemedia') {
                $data = json_decode($attribute['data_text'], true);

                if (is_array($data) && array_key_exists('resourceId', $data) && $data['resourceId'] !== null) {
                    $resourceIds[] = $data['resourceId'];
                }

                continue;
            }

            $doc = new DOMDocument();
            $doc->loadXML($attribute['data_text']);

            $xpath = new DOMXPath($doc);
            $tags = $xpath->query("//custom[@name='ngremotemedia']");

            /** @var \DOMElement $tag */
            foreach ($tags as $tag) {
                $resourceId = $this->extractAttribute($tag, 'resourceId');

                if ($resourceId !== null) {
                    $resourceIds[] = $resourceId;
                }
            }
        }

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

    private function extractAttribute(DOMElement $element, string $attributeName)
    {
        $value = $element->getAttributeNS(self::CUSTOMTAG_NAMESPACE, $attributeName);

        if ($value === '' || $value === 'undefined') {
            return null;
        }

        return $value;
    }

    private function isJson(string $string): bool
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    private function convertCoords(string $coords): ?array
    {
        $parts = explode(',', $coords);

        if (count($parts) < 4) {
            return null;
        }

        foreach ($parts as $part) {
            if (!is_numeric($part)) {
                return null;
            }
        }

        return [
            'x' => (int) trim($parts[0]),
            'y' => (int) trim($parts[1]),
            'w' => (int) trim($parts[2]),
            'h' => (int) trim($parts[3]),
        ];
    }

    private function updateEzXmlTextCustomTag(DOMElement &$tag, Value $value)
    {
        $imageVariations = $this->extractAttribute($tag, 'coords');
        $variation = $this->extractAttribute($tag, 'variation');
        $version = $this->extractAttribute($tag, 'version');
        $caption = $this->extractAttribute($tag, 'caption');
        $cssClass = $this->extractAttribute($tag, 'cssclass');

        if ($variation === null) {
            $variation = $version;
        }

        if ($variation !== null) {
            if (count($this->availableVariations) === 0) {
                $this->availableVariations = $this->variationResolver->getEmbedVariations();
            }

            if (!array_key_exists($variation, $this->availableVariations)) {
                if (!in_array($variation, $this->missingVariations, true)) {
                    $this->missingVariations[] = $variation;
                }
            }
        }

        if (!$this->isJson($imageVariations) && $imageVariations !== null && $variation !== null) {
            $imageVariationsNew = [];
            $coords = $this->convertCoords($imageVariations);

            if ($coords !== null) {
                $imageVariationsNew[$variation] = $coords;
            }

            $imageVariations = json_encode($imageVariationsNew);
        }

        switch ($value->resourceType) {
            case 'image':
                if ($variation !== null && array_key_exists($variation, $this->availableVariations)) {
                    $value->variations = json_decode($imageVariations, true);
                    $imageUrl = $this->provider->buildVariation($value, 'embedded', $variation)->url;

                    break;
                }

                $imageUrl = $value->secure_url;

                break;

            case 'video':
                $imageUrl = $this->provider->getVideoThumbnail($value);

                break;

            default:
                $imageUrl = '';
        }

        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceId');
        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceType');
        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'variation');
        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'coords');
        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'image_url');
        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'caption');
        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'cssclass');
        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'version');
        $tag->removeAttributeNS(self::CUSTOMTAG_NAMESPACE, 'alttext');

        $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'caption', $caption ?: '');
        $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'cssclass', $cssClass ?: '');
        $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'coords', $this->isJson($imageVariations) ? $imageVariations : '[]');
        $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceId', $value->resourceId);
        $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'resourceType', $value->resourceType);
        $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'variation', $variation ?: '');
        $tag->setAttributeNS(self::CUSTOMTAG_NAMESPACE, 'image_url', $imageUrl);
    }

    private function updateNgrmAttribute(array $attribute, Value $value): void
    {
        if ($this->dryRun === true) {
            return;
        }

        $oldDataText = $attribute['data_text'];
        $newDataText = json_encode($value);

        if ($oldDataText === $newDataText) {
            return;
        }

        $attribute['data_text'] = $newDataText;

        $this->updateAttribute($attribute);
    }

    private function updateEzXmlTextAttribute(array $attribute, DOMDocument $document)
    {
        if ($this->dryRun === true) {
            return;
        }

        $oldDataText = $attribute['data_text'];
        $newDataText = $document->saveXML();

        if ($oldDataText === $newDataText) {
            return;
        }

        $attribute['data_text'] = $newDataText;

        $this->updateAttribute($attribute);
    }

    private function updateAttribute(array $attribute): void
    {
        $sql = 'UPDATE ezcontentobject_attribute
                SET data_text=:data_text
                WHERE id=:id AND version=:version';

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('data_text', $attribute['data_text']);
        $query->bindValue('id', $attribute['id']);
        $query->bindValue('version', $attribute['version']);
        $query->execute();
    }

    private function loadAttributes(int $limit, int $offset): array
    {
        $sql = "SELECT DISTINCT
                coa.id, coa.version, coa.data_text, coa.data_type_string, coa.contentobject_id
                FROM ezcontentobject_attribute coa
                JOIN ezcontentobject co on coa.contentobject_id = co.id
                JOIN ezcontentclass cl on co.contentclass_id  = cl.id
                WHERE (coa.data_type_string=:data_type_string_ngrm
                OR (coa.data_type_string=:data_type_string_ezxmltext AND coa.data_text LIKE '%custom name=\"ngremotemedia\"%'))";

        if (count($this->contentIdsFilter) > 0) {
            $sql .= ' AND coa.contentobject_id IN (' . implode(',', $this->contentIdsFilter) . ')';
        }

        $sql .= ' LIMIT :offset,:limit';

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('data_type_string_ngrm', 'ngremotemedia');
        $query->bindValue('data_type_string_ezxmltext', 'ezxmltext');
        $query->bindValue('limit', $limit, ParameterType::INTEGER);
        $query->bindValue('offset', $offset, ParameterType::INTEGER);
        $query->execute();

        return $query->fetchAll();
    }

    private function getAttributesCount(): int
    {
        $sql = "SELECT COUNT(id) AS count
                FROM ezcontentobject_attribute
                WHERE (data_type_string=:data_type_string_ngrm
                OR (data_type_string=:data_type_string_ezxmltext AND data_text LIKE '%custom name=\"ngremotemedia\"%'))";

        if (count($this->contentIdsFilter) > 0) {
            $sql .= ' AND contentobject_id IN (' . implode(',', $this->contentIdsFilter) . ')';
        }

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('data_type_string_ngrm', 'ngremotemedia');
        $query->bindValue('data_type_string_ezxmltext', 'ezxmltext');
        $query->execute();

        return (int) $query->fetch()['count'];
    }

    private function updateExternalData($attribute, Value $value): void
    {
        if ($this->dryRun === true) {
            return;
        }

        $contentId = $attribute['contentobject_id'];
        $fieldId = $attribute['id'];
        $version = $attribute['version'];
        $provider = $this->provider->getIdentifier();

        if ($value->resourceId === null) {
            $sql = 'DELETE FROM ngremotemedia_field_link
                WHERE field_id = :fieldId AND version = :version';

            $query = $this->entityManager->getConnection()->prepare($sql);
            $query->bindValue('fieldId', $fieldId);
            $query->bindValue('version', $version);
            $query->execute();

            return;
        }

        $sql = 'SELECT COUNT(*) as count FROM ngremotemedia_field_link
                WHERE field_id = :fieldId AND version = :version';

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('fieldId', $fieldId);
        $query->bindValue('version', $version);
        $query->execute();

        if ($query->fetch()['count'] > 0) {
            $sql = 'UPDATE ngremotemedia_field_link
                SET resource_id = :resourceId, provider = :provider, contentobject_id = :contentId
                WHERE field_id = :fieldId AND version = :version';

            $query = $this->entityManager->getConnection()->prepare($sql);
            $query->bindValue('resourceId', $value->resourceId);
            $query->bindValue('provider', $provider);
            $query->bindValue('contentId', $contentId);
            $query->bindValue('fieldId', $fieldId);
            $query->bindValue('version', $version);
            $query->execute();

            return;
        }

        $sql = 'INSERT INTO ngremotemedia_field_link (field_id, version, resource_id, provider, contentobject_id)
                VALUES (:fieldId, :version, :resourceId, :provider, :contentId)';

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('contentId', $contentId);
        $query->bindValue('fieldId', $fieldId);
        $query->bindValue('version', $version);
        $query->bindValue('resourceId', $value->resourceId);
        $query->bindValue('provider', $provider);
        $query->execute();
    }

    private function apiRateLimitExceeded()
    {
        $usage = $this->provider->usage();

        $allowed = $usage['rate_limit_allowed'];
        $remaining = $usage['rate_limit_remaining'];

        $remainingPercent = $remaining / $allowed * 100;

        if ($remainingPercent < $this->apiRateLimitThreshold) {
            return true;
        }

        return false;
    }
}
