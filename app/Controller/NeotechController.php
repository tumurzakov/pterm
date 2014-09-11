<?php
App::uses('Xml', 'Utility');
App::uses('Application', 'Lib');
App::uses('PaymentLog', 'Lib');

class NeotechController extends AppController {

    public $uses = array('NeotechRequest', 'NeotechResponse', 'Payment', 'Event');

    protected $sid = "";
    protected $terminal_id = 0;

    public function beforeFilter() {
        $this->Auth->allow('data');
    }

    public function index() {
    }

    public function data() {
        try {
            CakeSession::write('reqid', String::uuid());

            if (Configure::read('debug')) {
                foreach($_SERVER as $var=>$val) {
                    PaymentLog::log("$var=$val");
                }

                foreach($_REQUEST as $var=>$val) {
                    PaymentLog::log("$var=$val");
                }
            }

            $requestXml = $this->request->input();
            PaymentLog::log("request: $requestXml");

            $xml = Xml::build($requestXml);

            $op = @$xml->HEAD->attributes()->OP;
            $qid = @$xml->HEAD->attributes()->QID;
            $dts = @$xml->HEAD->attributes()->DTS;
            $opqid = "{$this->terminal_id}-$op-$qid";

            $HEAD = array(
                '@OP' => $op,
                '@SID' => $this->sid,
                '@DTS' => strftime("%F %T", time()),
                '@QM' => ""
            );

            $request = $this->NeotechRequest->get($this->terminal_id, $opqid);
            $response = $this->NeotechResponse->get($this->terminal_id, $opqid);

            if (!empty($request)) {
                if (!empty($response)) {
                    PaymentLog::log("Answering with found response");
                    print $response['NeotechResponse']['body'];
                    exit;
                } else {
                    PaymentLog::log("Error: payment already in process");
                    $BODY = array('@MSG' => 'TRY AGAIN', '@STATUS' => 400);
                    $this->out($opqid, $HEAD, $BODY);
                }
            }

            $this->NeotechRequest->add($this->terminal_id, $opqid, $requestXml);

            list($status, $msg) =  array("", "");
            if ($op == 'QE11') {
                $service_id = @$xml->BODY->attributes()->SERVICE_ID;
                $account = @$xml->BODY->attributes()->PARAM1;
                list($status, $msg) = $this->check($service_id, $account);
            } else if ($op == 'QE10') {
                $service_id = @$xml->BODY->attributes()->SERVICE_ID;
                $account = @$xml->BODY->attributes()->PARAM1;
                $amount = @$xml->BODY->attributes()->SUM;

                list($status, $msg) = $this->pay($opqid, $service_id, $account, $amount, $dts);
            } else if ($op == 'PR09') {
                $cancel_qid = @$xml->BODY->attributes()->QID;
                list($status, $msg) = $this->cancel($cancel_qid);
            } else {
                PaymentLog::log("Error: operation not found");
                $BODY = array('@MSG' => "UNKNOWN OPERATION", '@STATUS' => 400);
                $this->out($opqid, $HEAD, $BODY);
            }

            $BODY = array(
                '@MSG' => $msg,
                '@STATUS' => $status,
            );

            PaymentLog::log("Done msg=$msg status=$status");
            $this->out($opqid, $HEAD, $BODY);
        } catch(Exception $e) {
            $HEAD = array(
                '@OP' => "",
                '@SID' => $this->sid,
                '@DTS' => strftime("%F %T", time()),
                '@QM' => ""
            );

            $BODY = array(
                '@MSG' => 'Internal Error [' . $e->getMessage() .']',
                '@STATUS' => 400,
            );

            $this->out("", $HEAD, $BODY, true);
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
        PaymentLog::log("Processing cancelation $service_id, $account");

        $result = array(200, "SUCCESS");

        $app = new Application();
        try {
            $app->check($account);
        } catch (Exception $e) {
            $result =  array(420, "ACCOUNT NOT FOUND");
        }

        $this->Event->add($this->terminal_id, $service_id, $account, __('Validation -> %s', $result[1]));

        return $result;
    }

    protected function pay($qid, $service_id, $account, $amount, $provider_date) {
        PaymentLog::log("Processing payment $qid, $service_id, $account, $amount, $provider_date");

        $result = array(250, "SUCCESS");

        $payment = compact('service_id', 'account', 'amount', 'provider_date');
        $payment['receipt'] = $qid;
        $payment['reqid'] = CakeSession::read('reqid');
        $payment['terminal_id'] = $this->terminal_id;
        $payment['ip'] = $_SERVER['REMOTE_ADDR'];
        $this->Payment->add($payment);

        $this->Event->add($this->terminal_id, $service_id, $account, __('Payment (amount=%.2f) -> %s', $amount, $result[1]));

        return $result;
    }

    protected function cancel($qid) {
        PaymentLog::log("Processing cancelation $qid");

        $result = array(250, "SUCCESS");
        $account  = "";

        try {
            $payment = $this->Payment->cancel($this->terminal_id, $qid);
            $account = $payment['Payment']['account'];
        } catch(Exception $e) {
            $result = array(420, $e->getMessage());
        }

        $this->Event->add($this->terminal_id, $service_id, $account, __('Cancelation -> %s', $result[1]));

        return $result;
    }
}
