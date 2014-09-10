<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

class PaymentLog {
    public static function log($msg) {
        CakeLog::write('payments', sprintf("[%s] %s", CakeSession::read('reqid'), $msg));
    }
}
