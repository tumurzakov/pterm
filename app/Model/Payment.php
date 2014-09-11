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

    public function getByQid($terminal_id, $opqid) {
        $payment = $this->find('first', array(
            'conditions'=>array(
                'status'=>'confirmed',
                'terminal_id'=>$terminal_id,
                'receipt'=>$opqid
            )
        ));

        if (empty($payment)) {
            throw new TerminalException(420, "PAYMENT NOT FOUND");
        }

        return $payment;
    }

    public function add($payment) {
        $this->check_activity($payment);

        $payment['Payment']['status'] = 'not confirmed';

        $this->create();
        $this->set($payment);
        $this->save();
    }

    public function cancel($id) {
        $payment = $this->read(null, $id);

        $this->check_activity($payment);
        if (!$payment['Terminal']['cancel_allowed']) {
            throw new Exception('CANCEL DENIED');
        }

        $this->saveField('status', 'not canceled');
    }

    public function transfer($id, $account) {
        $payment = $this->read(null, $id);

        $this->check_activity($payment);
        if (!$payment['Terminal']['cancel_allowed']) {
            throw new Exception('CANCEL DENIED');
        }

        $this->saveField('status', 'not canceled');

        unset($payment['Payment']['id']);
        $payment['Payment']['account'] = $account;
        $payment['Payment']['status'] = 'not confirmed';

        $this->create();
        $this->set($payment['Payment']);
        $this->save();
    }

    private function check_activity($payment) {
        $terminal = $payment;
        if (!isset($terminal['Terminal'])) {
            $terminal = $this->Terminal->findById($payment['Payment']['terminal_id']);
        }

        $service = $payment;
        if (!isset($service['Service'])) {
            $service = $this->Service->findById($payment['Payment']['service_id']);
        }

        if (!$terminal['Terminal']['active']) {
            throw new Exception('TERMINAL INACTIVE');
        }

        if (!$service['Service']['active']) {
            throw new Exception('SERVICE INACTIVE');
        }
    }
}
