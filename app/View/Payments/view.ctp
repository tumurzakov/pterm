<style>
.transfer {
    padding: 10px;
}

#PaymentAccount {
    height: 2.75rem;
}
</style>

<div class="payments view">
<h2><?php echo __('Payment'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Terminal'); ?></dt>
		<dd>
			<?php echo $this->Html->link($payment['Terminal']['name'], array('controller' => 'terminals', 'action' => 'view', $payment['Terminal']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Service'); ?></dt>
		<dd>
			<?php echo $this->Html->link($payment['Service']['name'], array('controller' => 'services', 'action' => 'view', $payment['Service']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Receipt'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['receipt']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Account'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['account']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Amount'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['amount']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Provider Date'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['provider_date']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Ip'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['ip']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Reqid'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['reqid']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($payment['Payment']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>

    <?php if($payment['Terminal']['cancel_allowed']): ?>
    <div class='row'>
        <?php if ($payment['Payment']['status'] == 'confirmed'):?>
        <div class='large-3 columns'>
            <?php echo $this->Form->postLink(__('Cancel Payment'), 
                array('action' => 'cancel', $payment['Payment']['id']), 
                array('class'=>'small alert button'), 
                __('Are you sure you want to cancel # %s?', $payment['Payment']['id'])); ?> 
        </div>

        <div class='transfer large-3 columns'>
            <?php echo $this->Form->create(array('url'=>array('action'=>'transfer', $payment['Payment']['id']))); ?>
            <div class='row collapse'>
                <?php echo $this->Form->input('account', 
                    array('div'=>array('class'=>'large-8 columns'), 
                    'label'=>'', 'placeholder'=>__('New account'))); ?>

                    <?php echo $this->Form->end(
                    array('label'=>__('Transfer'), 'class'=>'small expand button', 
                    'div'=>array('class'=>'large-4 columns')), 'hi');?>
            </div>
        </div>

        <div class='large-6 columns'>
        </div>

        <?php endif; ?>

    </div>
    <?php endif; ?>

	<ul>
		<li><?php echo $this->Html->link(__('List Payments'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Terminals'), array('controller' => 'terminals', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('List Services'), array('controller' => 'services', 'action' => 'index')); ?> </li>
	</ul>
</div>
