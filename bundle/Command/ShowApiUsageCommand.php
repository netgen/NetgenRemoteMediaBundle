<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Netgen\RemoteMedia\API\ProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function str_replace;
use function ucfirst;

final class ShowApiUsageCommand extends Command
{
    private ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->setName('netgen:remote_media:usage')
            ->setDescription('Show API usage (rate limits etc.)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statusData = $this->provider->status();

        $output->writeln('Source: <comment>' . $this->getPrettyKey($this->provider->getIdentifier()) . '</comment>');
        foreach ($statusData->all() as $key => $value) {
            $output->writeln($this->getPrettyKey($key) . ': <comment>' . $value . '</comment>');
        }

        return Command::SUCCESS;
    }

    private function getPrettyKey(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}
