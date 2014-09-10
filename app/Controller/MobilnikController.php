<?php
/*
 * @author     Umurzakov Temir <temir@umurzakov.com>
 * @link       https://github.com/tumurzakov/pterm.git
 *
*/

App::uses('Xml', 'Utility');
App::uses('Application', 'Lib');
App::uses('PaymentLog', 'Lib');
App::uses('NeotechController', 'Controller');

class MobilnikController extends NeotechController {

    protected $terminal_id = 1;

}
