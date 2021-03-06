<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

class NeotechShell extends AppShell {
    private $service_id = 1;

    public function main() {
        $this->out('Neotech protocol client');
        $this->hr();
        $this->out('cake neotech validate <url> <qid> <account>');
        $this->out('cake neotech pay <url> <qid> <account> <amount> <date>');
        $this->out('cake neotech cancel <url> <qid>');
        $this->out('cake neotech cron');
    }

    public function validate() {
        if (count($this->args) != 3) return;
        list($url, $qid, $account) = $this->args;
        $this->out("Validate [url=$url qid=$qid account=$account]");

        $request = 
            "<?xml version='1.0'?><XML><BODY  SERVICE_ID='{$this->service_id}' PARAM1='$account'/>".
            "<HEAD OP='QE11' SID='OSPP' QID='$qid'/></XML>";
        $this->request($url, $request);
    }

    public function cancel() {
        if (count($this->args) != 3) return;
        list($url, $qid, $cancel) = $this->args;
        $this->out("Cancel [url=$url qid=$qid cancel=$cancel]");

        $request = 
            "<?xml version='1.0'?><XML><BODY CANCEL='$cancel'/>".
            "<HEAD OP='PR09' SID='OSPP' QID='$qid'/></XML>";
        $this->request($url, $request);
    }

    public function pay() {
        if (count($this->args) != 5) return;
        list($url, $qid, $account, $amount, $date) = $this->args;
        $this->out("Pay [url=$url qid=$qid account=$account amount=$amount date=$date]");

        $request = 
            "<?xml version='1.0'?><XML><BODY SERVICE_ID='{$this->service_id}' SUM='$amount' PARAM1='$account'/>".
            "<HEAD OP='QE10' SID='OSPP' QID='$qid' DTS='$date'/></XML>";
        $this->request($url, $request);
    }

    public function cron() {
        $this->request('http://terminal.miglotto.kg/pterm/cron', '');
    }

    private function request($url, $request) {
        $this->hr();
        $this->out("Request: $request");

        $opts = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: text/xml',
                'content' => $request
            )
        );

        $context  = stream_context_create($opts);

        $response = file_get_contents($url, false, $context);

        $this->hr();
        $this->out("Response: $response");
    }

}
