<?php
// ******************************************************************
// *** Logger ***
// ******************************************************************
class Logger {
	public function __construct($logfile = null, $debugflg = false) {
		$this->_logfile = $logfile;
		$this->_debug = $debugflg;
	}
	public function trace() {
		$e = new Exception ();
		$trace = $e->getTrace ();

		return $trace [2];
	}
	public function log($level, $message) {
		$pid = getmypid ();

		if (is_array ( $message ) or is_object ( $message )) {
			$message = print_r( $message, true);
		}

		if ($level != 'DEBUG') {
			// Format:YYYY/MM/DD HH:MM:SS [<PID>] <Level> <Message>
			$log = strftime ( '%Y/%m/%d %T' ) . " [$pid] $level $message\n";
		} elseif ($this->_debug) {
			// Debug
			$trace = $this->trace ();
			// Format:YYYY/MM/DD HH:MM:SS [<PID>] <Level> <File>(Line) <Message>
			$log = strftime ( '%Y/%m/%d %T' ) . " [$pid] $level " . basename ( $trace ['file'] ) . '(' . $trace ['line'] . ')' . " $message\n";
		} else {
			return;
		}

		if (! error_log ( $log, 3, $this->_logfile )) {
			throw new Exception ( 'ログ出力に失敗しました。' );
		}
	}

	public function debug($message) {
		$level = 'DEBUG';
		$this->log ( $level, $message );
	}

	public function error($message) {
		$level = 'ERROR';
		$this->log ( $level, $message );
	}

	public function warn($message) {
		$level = 'WARN ';
		$this->log ( $level, $message );
	}

	public function info($message) {
		$level = 'INFO ';
		$this->log ( $level, $message );
	}
}
?>