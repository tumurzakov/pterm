<?php
App::uses('Application', 'Lib');
class CronController extends AppController {
    public $uses = array('Payment');
    public $layout = 'ajax';

    public function index() {
        $app = new Application();
        $payments = $this->Payment->get('not confirmed');
        foreach($payments as $payment) {
            try {
                $app->pay(
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

        $payments = $this->Payment->get('not conceled');
        foreach($payments as $payment) {
            try {
                $app->cancel($payment['Payment']['receipt']);
                $payment['Payment']['status'] = 'canceled';
            } catch(Exception $e) {
                $payment['Payment']['status'] = 'cancel failed';
            }
            $this->Payment->save($payment);
        }
    }
}
