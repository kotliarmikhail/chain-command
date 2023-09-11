<?php
declare(strict_types=1);

namespace App\ChainCommandBundle\Tests\Unit;

use App\ChainCommandBundle\EventListener\CommandChainListener;
use App\ChainCommandBundle\Service\ChainCommandManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class CommandChainListenerTest
 *
 * Unit tests for the CommandChainListener class. It tests the behavior
 * of the listener during console command events and ensures proper
 * integration with the CommandChainService.
 *
 * @extends TestCase
 */
class CommandChainListenerTest extends TestCase
{
    private ChainCommandManager $chainCommandManager;
    private LoggerInterface $logger;
    private CommandChainListener $listener;
    private Command $command;
    private Application $application;

    protected function setUp(): void
    {
        $this->chainCommandManager = $this->createMock(ChainCommandManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new CommandChainListener($this->chainCommandManager, $this->logger);
        $this->command = $this->createMock(Command::class);
        $this->application = $this->createMock(Application::class);
        $this->command->method('getApplication')->willReturn($this->application);
    }

    public function testOnConsoleCommandWithRootCommand(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getFirstArgument')->willReturn('root:command');

        $this->chainCommandManager->method('isRootCommand')
            ->willReturn(true);
        $this->chainCommandManager->method('getChainedCommandsFor')
            ->willReturn(['test:hello', 'test:hi']);

        $this->logger->expects($this->exactly(4))
            ->method('info');


        $event = new ConsoleCommandEvent(
            $this->createMock(Command::class),
            $input,
            $this->createMock(OutputInterface::class)
        );

        $this->listener->onConsoleCommand($event);
    }

    public function testOnConsoleCommandWithMemberErrorCommand(): void
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getFirstArgument')->willReturn('test:hello');

        $this->chainCommandManager->method('isRootCommand')
            ->willReturn(false);
        $this->chainCommandManager->method('getRootCommandOf')
            ->willReturn('root:command');

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with(
                'Error: test:hello command is a member of root:command command chain and cannot be executed on its own.'
            );;

        $event = new ConsoleCommandEvent(
            $this->createMock(Command::class),
            $input,
            $output
        );

        $this->listener->onConsoleCommand($event);
    }

    public function testOnConsoleTerminateWithNonRootCommand(): void
    {
        $this->command->method('getName')->willReturn('test:hello');
        $this->chainCommandManager->method('isRootCommand')->willReturn(false);

        $event = new ConsoleTerminateEvent(
            $this->command,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
            0
        );

        $this->listener->onConsoleTerminate($event);

        $this->assertTrue(true);
    }

    public function testOnConsoleTerminateWithRootCommand(): void
    {
        $this->command->method('getName')->willReturn('root:command');
        $this->chainCommandManager->method('isRootCommand')->willReturn(true);
        $this->chainCommandManager->method('getChainedCommandsFor')
            ->willReturn(['test:hello', 'test:hi']);

        $this->application->method('get')->willReturn($this->command);

        $this->logger->expects($this->exactly(2))->method('info');

        $event = new ConsoleTerminateEvent(
            $this->command,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
            0
        );

        $this->listener->onConsoleTerminate($event);
    }

    public function testOnConsoleTerminateWithNullApplication(): void
    {
        $mockCommand = $this->createMock(Command::class);
        $mockCommand->method('getName')->willReturn('root:command');
        $this->chainCommandManager->method('isRootCommand')->willReturn(true);
        $this->chainCommandManager->method('getChainedCommandsFor')
            ->willReturn(['test:hello', 'test:hi']);

        $mockCommand->method('getApplication')->willReturn(null);

        $this->logger->expects($this->never())
            ->method('info');

        $event = new ConsoleTerminateEvent(
            $mockCommand,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class),
            0
        );

        $this->listener->onConsoleTerminate($event);
    }
}