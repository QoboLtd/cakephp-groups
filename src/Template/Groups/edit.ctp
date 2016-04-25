<div class="row">
    <div class="col-xs-12">
        <?= $this->Form->create($group) ?>
        <fieldset>
            <legend><?= __('Edit Group') ?></legend>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">&nbsp;</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <?= $this->Form->input('name'); ?>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <?= $this->Form->input('users._ids', ['options' => $users]); ?>
                            </div>
                        </div>
                    </div>
                </div>
        </fieldset>
        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
        <?= $this->Form->end() ?>
    </div>
</div>
