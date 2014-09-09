<?php
App::uses('AppController', 'Controller');

class PaymentsController extends AppController {

	public $components = array('Paginator', 'Session');

	public function index() {
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
        $payment = $this->Payment->read(array('id', 'status'), $id);
        if ($payment) {
            if ($payment['Payment']['status'] == 'confirmed') {
                $this->Payment->set('status', 'not canceled');
            }
            $this->Payment->save();
        }
        $this->redirect(array('action'=>'view', $id));
    }

	public function transfer($id = null) {
        $this->redirect(array('action'=>'view', $id));
    }

}
