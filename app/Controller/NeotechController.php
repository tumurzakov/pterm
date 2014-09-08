<?php
App::uses('Xml', 'Utility');
App::uses('Application', 'Lib');

class NeotechController extends AppController {

    public $uses = array('NeotechRequest', 'NeotechResponse', 'Payment');

    protected $sid = "";
    protected $terminal_id = 0;

    public function index() {
    }

    public function data() {
        CakeSession::write('reqid', String::uuid());

        $requestXml = $this->request->input();
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
                $this->set('xml', $response['NeotechResponse']['body']);
                return $this->render('../Neotech/xml');
            } else {
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
            list($status, $msg) = $this->cancel($qid);
        } else {
            $BODY = array('@MSG' => "UNKNOWN OPERATION", '@STATUS' => 400);
            $this->out($opqid, $HEAD, $BODY);
        }

        $BODY = array(
            '@MSG' => $msg,
            '@STATUS' => $status,
        );

        $this->out($opqid, $HEAD, $BODY);
    }

    private function out($qid, $HEAD, $BODY) {
        $view = new XmlView($this, false);
        $view->set(compact('HEAD', 'BODY'));
        $view->set('_serialize', array('HEAD', 'BODY'));
        $view->set('_rootNode', 'XML');
        $xml = $view->render();
        $this->NeotechResponse->add($this->terminal_id, $qid, $xml);

        CakeSession::destroy();

        print $xml;
        exit;
    }

    protected function check($service_id, $account) {
        $app = new Application();
        try {
            $app->check($account);
        } catch (Exception $e) {
            return array(420, "ACCOUNT NOT FOUND");
        }
        return array(200, "SUCCESS");
    }

    protected function pay($qid, $service_id, $account, $amount, $date) {
        $payment = compact('qid', 'service_id', 'account', 'amount', 'date');
        $payment['reqid'] = CakeSession::read('reqid');
        $payment['terminal_id'] = $this->terminal_id;
        $this->Payment->add($payment);
        return array(250, "SUCCESS");
    }

    protected function cancel($qid) {
        try {
            $this->Payment->cancel($this->terminal_id, $qid);
        } catch(Exception $e) {
            return array(420, $e->getMessage());
        }
        return array(250, "SUCCESS");
    }
}
