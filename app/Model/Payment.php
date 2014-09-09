<?php
class Payment extends AppModel {

    public $belongsTo = array('Terminal', 'Service');

    public function get($status) {
        return $this->find('all', array(
            'conditions'=>array(
                'status'=>$status
            )
        ));
    }

    public function add($payment) {
        $payment['status'] = 'not confirmed';

        $this->create();
        $this->set($payment);
        $this->save();
    }

    public function cancel($terminal_id, $qid) {
        $payment = $this->find('first', array(
            'conditions'=>array(
                'status'=>'confirmed',
                'terminal_id'=>$terminal_id,
                'qid'=>$qid
            )
        ));

        if (empty($payment)) {
            throw new Exception("Payment not found");
        }

        $payment['status'] = 'not canceled';
        $this->save($payment);
    }
}
