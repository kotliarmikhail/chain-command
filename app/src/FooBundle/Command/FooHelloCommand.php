<?php
declare(strict_types=1);

namespace App\FooBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FooHelloCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('foo:hello');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello from Foo!');
        return Command::SUCCESS;
    }
}
