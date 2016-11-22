<?php
namespace Groups\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;

class GroupShell extends Shell
{
    /**
     * {@inheritDoc}
     */
    public $tasks = [
        'Groups.Assign',
        'Groups.Import'
    ];

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser
            ->description('Qobo Groups Shell that handle\'s related tasks.')
            ->addSubcommand(
                'assign',
                ['help' => 'Assign group to all users.', 'parser' => $this->Assign->getOptionParser()]
            )
            ->addSubcommand(
                'import',
                ['help' => 'Import system groups.', 'parser' => $this->Import->getOptionParser()]
            );

        return $parser;
    }
}
