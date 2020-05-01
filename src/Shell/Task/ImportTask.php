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
use Webmozart\Assert\Assert;

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
            $this->warn("System groups are not configured. Nothing to do.");

            return true;
        }

        // get groups table
        $table = TableRegistry::getTableLocator()->get('Groups.Groups');

        foreach ($systemGroups as $group) {
            if (empty($group['name'])) {
                $this->warn("Skipping group without a name.");
                continue;
            }

            $entity = $table->find()->where(['name' => $group['name']])->first();
            Assert::nullOrIsInstanceOf($entity, EntityInterface::class);

            if (null !== $entity && $entity->get('deny_edit')) {
                $this->warn(sprintf('Group "%s" already exists and is not allowed to be modified.', $group['name']));
                continue;
            }

            null === $entity ?
                $this->info(sprintf('Creating group "%s".', $group['name'])) :
                $this->info(sprintf('Updating group "%s".', $group['name']));

            $entity = null === $entity ? $table->newEntity() : $entity;
            $entity = $table->patchEntity($entity, $group);

            if (! $table->save($entity)) {
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
        foreach ($entity->getErrors() as $field => $error) {
            $msg = "[$field] ";
            $msg .= is_array($error) ? implode(', ', $error) : $error;
            $result[] = $msg;
        }

        return $result;
    }
}
