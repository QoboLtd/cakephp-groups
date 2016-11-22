<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;

/**
 * @todo This should be moved to its own file
 */
EventManager::instance()->on(
    'Model.afterSaveCommit',
    // Link newly created users to default group
    function (Event $event, $entity) {
        if ('CakeDC/Users.Users' === $event->subject()->registryAlias()) {
            $table = TableRegistry::get('Groups.Groups');
            // get default group
            $group = $table->find('all', [
                'conditions' => [
                    'name' => Configure::read('Groups.defaultGroup'),
                    'deny_edit' => 1,
                    'deny_delete' => 1
                ]
            ])->first();

            // skip if default group is not found
            if (!$group) {
                return;
            }

            // need to re-fetch the user entity from the database
            // as is still considered as new and link() fails.
            $user = $table->Users->get($entity->id);

            $table->Users->link($group, [$user]);
        }
    }
);
