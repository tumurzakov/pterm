<?Php
class TerminalException extends Exception {
    public $status;

    public function __construct($status, $message) {
        $this->status = $status;
        $this->message = $message;
    }
}
