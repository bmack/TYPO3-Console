<?php
declare(strict_types=1);
namespace Helhum\Typo3Console\Command\Extension;

/*
 * This file is part of the TYPO3 Console project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 *
 */

use Helhum\Typo3Console\Core\Booting\CompatibilityScripts;
use Helhum\Typo3Console\Mvc\Cli\Symfony\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExtensionDeactivateCommand extends Command
{
    protected function configure()
    {
        $this->setDescription('Deactivate extension(s)');
        $this->setHelp(
            <<<'EOH'
Deactivates one or more extensions by key.
Marks extensions as inactive in the system and clears caches for every deactivated extension.

This command is deprecated (and hidden) in Composer mode.
EOH
        );
        $this->addArgument(
            'extensionKeys',
            InputArgument::REQUIRED,
            'Extension keys to deactivate. Separate multiple extension keys with comma'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extensionKeys = explode(',', $input->getArgument('extensionKeys'));

        $this->showDeprecationMessageIfApplicable($output);
        (new ExtensionStateCommandsHelper($output))->deactivateExtensions($extensionKeys);
    }

    private function showDeprecationMessageIfApplicable(OutputInterface $output)
    {
        if (CompatibilityScripts::isComposerMode()) {
            $output->writeln('<warning>This command is deprecated when TYPO3 is composer managed.</warning>');
            $output->writeln('<warning>It might lead to unexpected results.</warning>');
            $output->writeln('<warning>The PackageStates.php file that tracks which extension should be active,</warning>');
            $output->writeln('<warning>should be generated automatically using install:generatepackagestates.</warning>');
            $output->writeln('<warning>To set up all active extensions, extension:setupactive should be used.</warning>');
            $output->writeln('<warning>This command will be disabled, when TYPO3 is composer managed, in TYPO3 Console 6</warning>');
        }
    }

    public function isHidden(): bool
    {
        $application = $this->getApplication();
        if (!$application instanceof Application || getenv('TYPO3_CONSOLE_RENDERING_REFERENCE')) {
            return true;
        }

        return !$application->isComposerManaged();
    }
}
