<?php

declare(strict_types=1);

namespace Netgen\Bundle\RemoteMediaBundle\Tests\Command;

use Netgen\Bundle\RemoteMediaBundle\Command\ShowApiUsageCommand;
use Netgen\RemoteMedia\API\ProviderInterface;
use Netgen\RemoteMedia\API\Values\StatusData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

use function trim;

final class ShowApiUsageCommandTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Netgen\RemoteMedia\API\ProviderInterface */
    private MockObject $providerMock;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->providerMock = $this->createMock(ProviderInterface::class);

        $application = new Application();
        $application->add(new ShowApiUsageCommand($this->providerMock));

        $command = $application->find('netgen:remote_media:usage');
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @covers \Netgen\Bundle\RemoteMediaBundle\Command\ShowApiUsageCommand::__construct
     * @covers \Netgen\Bundle\RemoteMediaBundle\Command\ShowApiUsageCommand::configure
     * @covers \Netgen\Bundle\RemoteMediaBundle\Command\ShowApiUsageCommand::execute
     * @covers \Netgen\Bundle\RemoteMediaBundle\Command\ShowApiUsageCommand::getPrettyKey
     */
    public function testExecute(): void
    {
        $statusData = new StatusData([
            'plan' => 'Advanced',
            'api_rate_limit' => 1000,
            'resources' => 500,
            'variations' => 3000,
        ]);

        $this->providerMock
            ->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('cloudinary');

        $this->providerMock
            ->expects(self::once())
            ->method('status')
            ->willReturn($statusData);

        $this->commandTester->execute([]);

        self::assertSame(
            'Source: Cloudinary'
                . PHP_EOL . 'Plan: Advanced'
                . PHP_EOL . 'Api rate limit: 1000'
                . PHP_EOL . 'Resources: 500'
                . PHP_EOL . 'Variations: 3000',
            trim($this->commandTester->getDisplay()),
        );
    }
}
