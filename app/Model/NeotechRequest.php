<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

App::uses('PaymentLog', 'Lib');
class NeotechRequest extends AppModel {
    public function get($terminal_id, $qid) {
        $request = $this->find('first', array(
            'conditions'=>array(
                'terminal_id'=>$terminal_id,
                'qid'=>$qid
            )
        ));

        if ($request) {
            PaymentLog::log("Request with terminal=$terminal_id and qid=$qid found!");
        }
        
        return $request;
    }

    public function add($terminal_id, $qid, $body) {
        PaymentLog::log("Adding request to storage");
        $data = compact('terminal_id', 'qid', 'body');
        $data['reqid'] = CakeSession::read('reqid');

        $this->create();
        $this->set($data);
        $this->save();
    }
}
