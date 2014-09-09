<?php
App::uses('PaymentLog', 'Lib');
class NeotechResponse extends AppModel {
    public function get($terminal_id, $qid) {
        $response =  $this->find('first', array(
            'conditions'=>array(
                'terminal_id'=>$terminal_id,
                'qid'=>$qid
            )
        ));

        if ($response) {
            PaymentLog::log("Response with terminal=$terminal_id and qid=$qid found!");
        }

        return $response;
    }

    public function add($terminal_id, $qid, $body) {
        PaymentLog::log("Adding response to storage");
        $data = compact('terminal_id', 'qid', 'body');
        $data['reqid'] = CakeSession::read('reqid');

        $this->create();
        $this->set($data);
        $this->save();
    }
}
