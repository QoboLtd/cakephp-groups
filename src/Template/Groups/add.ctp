<?php
$this->Form->templates([
    'checkboxWrapper' => '{{label}}',
    'nestingLabel' => '{{hidden}}<label class="checkbox-inline">{{input}}{{text}}</label>',
]);
?>
<section class="content-header">
    <h1><?= __('Create {0}', ['Group']) ?></h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
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
                        <div class="col-xs-12">
                            <div><?= $this->Form->label(__('Users')); ?></div>
                            <?php foreach ($users as $k => $v) : ?>
                                <?= $this->Form->select('users._ids', [$k => $v], [
                                    'multiple' => 'checkbox',
                                    'hiddenField' => false
                                ]); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                </div>
                <?= $this->Form->end() ?>
        </div>
    </div>
</section>