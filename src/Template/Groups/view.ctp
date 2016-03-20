<div class="row">
    <div class="col-xs-12">
        <h3><strong><?= $this->Html->link(__('Groups'), ['action' => 'index']) . ' &raquo; ' . h($group->name) ?></strong></h3>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">&nbsp;</h3>
            </div>
            <table class="table table-hover">
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($group->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($group->name) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <h3><?= __('Associated Records'); ?></h3>
        <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#users" aria-controls="users" role="tab" data-toggle="tab">
                    <?= __('Users'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="groups_users">
                <?php if (!empty($group->users)): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?= $this->Paginator->sort(__('Id')) ?></th>
                            <th><?= $this->Paginator->sort(__('Username')) ?></th>
                            <th><?= $this->Paginator->sort(__('Email')) ?></th>
                            <th><?= $this->Paginator->sort(__('First Name')) ?></th>
                            <th><?= $this->Paginator->sort(__('Last Name')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group->users as $users): ?>
                        <tr>
                            <td><?= h($users->id) ?></td>
                            <td><?= h($users->username) ?></td>
                            <td><?= h($users->email) ?></td>
                            <td><?= h($users->first_name) ?></td>
                            <td><?= h($users->last_name) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
