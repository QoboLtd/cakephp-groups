<?php
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;

/**
 * Groups configuration
 */
// get app level config
$config = Configure::read('Groups');
$config = $config ? $config : [];
// load default plugin config
Configure::load('Groups.groups');
// overwrite default plugin config by app level config
Configure::write('Groups', array_replace_recursive(
    Configure::read('Groups'),
    $config
));

/**
 * @todo This should be moved to its own file
 */
EventManager::instance()->on(
    'Model.afterSaveCommit',
    // Link newly created users to default group
    function (Event $event, $entity) {
        $usersTable = Configure::read('Auth.authenticate.all.userModel');
        $usersTable = array_merge(['CakeDC/Users.Users'], [$usersTable]);
        if (!in_array($event->subject()->registryAlias(), $usersTable)) {
            return;
        }

        // skip existing users
        if (!$entity->isNew()) {
            return;
        }

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
);
