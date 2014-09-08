<?php
class NeotechRequest extends AppModel {
    public function get($terminal_id, $qid) {
        return $this->find('first', array(
            'conditions'=>array(
                'terminal_id'=>$terminal_id,
                'qid'=>$qid
            )
        ));
    }

    public function add($terminal_id, $qid, $body) {
        $data = compact('terminal_id', 'qid', 'body');
        $data['reqid'] = CakeSession::read('reqid');

        $this->create();
        $this->set($data);
        $this->save();
    }
}
