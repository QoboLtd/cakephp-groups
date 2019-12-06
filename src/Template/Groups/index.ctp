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

use Cake\Core\Configure;

echo $this->Html->css('Qobo/Utils./plugins/datatables/css/dataTables.bootstrap.min', ['block' => 'css']);

echo $this->Html->script(
    [
        'Qobo/Utils./plugins/datatables/datatables.min',
        'Qobo/Utils./plugins/datatables/js/dataTables.bootstrap.min',
    ],
    ['block' => 'scriptBottom']
);

echo $this->Html->scriptBlock(
    '$(".table-datatable").DataTable({
        stateSave: true,
        stateDuration: ' . (int)(Configure::read('Session.timeout') * 60) . '
    });',
    ['block' => 'scriptBottom']
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __d('Groups', 'Groups'); ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                <?= $this->Html->link(
                    '<i class="fa fa-plus"></i> ' . __d('Groups', 'Add'),
                    ['plugin' => 'Groups', 'controller' => 'Groups', 'action' => 'add'],
                    ['escape' => false, 'title' => __d('Groups', 'Add'), 'class' => 'btn btn-default']
                ); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable">
                <thead>
                    <tr>
                        <th><?= __d('Groups', 'Name') ?></th>
                        <th><?= __d('Groups', 'Users') ?></th>
                        <th class="actions"><?= __d('Groups', 'Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $group) : ?>
                    <tr>
                        <td>
                            <?= h($group->name) ?>
                            <p class="text-muted"><?= h($group->description) ?></p>
                        </td>
                        <td>
                            <?php
                            if (!empty($group->users)) {
                                $users = [];
                                foreach ($group->users as $user) {
                                    $users[] = $this->Html->link($user->username, '/users/view/' . $user->id, ['class' => "label label-primary"]);
                                }
                                print implode(' ', $users);
                            }
                            ?>
                        </td>
                        <td class="actions">
                            <div class="btn-group btn-group-xs" role="group">
                            <?= $this->Html->link(
                                '<i class="fa fa-eye"></i>',
                                ['plugin' => 'Groups', 'controller' => 'Groups', 'action' => 'view', $group->id],
                                ['title' => __d('Groups', 'View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ); ?>
                            <?php if (!$group->deny_edit) : ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-pencil"></i>',
                                    ['plugin' => 'Groups', 'controller' => 'Groups', 'action' => 'edit', $group->id],
                                    ['title' => __d('Groups', 'Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                ); ?>
                            <?php endif; ?>
                            <?php if (!$group->deny_delete) : ?>
                                <?= $this->Form->postLink(
                                    '<i class="fa fa-trash"></i>',
                                    ['plugin' => 'Groups', 'controller' => 'Groups', 'action' => 'delete', $group->id],
                                    [
                                        'confirm' => __d('Groups', 'Are you sure you want to delete {0}?', $group->name),
                                        'title' => __d('Groups', 'Delete'),
                                        'class' => 'btn btn-default btn-sm',
                                        'escape' => false
                                    ]
                                ) ?>
                            <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
