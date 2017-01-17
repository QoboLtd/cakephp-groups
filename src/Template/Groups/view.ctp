<section class="content-header">
    <h1><?= $this->Html->link(
        __('Groups'),
        ['plugin' => 'Groups', 'controller' => 'Groups', 'action' => 'index']
    ) . ' &raquo; ' . h($group->name) ?></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-users"></i>

                    <h3 class="box-title">Details</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt><?= __('Name') ?></dt>
                        <dd><?= h($group->name) ?></dd>
                        <dt><?= __('Description') ?></dt>
                        <dd><?= h($group->description) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#users" aria-controls="users" role="tab" data-toggle="tab">
                            <?= __('Users'); ?>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="users">
                        <?php if (!empty($group->users)) : ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-condensed table-vertical-align">
                                <thead>
                                    <tr>
                                        <th><?= __('Username') ?></th>
                                        <th><?= __('Email') ?></th>
                                        <th><?= __('First Name') ?></th>
                                        <th><?= __('Last Name') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group->users as $users) : ?>
                                    <tr>
                                        <td><?= $this->Html->link($users->username, [
                                            'plugin' => 'CakeDC/Users',
                                            'controller' => 'Users',
                                            'action' => 'view',
                                            $users->id
                                        ]) ?></td>
                                        <td><?= h($users->email) ?></td>
                                        <td><?= h($users->first_name) ?></td>
                                        <td><?= h($users->last_name) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>