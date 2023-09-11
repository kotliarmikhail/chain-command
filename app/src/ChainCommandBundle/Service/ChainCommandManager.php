<?php
declare(strict_types=1);

namespace App\ChainCommandBundle\Service;

class ChainCommandManager
{
    /**
     * Array structure:
     * [
     *     'root_command' => ['member_command_1', 'member_command_2', ...]
     * ]
     *
     * @var array
     */
    private $chainCommands = [];

    /**
     * Add a root command with its associated member commands.
     *
     * @param string $rootCommand
     * @param array $memberCommands
     */
    public function addChain(string $rootCommand, array $memberCommands): void
    {
        // Check if root command is already a member of any chain
        foreach ($this->chainCommands as $existingRoot => $members) {
            if (in_array($rootCommand, $members, true)) {
                throw new \LogicException(sprintf('The command "%s" is already a member of "%s" and cannot be set as a root command.', $rootCommand, $existingRoot));
            }
        }

        // Check if root command is trying to add itself as a member
        if (in_array($rootCommand, $memberCommands, true)) {
            throw new \LogicException(sprintf('The command "%s" cannot be its own member.', $rootCommand));
        }

        if (!isset($this->chainCommands[$rootCommand])) {
            $this->chainCommands[$rootCommand] = [];
        }

        // Use array merge to ensure uniqueness of commands and prevent duplicates.
        $this->chainCommands[$rootCommand] = array_unique(array_merge($this->chainCommands[$rootCommand], $memberCommands));
    }

    /**
     * Retrieve all chained commands for a given root command.
     *
     * @param string $rootCommand
     * @return array|null
     */
    public function getChainedCommandsFor(string $rootCommand): ?array
    {
        return $this->chainCommands[$rootCommand] ?? null;
    }

    /**
     * Check if a command is a root command.
     *
     * @param string $command
     * @return bool
     */
    public function isRootCommand(string $command): bool
    {
        return isset($this->chainCommands[$command]);
    }

    /**
     * Get the root command of a given member command.
     *
     * @param string $memberCommand
     * @return string|null The root command or null if the member command is not found.
     */
    public function getRootCommandOf(string $memberCommand): ?string
    {
        foreach ($this->chainCommands as $root => $members) {
            if (in_array($memberCommand, $members, true)) {
                return $root;
            }
        }
        return null;
    }
}