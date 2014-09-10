<style>
form {
    margin-top: 10px;
}

.header th {
    background: #fff;
}

.terminal td, .terminal th {
    background: #ddd;
}

.total td, .total th {
    background: #ddd;
}
</style>

<div>
    <?php
        $from = strftime("%Y-%m-01", time());
        $to = strftime("%F", strtotime("+1 month", strtotime($from)));

        echo $this->Form->create(array('action'=>'summary'));
        echo $this->Form->input('terminal_id', array('empty' => array(0 => ''), 'div'=>array('class'=>'large-2 columns')));
        echo $this->Form->input('service_id', array('empty' => array(0 => ''), 'div'=>array('class'=>'large-2 columns')));
    ?>

    <div class='large-3 columns'>
        <?php echo $this->Form->input('from', array('type'=>'date', 'dateFormat'=>'DMY', 'default'=>$from)); ?>
    </div>

    <div class='large-3 columns'>
        <?php echo $this->Form->input('to', array('type'=>'date', 'dateFormat'=>'DMY', 'default'=>$to)); ?>
    </div>

    <?php
        echo $this->Form->end(array('label'=>'Filter', 'class'=>'right button', 
            'div'=>array('class'=>'large-2 columns')));
    ?>
</div>

<table>
<?php if ($report): ?>
    <tr class='header'>
        <th rowspan='2'></th>
        <th rowspan='2'><?php echo __('Count'); ?></th>
        <th rowspan='2'><?php echo __('Amount'); ?></th>

        <?php foreach($report['total']['services'] as $service=>$data): ?>
        <th colspan='2'><?php echo $service;?></th>
        <?php endforeach; ?>
    </tr>
    <tr class='header'>
        <?php foreach($report['total']['services'] as $service=>$data): ?>
        <th><?php echo __('Count'); ?></th>
        <th><?php echo __('Amount'); ?></th>
        <?php endforeach; ?>
    </tr>

    <?php foreach($report['terminals'] as $terminal=>$data):?>
    <tr class='terminal'>
        <th><?php echo $terminal; ?></th>
        <th><?php echo $report['total'][$terminal]['count'];?></th>
        <th><?php echo $report['total'][$terminal]['amount'];?></th>

        <?php foreach($report['total'][$terminal]['services'] as $service): ?>
        <th><?php echo $service['count'];?></th>
        <th><?php echo $service['amount'];?></th>
        <?php endforeach;?>
    <tr>

    <?php foreach($data['days'] as $day=>$dayData):?>
    <tr>
        <th><?php echo $day; ?></th>
        <td><?php echo $dayData['count']; ?></td>
        <td><?php echo $dayData['amount']; ?></td>

        <?php foreach($dayData['services'] as $serviceData):?>
        <td><?php echo $serviceData['count']; ?></td>
        <td><?php echo $serviceData['amount']; ?></td>
        <?php endforeach; ?>

    </tr>
    <?php endforeach; ?>

    <?php endforeach; ?>

    <tr class='total'>
        <th><?php echo __('Total');?></th>
        <th><?php echo $report['total']['count'];?></th>
        <th><?php echo $report['total']['amount'];?></th>

        <?php foreach($report['total']['services'] as $service): ?>
        <th><?php echo $service['count'];?></th>
        <th><?php echo $service['amount'];?></th>
        <?php endforeach;?>
    <tr>

<?php endif; ?>
</table>
