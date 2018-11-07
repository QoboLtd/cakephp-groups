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
namespace Groups\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;

/**
 * Import Task
 *
 * Import configured system groups, like Admins and Everyone.
 */
class ImportTask extends Shell
{
    /**
     * Main task method
     *
     * @return bool True on success, false otherwise
     */
    public function main()
    {
        $this->info('Task: import system groups');
        $this->hr();

        $systemGroups = Configure::read('Groups.systemGroups');
        if (empty($systemGroups)) {
            $this->warn("System groups are not configured.  Nothing to do.");

            return true;
        }

        // get groups table
        $table = TableRegistry::get('Groups.Groups');

        foreach ($systemGroups as $group) {
            if (empty($group['name'])) {
                $this->warn("Skipping group without a name.");
                continue;
            }

            if ($table->exists(['name' => $group['name']])) {
                $this->warn("Group [" . $group['name'] . "] already exists. Skipping.");
                continue;
            }

            $this->info("Group [" . $group['name'] . "] does not exist. Creating.");
            $entity = $table->newEntity();
            $entity = $table->patchEntity($entity, $group);
            $result = $table->save($entity);
            if (!$result) {
                $this->err("Errors: \n" . implode("\n", $this->getImportErrors($entity)));
                $this->abort("Failed to create group [" . $group['name'] . "]");
            }
        }

        $this->success('System groups imported successfully');
    }

    /**
     * Get import errors from entity object.
     *
     * @param  \Cake\Datasource\EntityInterface $entity Entity instance
     * @return string[]
     */
    protected function getImportErrors(EntityInterface $entity): array
    {
        $result = [];

        /**
         * @var array $errors
         */
        $errors = $entity->errors();

        if (empty($errors)) {
            return $result;
        }

        foreach ($errors as $field => $error) {
            $msg = "[$field] ";
            $msg .= is_array($error) ? implode(', ', $error) : $error;
            $result[] = $msg;
        }

        return $result;
    }
}
