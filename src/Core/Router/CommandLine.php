<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Attribute\Command\Argument;
use App\Attribute\Command\Command;
use App\Attribute\Command\Option;
use App\Core\Controller\AbstractCommand;
use App\Core\Http\Response;
use App\Exception\Core\CommandException;
use App\Kernel;

final class CommandLine
{
    /** @var array */
    private array $commands = [];

    public function resolve(?string $name, array $arguments, array $options): string
    {
        $commandMeta = $this->commands[$name] ?? null;
        if ($commandMeta === null) {
            throw new CommandException("Command not found by name: {$name}", Response::HTTP_NOT_FOUND);
        }

        $command = new $commandMeta['class']();
        $reflectionCommand = new \ReflectionClass($commandMeta['class']);
        $runArguments = [];
        $runOptions = [];
        foreach ($reflectionCommand->getMethods() as $method) {
            if ($method->name === 'run') {
                $attributes = $method->getAttributes(Argument::class);
                foreach ($attributes as $i => $attribute) {
                    /** @var Argument $a */
                    $a = $attribute->newInstance();
                    if (array_key_exists($i, $arguments)) {
                        $runArguments[$a->name] = $a->value($arguments[$i]);
                    } else {
                        if ($a->required) {
                            throw new CommandException("Required argument \"{$a->name}\" not specified");
                        }
                        $runArguments[$a->name] = $a->default;
                    }
                }

                $attributes = $method->getAttributes(Option::class);
                foreach ($attributes as $attribute) {
                    /** @var Option $o */
                    $o = $attribute->newInstance();
                    if (array_key_exists($o->name, $options)) {
                        $runOptions[$o->name] = $o->value($options[$o->name]);
                    } else {
                        if ($o->required) {
                            throw new CommandException("Required option \"{$o->name}\" not specified");
                        }
                        $runArguments[$o->name] = $o->default;
                    }
                }
            }
        }

        return match ($command->run($runArguments, $runOptions)) {
            AbstractCommand::EXIT_OK => 'OK',
            AbstractCommand::EXIT_ERROR => 'Failed!',
            default => throw new CommandException('Unknown command error'),
        };
    }

    /**
     * @param array $commands
     */
    public function registerCommandAttributes(array $commands): void
    {
        foreach ($commands as $command) {
            try {
                $reflectionCommand = new \ReflectionClass($command);
                $reflectionCommandAttributes = $reflectionCommand->getAttributes(Command::class);
                foreach ($reflectionCommandAttributes as $reflectionCommandAttribute) {
                    /** @var Command $attribute */
                    $attribute = $reflectionCommandAttribute->newInstance();
                    $this->commands[$attribute->name] = [
                        'class' => $command,
                        'description' => $attribute->description,
                    ];
                }
            } catch (\Throwable) {}
        }
    }
}
