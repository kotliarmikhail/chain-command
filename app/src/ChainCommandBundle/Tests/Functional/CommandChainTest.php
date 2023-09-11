<?php
declare(strict_types=1);

namespace App\ChainCommandBundle\Tests\Functional;

use App\BarBundle\Command\BarHiCommand;
use App\ChainCommandBundle\Service\ChainCommandManager;
use App\FooBundle\Command\FooHelloCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class CommandChainTest
 *
 * Functional tests to ensure the correct behavior of the command chain
 * mechanism. It tests whether master commands can execute their chained
 * commands and also ensures that chained commands cannot be executed individually.
 *
 * @extends KernelTestCase
 */
class CommandChainTest extends KernelTestCase
{
    protected Application $application;
    protected OutputInterface $bufferedOutput;
    protected LoggerInterface $logger;
    protected ?EventDispatcher $dispatcher;
    protected ChainCommandManager $manager;

    /**
     * Setup necessary dependencies and mocks for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $kernel = self::bootKernel();
        $kernel->getContainer();
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $this->dispatcher = $kernel->getContainer()->get('event_dispatcher');
        $this->manager = new ChainCommandManager();
        $this->bufferedOutput = new BufferedOutput();
    }

    /**
     * Test if master commands can successfully execute their chained commands.
     */
    public function testChainCommands(): void
    {
        $this->manager->addChain((new FooHelloCommand())->getName(), [(new BarHiCommand())->getName()]);
        $this->application->run(
            new ArrayInput([(new FooHelloCommand())->getName()]),
            $this->bufferedOutput
        );

        static::assertEquals('Hello from Foo!' . PHP_EOL . 'Hi from Bar!' . PHP_EOL, $this->bufferedOutput->fetch());
    }

    /**
     * Test if chained commands cannot be executed individually.
     */
    public function testChildExecutionCommand(): void
    {
        $this->manager->addChain((new FooHelloCommand())->getName(), [(new BarHiCommand())->getName()]);
        $this->application->run(
            new ArrayInput([(new BarHiCommand())->getName()]),
            $this->bufferedOutput
        );

        self::assertEquals(
            'Error: bar:hi command is a member of foo:hello command chain and cannot be executed on its own.' . PHP_EOL,
            $this->bufferedOutput->fetch()
        );
    }
}