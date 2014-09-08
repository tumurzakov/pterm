<?php
class Payment extends AppModel {
    public function add($payment) {
        $payment['status'] = 'not confirmed';

        $this->create();
        $this->set($payment);
        $this->save();
    }

    public function cancel($terminal_id, $qid) {
        $payment = $this->find('first', array(
            'conditions'=>array(
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
