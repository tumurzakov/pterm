<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

App::uses('Application', 'Lib');
class CronController extends AppController {
    public $uses = array('Payment');
    public $layout = 'ajax';

    public function beforeFilter() {
        $this->Auth->allow();
    }

    public function index() {
        $payments = $this->Payment->get('not confirmed');
        foreach($payments as $payment) {
            try {
                $app = new Application();
                $app->pay(
                    $payment['Payment']['service_id'],
                    $payment['Payment']['receipt'],
                    $payment['Payment']['account'],
                    $payment['Payment']['amount']
                );

                $payment['Payment']['status'] = 'confirmed';
            } catch(Exception $e) {
                $payment['Payment']['status'] = 'confirm failed';
            }
            $this->Payment->save($payment);
        }

        $payments = $this->Payment->get('not canceled');
        foreach($payments as $payment) {
            try {
                $app = new Application();
                $app->cancel(
                    $payment['Payment']['service_id'],
                    $payment['Payment']['receipt']);
                $payment['Payment']['status'] = 'canceled';
            } catch(Exception $e) {
                $payment['Payment']['status'] = 'cancel failed';
            }
            $this->Payment->save($payment);
        }
    }
}
