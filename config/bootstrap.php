<?php
use Cake\Core\Configure;

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
