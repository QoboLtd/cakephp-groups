<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Groups\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Webmozart\Assert\Assert;

/**
 * Group shell
 *
 * @property \Groups\Shell\Task\AssignTask $Assign
 * @property \Groups\Shell\Task\ImportTask $Import
 * @property \Groups\Shell\Task\SyncLdapGroupsTask $SyncLdapGroups
 */
class GroupShell extends Shell
{
    /**
     * Contains tasks to load and instantiate
     *
     * @var array|bool
     * @link https://book.cakephp.org/3.0/en/console-and-shells.html#Shell::$tasks
     */
    public $tasks = [
        'Groups.Assign',
        'Groups.Import',
        'Groups.SyncLdapGroups',
    ];

    /**
     * Gets the option parser instance and configures it.
     *
     * By overriding this method you can configure the ConsoleOptionParser before returning it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     * @link https://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        Assert::isInstanceOf($parser, ConsoleOptionParser::class);

        $parser
            ->setDescription("Groups management tasks.")
            ->addSubcommand(
                'assign',
                ['help' => 'Assign group to all users.', 'parser' => $this->Assign->getOptionParser()]
            )
            ->addSubcommand(
                'import',
                ['help' => 'Import system groups.', 'parser' => $this->Import->getOptionParser()]
            )
            ->addSubcommand(
                'sync_ldap_groups',
                ['help' => 'LDAP groups synchronization.', 'parser' => $this->SyncLdapGroups->getOptionParser()]
            );

        return $parser;
    }
}
