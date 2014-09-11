<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

App::uses('AppController', 'Controller');
App::uses('Application', 'Lib');

class PaymentsController extends AppController {

    public $uses = array('Payment', 'Event');
	public $components = array('Paginator', 'Session');

	public function index() {
        $this->Paginator->settings = array('order'=>array('Payment.id'=>'desc'));
        if ($this->request->is('post')) {
            $filter = array();

            if (!empty($this->request->data['Payment']['terminal_id'])) {
                $filter['terminal_id'] = $this->request->data['Payment']['terminal_id'];
            }

            if (!empty($this->request->data['Payment']['service_id'])) {
                $filter['service_id'] = $this->request->data['Payment']['service_id'];
            }

            if (!empty($this->request->data['Payment']['account'])) {
                $filter['account'] = $this->request->data['Payment']['account'];
            }

            if (!empty($this->request->data['Payment']['status'])) {
                $filter['status'] = $this->request->data['Payment']['status'];
            }

            $from = $this->request->data['Payment']['from'];
            $from = strftime("%F", strtotime(implode('-', $from)));
            $to = $this->request->data['Payment']['to'];
            $to = strftime("%F", strtotime(implode('-', $to)));
            $filter['provider_date BETWEEN ? AND ?'] = array($from, $to);

            $this->paginate = array('conditions'=>$filter);
        }

		$this->Payment->recursive = 0;
		$this->set('payments', $this->Paginator->paginate());
        $this->set('terminals', $this->Payment->Terminal->find('list'));
        $this->set('services', $this->Payment->Service->find('list'));
	}

	public function view($id = null) {
		if (!$this->Payment->exists($id)) {
			throw new NotFoundException(__('Invalid payment'));
		}
		$options = array('conditions' => array('Payment.' . $this->Payment->primaryKey => $id));
		$this->set('payment', $this->Payment->find('first', $options));
	}

	public function cancel($id = null) {
        $payment = $this->Payment->findById($id);
        
        if ($payment) {
            if ($payment['Payment']['status'] == 'confirmed') {

                try {
                    $this->Payment->cancel($id);
                } catch(TerminalException $e) {
                    $this->setFlash(__($e->getMessage()));
                    $this->redirect(array('action'=>'view', $id));
                }

                $this->Event->add(
                    $payment['Payment']['terminal_id'], 
                    $payment['Payment']['service_id'], 
                    $payment['Payment']['account'], 
                    __('Manual cancelation'));
            }
        }
        $this->redirect(array('action'=>'view', $id));
    }

	public function transfer($id = null) {

        $payment = $this->Payment->findById($id);

        if ($this->request->is('post')) {

            $account = $this->data['Payment']['account'];

            try {
                $app = new Application();
                $app->check($account);
            } catch(Exception $e) {
                $this->setFlash(__($e->getMessage()));
                $this->redirect(array('action'=>'view', $id));
            }

            $payment = $this->Payment->findById($id);

            if ($payment) {
                if ($payment['Payment']['status'] == 'confirmed') {

                    try {
                        $this->Payment->transfer($id, $account);
                    } catch(TerminalException $e) {
                        $this->setFlash(__($e->getMessage()));
                        $this->redirect(array('action'=>'view', $id));
                    }

                    $this->Event->add(
                        $payment['Payment']['terminal_id'], 
                        $payment['Payment']['service_id'], 
                        $payment['Payment']['account'], 
                        __('Transfer to %s', $account));
                }
            }

        }

        $this->redirect(array('action'=>'view', $id));
    }

