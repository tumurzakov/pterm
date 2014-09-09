<?php
class PaymentLog {
    public static function log($msg) {
        CakeLog::write('payments', sprintf("[%s] %s", CakeSession::read('reqid'), $msg));
    }
}
