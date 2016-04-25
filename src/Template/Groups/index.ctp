<div class="row">
    <div class="col-xs-12">
        <p class="text-right">
            <?= $this->Html->link(__('Add Group'), ['action' => 'add'], ['class' => 'btn btn-primary']); ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('id') ?></th>
                        <th><?= $this->Paginator->sort('name') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?= h($group->id) ?></td>
                        <td><?= h($group->name) ?></td>
                        <td class="actions">
                            <?= $this->Html->link('', ['action' => 'view', $group->id], ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-eye-open']) ?>
                            <?= $this->Html->link('', ['action' => 'edit', $group->id], ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']) ?>
                            <?= $this->Form->postLink('', ['action' => 'delete', $group->id], ['confirm' => __('Are you sure you want to delete # {0}?', $group->id), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->prev('< ' . __('previous')) ?>
        <?= $this->Paginator->numbers(['before' => '', 'after' => '']) ?>
        <?= $this->Paginator->next(__('next') . ' >') ?>
    </ul>
    <p><?= $this->Paginator->counter() ?></p>
</div>
