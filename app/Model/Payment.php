<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

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

        $this->cancelById($payment['Payment']['id']);
    }

    public function cancelById($id) {
        $this->id = $id;
        $this->setField('status', 'not canceled');
    }

    public function transfer($id, $account) {
        $payment = $this->read(null, $id);
        $this->setField('status', 'not canceled');

        unset($payment['Payment']['id']);
        $payment['Payment']['account'] = $account;
        $payment['Payment']['status'] = 'not confirmed';

        $this->create();
        $this->set($payment['Payment']);
        $this->save();
    }
}
