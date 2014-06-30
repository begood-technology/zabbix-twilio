<?php
// **********************************************
// *** 外部ファイル ***
// **********************************************
// configファイル
require_once '/usr/lib/zabbix/alertscripts/zabbix-twilio/config.php';
// Twilio PHP ライブラリ
require_once $SCRIPT_DIR . '/lib/twilio-php-latest/Services/Twilio.php';
// Logger クラス
require_once $SCRIPT_DIR . '/Logger.php';


// **********************************************
// *** メイン ***
// **********************************************
$log = new Logger ( $LOG_DIR . '/' . basename($_SERVER['SCRIPT_FILENAME'], '.php') . '.log', $DEBUG_FLG );
$log->info ( 'Start' );

$log->debug ( $_REQUEST );

// コマンド指定確認
if (! isset ( $_REQUEST ['cmd'] )) {
	$log->error ( '不正なリクエストです。' );
	$log->error ( $_REQUEST );
	exit ();
}

Switch ($_REQUEST ['cmd']) {
	case "register" : // Zabbixへ登録
		if (! isset ( $_REQUEST ['Digits'] )) {
			$log->info ( "Digits 未入力" );
			exit ();
		}

		// 入力キー確認
		Switch ($_REQUEST ['Digits']) {
			case $DIGITS :
				$log->info ( 'Zabbixへ登録処理開始' );

				$log->debug ( "ZABBIX_API:$ZABBIX_API ZABBIX_USER:$ZABBIX_USER ZABBIX_PASS:$ZABBIX_PASS" );
				$log->debug ( "MESSAGE_REG_OK:$MESSAGE_REG_OK" );
				$log->debug ( "MESSAGE_REG_NG:$MESSAGE_REG_NG" );

				$response = new Services_Twilio_Twiml ();

				try {
					$api = new Zabbix_API ( $ZABBIX_API, $ZABBIX_USER, $ZABBIX_PASS );
					$api_res = $api->request('event.acknowledge', array (
							'eventids' => $_REQUEST ['eventid'],
							'message' => $_REQUEST ['name'] . ' が受電確認済み。'
					) );

					// 登録成功
					$response->say ( $MESSAGE_REG_OK, array (
							'voice' => 'woman',
							'language' => 'ja-JP'
					) );
					header ( 'Content-type: text/xml' );
					print $response;
					$log->info ( 'Zabbixへの登録成功' );
					$log->debug ( $response );
				} catch ( Exception $e ) {
					// 登録失敗
					$response->say ( $MESSAGE_REG_NG, array (
							'voice' => 'woman',
							'language' => 'ja-JP'
					) );
					header ( 'Content-type: text/xml' );
					print $response;
					$log->debug ( $response );

					$log->error ( 'Zabbixへの登録失敗 ' );
					$log->error ( $e->getMessage () );

					return $e->getMessage ();
				}

				break;
			case 0 :
			case 1 < $_REQUEST ['Digits'] and $_REQUEST ['Digits'] < 10 :
				$log->info ( "Digits 不一致 Digits:$_REQUEST ['Digits]" );
				$log->info ( "再入力処理開始" );

				$log->debug ( "MESSAGE_RETYPE:$MESSAGE_RETYPE" );

				$response = new Services_Twilio_Twiml ();
				$gather = $response->gather ( array (
						'action' => $SCRIPT_URL . '?cmd=register&eventid=' . $_REQUEST ['eventid'] . '&name=' . $_REQUEST ['name'],
						'timeout' => $DIGITS_TIMEOUT,
						'finishOnKey' => '#',
						'numDigits' => $DIGITS_NUM
				) );

				// $$DIGITS$$を置き換え
				$message = str_replace ( '$$DIGITS$$', $DIGITS, $MESSAGE_RETYPE );
				$gather->say ( $message, array (
						'voice' => 'woman',
						'language' => 'ja-JP'
				) );

				header ( 'Content-type: text/xml' );
				print $response;
				$log->debug ( $response );

				break;
		}
		break;
	case "notice" : // アラート通知
		$log->info ( "自動通知処理開始" );

		// URLデコード
		$message = urldecode ( $_REQUEST ['message'] );
		// $$MESSAGE$$
		$message = str_replace ( '$$MESSAGE$$', $message, $MESSAGE_NOTICE );
		// $$DIGITS$$を置き換え
		$message = str_replace ( '$$DIGITS$$', $DIGITS, $message );

		$log->debug ( "MESSAGE:$message" );
		$log->debug ( "DIGITS_TIMEOUT:$DIGITS_TIMEOUT DIGITS_NUM:$DIGITS_NUM URL:$SCRIPT_URL" );

		$response = new Services_Twilio_Twiml ();
		$gather = $response->gather ( array (
				'action' => $SCRIPT_URL . '?cmd=register&eventid=' . $_REQUEST ['eventid'] . '&name=' . $_REQUEST ['name'],
				'timeout' => $DIGITS_TIMEOUT,
				'finishOnKey' => '#',
				'numDigits' => $DIGITS_NUM
		) );

		$gather->say ( $message, array (
				'voice' => 'woman',
				'language' => 'ja-JP'
		) );

		header ( 'Content-type: text/xml' );
		print $response;

		$log->debug ( $response );
		break;
	default :
}

exit ();


// ******************************************************************
// *** Zabbix_API ***
// ******************************************************************
class Zabbix_API  {
	private $api_url = '';
	private $auth = '';

	public function __construct($api_url, $user='', $password='') {
		$this->api_url = $api_url;

		if (!empty($user) and !empty($password)) {
			$this->userLogin(array('user' => $user, 'password' => $password));
		}
	}

	public function userLogin($params) {
		$res = $this->request('user.login', $params);
		$this->auth = $res['result'];
	}

	public function request($method, $params) {
		// APIをJSONエンコード
		$content = json_encode( array (
				'jsonrpc' => '2.0',
				'method'  => $method,
				'params'  => $params,
				'auth'    => $this->auth,
				'id'      => '1'
		));

		// リクエストオプション
		$context = stream_context_create(array('http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/json-rpc; charset=UTF-8' . "\r\n",
				'content' => $content,
				'ignore_errors' => true
		)));

		// リクエスト
		$res = @file_get_contents ( $this->api_url, false, $context);
		if (!$res) {
			throw new Exception('"' . $this->api_url . '"' . 'からデータを取得できません。');
		}

		// JSONデコード
		$api_res = json_decode($res, true);

		if (!$api_res) {
			$msg = print_r($res, true);
			throw new Exception('レスポンスデータがJSON形式ではありません。' . "\n\n" . $msg);
		}

		// APIの返り値チェック
		if (array_key_exists('error', $api_res)) {
			$msg = print_r($api_res, true);
			throw new Exception('API エラーが発生しました。' . "\n\n" . $msg);
		}
		return $api_res;
	}
}
?>