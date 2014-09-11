<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

App::uses('ClassRegistry', 'Utility');
App::uses('Luhn', 'Lib');

class Application {
    public function __construct() {
        $this->MiglottoUser = ClassRegistry::init('MiglottoUser');
        $this->MiglottoPayment = ClassRegistry::init('MiglottoPayment');
    }

    public function check($service_id, $account) {
        $user = $this->MiglottoUser->findById(Luhn::decode($account));
        if (empty($user)) {
            throw new Exception('Account not found');
        }
        return $user;
    }

    public function pay($service_id, $receipt, $account, $amount) {
        $user_id = Luhn::decode($account);
        $old_user = $this->check($service_id, $account);
        $amount = round($amount, 2);

        $this->MiglottoUser->updateAll(
            array('MiglottoUser.money'=>sprintf('MiglottoUser.money + %.2f', $amount)),
            array('MiglottoUser.id'=>$user_id)
        );
        $new_user = $this->MiglottoUser->findById($user_id);

        $this->MiglottoPayment->create();
        $this->MiglottoPayment->set(array(
            'operation'=>'+',
            'amount'=>$amount,
            'user_id'=>$user_id,
            'event_text'=>__('Account was credited on %.2f', $amount),
            'old_balance'=>$old_user['MiglottoUser']['money'],
            'new_balance'=>$new_user['MiglottoUser']['money'],
            'created_at'=>strftime('%F %T', time())
        ));
        $this->MiglottoPayment->save();
    }

    public function cancel($service_id, $receipt) {
        throw new Exception('Cancel denied');
    }

    public function check_cancel($service_id, $account, $amount) {
        throw new Exception('Cancel denied');
    }
}