    public function summary() {

        $this->set('terminals', $terminals = $this->Payment->Terminal->find('list'));
        $this->set('services', $services = $this->Payment->Service->find('list'));


        $report = array(
            'total'=>array(
                'count'=>0,
                'amount'=>0,
                'services'=>array()
            ),
            'terminals'=>array()
        );

        foreach($terminals as $id=>$terminal) {
            $report['total'][$terminal] = array('count'=>0, 'amount'=>0);
            $report['terminals'][$terminal] = array('count'=>0, 'amount'=>0, 'days'=>array(), 'services'=>array());
            foreach($services as $id=>$service) {
                $report['total'][$terminal]['services'][$service] = array('count'=>0, 'amount'=>0);
                $report['terminals'][$terminal]['services'][$service] = array('count'=>0, 'amount'=>0, 'days'=>array());
            }
        }

        foreach($services as $id=>$service) {
            $report['total']['services'][$service] = array('count'=>0, 'amount'=>0);
        }


        if ($this->request->is('post')) {
            $filter = array();

            if (!empty($this->request->data['Payment']['terminal_id'])) {
                $filter['terminal_id'] = $this->request->data['Payment']['terminal_id'];
            }

            if (!empty($this->request->data['Payment']['service_id'])) {
                $filter['service_id'] = $this->request->data['Payment']['service_id'];
            }

            $from = $this->request->data['Payment']['from'];
            $from = strftime("%F", strtotime(implode('-', $from)));
            $to = $this->request->data['Payment']['to'];
            $to = strftime("%F", strtotime(implode('-', $to)));
            $filter['provider_date BETWEEN ? AND ?'] = array($from, $to);

            $filter['status'] = 'confirmed';

            $data = $this->Payment->find('all', array(
                'fields'=>array('Terminal.name', 'Service.name', 'DATE(provider_date) AS date', 'COUNT(*) as count', 'SUM(amount) as amount'),
                'group'=>array('terminal_id', 'service_id', 'DATE(provider_date)'),
                'order'=>array('terminal_id', 'service_id', 'DATE(provider_date)'),
                'conditions'=>$filter
            ));

            foreach($data as $row) {
                $report['total']['count'] += $row[0]['count'];
                $report['total']['amount'] += $row[0]['amount'];

                $report['total'][$row['Terminal']['name']]['count'] += $row[0]['count'];
                $report['total'][$row['Terminal']['name']]['amount'] += $row[0]['amount'];

                $report['total'][$row['Terminal']['name']]['services'][$row['Service']['name']]['count'] += $row[0]['count'];
                $report['total'][$row['Terminal']['name']]['services'][$row['Service']['name']]['amount'] += $row[0]['amount'];

                $report['total']['services'][$row['Service']['name']]['count'] += $row[0]['count'];
                $report['total']['services'][$row['Service']['name']]['amount'] += $row[0]['amount'];

                $report['terminals'][$row['Terminal']['name']]['count'] += $row[0]['count'];
                $report['terminals'][$row['Terminal']['name']]['amount'] += $row[0]['amount'];

                $report['terminals'][$row['Terminal']['name']]['services'][$row['Service']['name']]['count'] += $row[0]['amount'];
                $report['terminals'][$row['Terminal']['name']]['services'][$row['Service']['name']]['amount'] += $row[0]['amount'];

                if (!isset($report['terminals'][$row['Terminal']['name']]['days'][$row[0]['date']])) {
                    $report['terminals'][$row['Terminal']['name']]['days'][$row[0]['date']] = array('count'=>0, 'amount'=>0, 'services'=>array());
                }

                $report['terminals'][$row['Terminal']['name']]['days'][$row[0]['date']]['count'] += $row[0]['count'];
                $report['terminals'][$row['Terminal']['name']]['days'][$row[0]['date']]['amount'] += $row[0]['amount'];

                if (!isset($report['terminals'][$row['Terminal']['name']]['days'][$row[0]['date']]['services'][$row['Service']['name']])) {
                    $report['terminals'][$row['Terminal']['name']]['days'][$row[0]['date']]['services'][$row['Service']['name']] = array('count'=>0, 'amount'=>0);
                }

                $report['terminals'][$row['Terminal']['name']]['days'][$row[0]['date']]['services'][$row['Service']['name']]['count'] += $row[0]['count'];
                $report['terminals'][$row['Terminal']['name']]['days'][$row[0]['date']]['services'][$row['Service']['name']]['amount'] += $row[0]['count'];
            }

        }

        $this->set(compact('report'));
    }

}
