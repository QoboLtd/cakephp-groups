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
            // get default group name
            $defaultGroupName = Configure::read('Groups.defaultGroup');
            if (!$defaultGroupName) {
                return;
            }
            $table = TableRegistry::get('Groups.Groups');
            // get default group entity
            $group = $table->findByName($defaultGroupName)->first();

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
