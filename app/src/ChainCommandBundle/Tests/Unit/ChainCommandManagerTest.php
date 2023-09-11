<?php
declare(strict_types=1);

namespace App\ChainCommandBundle\Tests\Unit;

use App\ChainCommandBundle\Service\ChainCommandManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ChainCommandManagerTest
 *
 * This class conducts unit tests on the ChainCommandManager to ensure its
 * functionality, especially regarding master and chained commands.
 *
 * @extends TestCase
 */
class ChainCommandManagerTest extends TestCase
{
    /**
     * The service under test.
     *
     * @var ChainCommandManager
     */
    private ChainCommandManager $manager;

    protected function setUp(): void
    {
        $this->manager = new ChainCommandManager();
    }

    public function testAddChain(): void
    {
        $this->manager->addChain('root_command', ['member1', 'member2']);
        $chainedCommands = $this->manager->getChainedCommandsFor('root_command');

        $this->assertContains('member1', $chainedCommands);
        $this->assertContains('member2', $chainedCommands);
    }

    public function testGetChainedCommandsForNonExistentCommand(): void
    {
        $this->assertNull($this->manager->getChainedCommandsFor('non_existent_command'));
    }

    public function testIsRootCommand(): void
    {
        $this->manager->addChain('root_command', ['member1', 'member2']);

        $this->assertTrue($this->manager->isRootCommand('root_command'));
    }

    public function testIsNotRootCommand(): void
    {
        $this->assertFalse($this->manager->isRootCommand('non_existent_command'));
    }

    public function testGetRootCommandOf(): void
    {
        $this->manager->addChain('root_command', ['member1', 'member2']);

        $this->assertEquals('root_command', $this->manager->getRootCommandOf('member1'));
    }

    public function testGetRootCommandOfNonExistentMember(): void
    {
        $this->assertNull($this->manager->getRootCommandOf('non_existent_member'));
    }

    public function testGetRootCommandOfNonMemberCommand(): void
    {
        $this->manager->addChain('root_command', ['member1', 'member2']);

        $this->assertNull($this->manager->getRootCommandOf('another_root_command'));
    }

    public function testAddChainStructure(): void
    {
        $this->manager->addChain('root_command', ['member_command']);
        $this->manager->addChain('root2_command', ['test_command']);

        $expectedStructure = [
            'root_command' => ['member_command'],
            'root2_command' => ['test_command']
        ];

        $this->assertEquals($expectedStructure, $this->getChainCommandsFromManager());
    }

    private function getChainCommandsFromManager(): array
    {
        $reflection = new \ReflectionClass($this->manager);
        $property = $reflection->getProperty('chainCommands');
        $property->setAccessible(true);

        return $property->getValue($this->manager);
    }

    public function testAddNonStringCommandName(): void
    {
        $this->expectException(\TypeError::class);
        $this->manager->addChain(123, ['member_command']);
    }

    public function testAddNonArrayMemberCommands(): void
    {
        $this->expectException(\TypeError::class);
        $this->manager->addChain('root_command', 'member_command');
    }

    public function testGetRootCommandOfNonExistingMember(): void
    {
        $result = $this->manager->getRootCommandOf('non_existing_command');
        $this->assertNull($result);
    }

    public function testCommandCannotBeItsOwnMember(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The command "root_command" cannot be its own member.');

        $this->manager->addChain('root_command', ['root_command']);
    }

    public function testMemberCannotBeSetAsRoot(): void
    {
        $this->manager->addChain('root_command', ['member_command']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The command "member_command" is already a member of "root_command" and cannot be set as a root command.');

        $this->manager->addChain('member_command', ['test_command']);
    }
}
