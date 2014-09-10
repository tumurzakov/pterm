<style>
.filter {
    padding: 10px;
}
</style>

<div class='large-9 small-12 columns'>

    <div class="payments index">
        <h2><?php echo __('Payments'); ?></h2>
        <table cellpadding="0" cellspacing="0">
        <thead>
        <tr>
                <th><?php echo $this->Paginator->sort('id'); ?></th>
                <th><?php echo $this->Paginator->sort('terminal_id'); ?></th>
                <th><?php echo $this->Paginator->sort('service_id'); ?></th>
                <th><?php echo $this->Paginator->sort('receipt'); ?></th>
                <th><?php echo $this->Paginator->sort('account'); ?></th>
                <th><?php echo $this->Paginator->sort('amount'); ?></th>
                <th><?php echo $this->Paginator->sort('provider_date'); ?></th>
                <th><?php echo $this->Paginator->sort('status'); ?></th>
                <th class="actions"><?php echo __('Actions'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $payment): ?>
        <tr>
            <td><?php echo h($payment['Payment']['id']); ?>&nbsp;</td>
            <td>
                <?php echo $this->Html->link($payment['Terminal']['name'], array('controller' => 'terminals', 'action' => 'view', $payment['Terminal']['id'])); ?>
            </td>
            <td>
                <?php echo $this->Html->link($payment['Service']['name'], array('controller' => 'services', 'action' => 'view', $payment['Service']['id'])); ?>
            </td>
            <td><?php echo h($payment['Payment']['receipt']); ?>&nbsp;</td>
            <td><?php echo h($payment['Payment']['account']); ?>&nbsp;</td>
            <td><?php echo h($payment['Payment']['amount']); ?>&nbsp;</td>
            <td><?php echo h($payment['Payment']['provider_date']); ?>&nbsp;</td>
            <td><?php echo h($payment['Payment']['status']); ?>&nbsp;</td>

            <td class="actions">
                <?php echo $this->Html->link(__('View'), array('action' => 'view', $payment['Payment']['id'])); ?>
            </td>
        </tr>
    <?php endforeach; ?>
        </tbody>
        </table>
        <p>
        <?php
        echo $this->Paginator->counter(array(
        'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
        ));
        ?>	</p>
        <div class="paging">
        <?php
            echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
            echo $this->Paginator->numbers(array('separator' => ''));
            echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
        ?>
        </div>
    </div>
    
    <div class="actions">
        <h3><?php echo __('Actions'); ?></h3>
        <ul>
            <li><?php echo $this->Html->link(__('List Terminals'), array('controller' => 'terminals', 'action' => 'index')); ?> </li>
            <li><?php echo $this->Html->link(__('List Services'), array('controller' => 'services', 'action' => 'index')); ?> </li>
        </ul>
    </div>

</div>

<div class='filter large-3 small-12 columns'>
    <?php 
        $from = strftime("%Y-%m-01", time());
        $to = strftime("%F", strtotime("+1 month", strtotime($from)));

        echo $this->Form->create(array('action'=>'index')); 
        echo $this->Form->input('terminal_id', array('empty' => array(0 => '')));
        echo $this->Form->input('service_id', array('empty' => array(0 => '')));
        echo $this->Form->input('account');
        echo $this->Form->input('from', array('type'=>'date', 'dateFormat'=>'DMY', 'default'=>$from));
        echo $this->Form->input('to', array('type'=>'date', 'dateFormat'=>'DMY', 'default'=>$to));
        echo $this->Form->input('status', array(
            'options'=>array('confirmed', 'not confirmed', 'canceled', 'not canceld', 'failed')
        ));
        echo $this->Form->end(array('label'=>'Find', 'class'=>'small button')); 
    ?>
</div>

