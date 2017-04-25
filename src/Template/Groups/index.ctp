<?php
echo $this->Html->css('AdminLTE./plugins/datatables/dataTables.bootstrap', ['block' => 'css']);
echo $this->Html->script(
    [
        'AdminLTE./plugins/datatables/jquery.dataTables.min',
        'AdminLTE./plugins/datatables/dataTables.bootstrap.min'
    ],
    [
        'block' => 'scriptBotton'
    ]
);
echo $this->Html->scriptBlock(
    '$(".table-datatable").DataTable({});',
    ['block' => 'scriptBotton']
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Groups'); ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm" role="group">
                <?= $this->Html->link(
                    '<i class="fa fa-plus"></i> ' . __('Add'),
                    ['plugin' => 'Groups', 'controller' => 'Groups', 'action' => 'add'],
                    ['escape' => false, 'title' => __('Add'), 'class' => 'btn btn-default']
                ); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">
            <table class="table table-hover table-condensed table-vertical-align table-datatable">
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('name') ?></th>
                        <th><?= __('Users') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
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
                                ['title' => __('View'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                            ); ?>
                            <?php if (!$group->deny_edit) : ?>
                                <?= $this->Html->link(
                                    '<i class="fa fa-pencil"></i>',
                                    ['plugin' => 'Groups', 'controller' => 'Groups', 'action' => 'edit', $group->id],
                                    ['title' => __('Edit'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
                                ); ?>
                            <?php endif; ?>
                            <?php if (!$group->deny_delete) : ?>
                                <?= $this->Form->postLink(
                                    '<i class="fa fa-trash"></i>',
                                    ['plugin' => 'Groups', 'controller' => 'Groups', 'action' => 'delete', $group->id],
                                    [
                                        'confirm' => __('Are you sure you want to delete # {0}?', $group->id),
                                        'title' => __('Delete'),
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
