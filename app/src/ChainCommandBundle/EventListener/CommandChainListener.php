<?php
declare(strict_types=1);

namespace App\ChainCommandBundle\EventListener;

use App\ChainCommandBundle\Service\ChainCommandManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Listener for handling chain commands.
 */
class CommandChainListener
{
    private ChainCommandManager $chainCommandManager;
    private LoggerInterface $logger;

    public function __construct(ChainCommandManager $chainCommandManager, LoggerInterface $logger)
    {
        $this->chainCommandManager = $chainCommandManager;
        $this->logger = $logger;
    }

    /**
     * Handles the console command event.
     *
     * @param ConsoleCommandEvent $event Console command event.
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $commandName = $event->getInput()->getFirstArgument();

        if (null === $commandName) {
            return;
        }

        if ($this->chainCommandManager->isRootCommand($commandName)) {
            $this->handleRootCommand($commandName, $event);
            $event->disableCommand();
            return;
        }

        $rootCommand = $this->chainCommandManager->getRootCommandOf($commandName);
        if ($rootCommand) {
            $this->handleMemberCommand($commandName, $rootCommand, $event);
            $event->disableCommand();
        }
    }

    /**
     * Handles the console terminate event.
     *
     * @param ConsoleTerminateEvent $event Console terminate event.
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        $commandName = $event->getCommand()->getName();
        $application = $event->getCommand()->getApplication();
        if (!$application || !$this->chainCommandManager->isRootCommand($commandName)) {
            return;
        }

        $this->logger->info(sprintf('Executing %s chain members:', $commandName));

        foreach ($this->chainCommandManager->getChainedCommandsFor($commandName) as $memberCommand) {
            $this->runCommand(
                $application->get($memberCommand),
                $event->getInput(),
                $event->getOutput()
            );
        }

        $this->logger->info(sprintf('Execution of %s chain completed.', $commandName));
    }

    /**
     * Utility method to run a specific command and log its output.
     *
     * @param Command $command Command to be executed.
     * @param InputInterface $input The input interface instance.
     * @param OutputInterface $output The output interface instance.
     */
    private function runCommand(Command $command, InputInterface $input, OutputInterface $output): void
    {
        $bufferedOutput = new BufferedOutput();
        $exitCode = $command->run($input, $bufferedOutput);

        if ($exitCode !== 0) {
            $output->writeln(sprintf('Command "%s" failed with exit code: %d', $command->getName(), $exitCode));
            return;
        }

        $capturedMessage = trim($bufferedOutput->fetch());
        if (!empty($capturedMessage)) {
            $this->logger->info($capturedMessage);
            $output->writeln($capturedMessage);
        }
    }

    private function handleRootCommand(string $commandName, ConsoleCommandEvent $event): void
    {
        $this->logger->info(sprintf('%s is a master command of a command chain that has registered member commands', $commandName));

        $memberCommands = $this->chainCommandManager->getChainedCommandsFor($commandName);
         foreach ($memberCommands as $memberCommand) {
            $this->logger->info(sprintf('%s registered as a member of %s command chain', $memberCommand, $commandName));
        }

        $this->logger->info(sprintf('Executing %s command itself first:', $commandName));
        $this->runCommand($event->getCommand(), $event->getInput(), $event->getOutput());
    }

    private function handleMemberCommand(string $commandName, string $rootCommand, ConsoleCommandEvent $event): void
    {
        $event->getOutput()->writeln(sprintf('Error: %s command is a member of %s command chain and cannot be executed on its own.', $commandName, $rootCommand));
    }
}