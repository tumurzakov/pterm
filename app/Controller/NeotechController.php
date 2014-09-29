<?php
App::uses('Xml', 'Utility');
App::uses('Application', 'Lib');
App::uses('PaymentLog', 'Lib');

class NeotechController extends AppController {

    public $uses = array('NeotechRequest', 'NeotechResponse', 'Payment', 'Event');

    protected $sid = "";
    protected $terminal_id = 0;

    public function beforeFilter() {
        $this->terminal = $this->Payment->Terminal->findById($this->terminal_id);
        $this->Auth->allow('data');
    }

    public function data() {
        $op = $qid = "";

        try {
            CakeSession::write('reqid', String::uuid());

            $this->check_terminal();

            $requestXml = $this->request->input();
            PaymentLog::log("request: $requestXml");

            $xml = Xml::build($requestXml);

            $op = @$xml->HEAD->attributes()->OP;
            $qid = @$xml->HEAD->attributes()->QID;
            $dts = @$xml->HEAD->attributes()->DTS;

            $request = $this->NeotechRequest->get($this->terminal_id, $qid);
            $response = $this->NeotechResponse->get($this->terminal_id, $qid);

            if (!empty($request)) {
                if (!empty($response)) {
                    PaymentLog::log("Answering with found response");
                    $this->Event->add($this->terminal_id, 0, 0, __('Terminal sent duplicated request'));
                    print $response['NeotechResponse']['body'];
                    exit;
                } else {
                    PaymentLog::log("Error: payment already in process");
                    $this->Event->add($this->terminal_id, 0, 0, __('Terminal sent request while previous was not processed yet'));
                    throw new TerminalException(400, 'TRY AGAIN');
                }
            }

            $this->NeotechRequest->add($this->terminal_id, $qid, $requestXml);

            list($status, $msg) =  array("", "");
            if ($op == 'QE11') {
                $service_id = @$xml->BODY->attributes()->SERVICE_ID;
                $account = @$xml->BODY->attributes()->PARAM1;
                list($status, $msg) = $this->check($service_id, $account);
            } else if ($op == 'QE10') {
                $service_id = @$xml->BODY->attributes()->SERVICE_ID;
                $account = @$xml->BODY->attributes()->PARAM1;
                $amount = @$xml->BODY->attributes()->SUM;
                list($status, $msg) = $this->pay($qid, $service_id, $account, $amount, $dts);
            } else if ($op == 'PR09') {
                $cancel = @$xml->BODY->attributes()->CANCEL;
                list($status, $msg) = $this->cancel($cancel);
            } else {
                PaymentLog::log("Error: operation not found");
                throw new TerminalException(400, 'UNKNOWN OPERATION');
            }

            $this->success($status, $msg, $op, $qid);

        } catch(TerminalException $e) {

            $this->error($e->status, $e->getMessage(), $op, $qid);

        } catch(Exception $e) {

            PaymentLog::log($e->getTraceAsString());
            $this->error(400, 'Internal Error [' . $e->getMessage() .']', $op, $qid);
        }
    }

    private function out($qid, $HEAD, $BODY, $error = false) {
        $xml = Xml::build(array('XML'=>array('HEAD'=>$HEAD, 'BODY'=>$BODY)));
        $responseXml = $xml->asXml();

        if (!$error) {
            $this->NeotechResponse->add($this->terminal_id, $qid, $responseXml);
        }

        PaymentLog::log("response: $responseXml");

        CakeSession::destroy();

        print $responseXml;
        exit;
    }

    protected function check($service_id, $account) {
        PaymentLog::log("Processing validation $service_id, $account");

        $this->check_service($service_id);

        $result = array(200, "SUCCESS");

        try {
            $app = new Application();
            $app->check($service_id, $account);
        } catch (Exception $e) {
            $this->Event->add($this->terminal_id, $service_id, $account, __('Validation SERVICE_ID=%s ACCOUNT=%s ACCOUNT NOT FOUND', $service_id, $account));
            throw new TerminalException(420, "ACCOUNT NOT FOUND");
        }

        $this->Event->add($this->terminal_id, $service_id, $account, __('Validation SERVICE_ID=%s ACCOUNT=%s SUCCESS', $service_id, $account));

        return $result;
    }

    protected function pay($qid, $service_id, $account, $amount, $provider_date) {
        PaymentLog::log("Processing payment $qid, $service_id, $account, $amount, $provider_date");

	$this->check($service_id, $account);

        $result = array(250, "SUCCESS");
        try {
            $payment = compact('service_id', 'account', 'amount', 'provider_date');
            $payment['receipt'] = $qid;
            $payment['reqid'] = CakeSession::read('reqid');
            $payment['terminal_id'] = $this->terminal_id;
            $payment['ip'] = $_SERVER['REMOTE_ADDR'];
            $this->Payment->add(array('Payment'=>$payment));
        } catch (Exception $e) {
            $this->Event->add($this->terminal_id, $service_id, $account, __('Payment SERVICE_ID=%s ACCOUNT=%s AMOUNT=%s ERROR', $service_id, $account, $amount));
            throw new TerminalException(420, $e->getMessage());
        }

        $this->Event->add($this->terminal_id, $service_id, $account, __('Payment SERVICE_ID=%s ACCOUNT=%s AMOUNT=%s SUCCESS', $service_id, $account, $amount));

        return $result;
    }

    protected function cancel($qid) {
        PaymentLog::log("Processing cancelation $qid");
        
        $payment = $this->Payment->getByQid($this->terminal_id, $qid);

        try {
            $app = new Application();
            $app->check_cancel(
                $payment['Payment']['service_id'], 
                $payment['Payment']['account'], 
                $payment['Payment']['amount']);
        } catch(Exception $e) {
            $this->Event->add($this->terminal_id, $service_id, $account, __('Cancel QID=%s ERROR', $qid));
            throw new TerminalException(420, $e->getMessage());
        }

        $result = array(250, "SUCCESS");
        $payment = $this->Payment->cancel($payment['Payment']['id']);
        $account = $payment['Payment']['account'];

        $this->Event->add($this->terminal_id, $service_id, $account, __('Cancel QID=%s SUCCESS', $qid));

        return $result;
    }

    private function check_terminal() {
        if (!empty($this->terminal['Terminal']['ip']) &&
            $_SERVER['REMOTE_ADDR'] != $this->terminal['Terminal']['ip']) {
            throw new TerminalException(420, 'ACCESS DENIED');
        }
    }

    private function check_service($service_id) {
        $service = $this->Payment->Service->findById($service_id);
        if (!$service['Service']['active']) {
            throw new TerminalException(420, 'SERVICE INACTIVE');
        }
    }

    private function success($status, $msg, $op, $qid) {
        $HEAD = array(
            '@OP' => $op,
            '@SID' => $this->sid,
            '@DTS' => strftime("%F %T", time()),
            '@QM' => ""
        );

        $BODY = array(
            '@MSG' => $msg,
            '@STATUS' => $status,
        );

        PaymentLog::log("Done msg=$msg status=$status");
        $this->out($qid, $HEAD, $BODY);
    }

    private function error($status, $msg, $op, $qid) {
        $HEAD = array(
            '@OP' => $op,
            '@SID' => $this->sid,
            '@DTS' => strftime("%F %T", time()),
            '@QM' => ""
        );

        $BODY = array(
            '@MSG' => $msg,
            '@STATUS' => $status,
        );

        PaymentLog::log("Error msg=$msg status=$status");
        $this->out($qid, $HEAD, $BODY);
    }
}
