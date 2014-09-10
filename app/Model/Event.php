<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

class Event extends AppModel {
    public $belongsTo = array('Terminal', 'Service');
    public function add($terminal_id, $service_id, $account, $description) {
        $this->create();
        $this->set(compact('terminal_id', 'service_id', 'account', 'description'));
        $this->save();
    }
}
