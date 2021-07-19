<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Command;

use Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function str_replace;
use function ucfirst;

class ShowApiUsageCommand extends Command
{
    /**
     * @var \Netgen\Bundle\RemoteMediaBundle\RemoteMedia\RemoteMediaProvider
     */
    private $provider;

    public function __construct(RemoteMediaProvider $provider)
    {
        $this->provider = $provider;

        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->setName('netgen:ngremotemedia:usage')
            ->setDescription('Show API usage (rate limits etc.)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $usage = $this->provider->usage();

        foreach ($usage as $key => $value) {
            $output->writeln($this->getPrettyKey($key) . ': <comment>' . $value . '</comment>');
        }

        return 0;
    }

    private function getPrettyKey(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}
