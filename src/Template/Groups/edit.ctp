<?php
echo $this->Html->css(
    [
        'AdminLTE./plugins/select2/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script('AdminLTE./plugins/select2/select2.full.min', ['block' => 'scriptBottom']);
echo $this->Html->scriptBlock(
    '$(".select2").select2({
        width: "100%",
        theme: "bootstrap",
        placeholder: " -- Please choose -- ",
        allowClear: true
    });',
    ['block' => 'scriptBottom']
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Edit {0}', ['Group']) ?></h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="box box-solid">
                <?= $this->Form->create($group) ?>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->Form->input('name'); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $this->Form->input('description'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-<?= !empty($remoteGroups) ? 6 : 12 ?>">
                            <div><?= $this->Form->label(__('Users')); ?></div>
                            <?= $this->Form->select('users._ids', $users, [
                                'class' => 'select2',
                                'multiple' => true
                            ]); ?>
                        </div>
                        <?php if (!empty($remoteGroups)) : ?>
                            <div class="col-xs-6">
                                <div><?= $this->Form->label(__('Remote Group')); ?></div>
                                <?= $this->Form->select('remote_group_id', $remoteGroups, [
                                    'empty' => true,
                                    'class' => 'select2'
                                ]); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="box-footer">
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</section>
