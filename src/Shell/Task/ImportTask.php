<?php
namespace Groups\Shell\Task;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Task for assign default group to all users.
 */
class ImportTask extends Shell
{
    /**
     * {@inheritDoc}
     */
    public function main()
    {
        $this->out('Task: import system group(s)');
        $this->hr();

        // get groups table
        $table = TableRegistry::get('Groups.Groups');

        $systemGroups = $this->_getSystemGroups();
        if ($systemGroups) {
            foreach ($systemGroups as $group) {
                $entity = $table->newEntity();
                foreach ($group as $k => $v) {
                    $entity->{$k} = $v;
                }
                $saved = $table->save($entity);
                if ($saved) {
                    $this->out('Group [' . $entity->name . '] imported successfully');
                } else {
                    $this->err('Failed to import group [' . $entity->name . ']');
                    $errors = $this->_getImportErrors($entity);
                    if (!empty($errors)) {
                        $this->out(implode("\n", $errors));
                        $this->hr();
                    }
                }
            }
        }

        $this->out('<success>System group(s) imporitng task completed</success>');
    }

    /**
     * Get default group name.
     *
     * @return string|null
     */
    protected function _getSystemGroups()
    {
        $result = [
            [
                'name' => 'Admins',
                'description' => 'Administrators of the system',
                'deny_edit' => true,
                'deny_delete' => true
            ],
            [
                'name' => 'Everyone',
                'description' => 'All users',
                'deny_edit' => true,
                'deny_delete' => true
            ]
        ];

        if (empty($result)) {
            $this->err('System groups are not defined, all following tasks are skipped');
        }

        return $result;
    }

    /**
     * Get import errors from entity object.
     *
     * @param  \Cake\ORM\Entity $entity Entity instance
     * @return array
     */
    protected function _getImportErrors($entity)
    {
        $result = [];
        if (!empty($entity->errors())) {
            foreach ($entity->errors() as $field => $error) {
                if (is_array($error)) {
                    $msg = implode(', ', $error);
                } else {
                    $msg = $errors;
                }
                $result[] = $msg . ' [' . $field . ']';
            }
        }

        return $result;
    }
}
