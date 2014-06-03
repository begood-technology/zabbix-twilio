#!/usr/bin/php
<?php
// **********************************************
// *** 外部ファイル読込 ***
// **********************************************
// Twilio PHP ライブラリ
require_once dirname ( __FILE__ ) . '/lib/twilio-php-latest/Services/Twilio.php';
// Logger クラス
require_once dirname ( __FILE__ ) . '/Logger.php';
// configファイル
require_once dirname ( __FILE__ ) . '/config.php';

// **********************************************
// *** 定数 ***
// **********************************************
define ( 'EXIT_OK', 0 );
define ( 'EXIT_INFO', 1 );
define ( 'EXIT_ERROR', -1 );

// **********************************************
// *** 設定値 ***
// **********************************************
$check_limit = 60; // ステータス確認最大数
$check_interval = 1; // ステータス確認間隔(秒)

// **********************************************
// *** メイン ***
// **********************************************
$log = new Logger ( $LOG_DIR . '/' . basename($argv[0], '.php') . '.log', $DEBUG_FLG );
$log->info ( 'Start' );

// 引数チェック
if ($argc != 4) {
	$log->error ( "引数が不正です。ARGC:$argc ARGV:" . var_export ( $argv, true ) );
	$log->info ( 'End' );
	exit ( EXIT_ERROR );
}

$log->debug ( "ARGC:$argc ARGV:" . var_export ( $argv, true ) );

// 電話番号確認
if (substr ( $argv [1], 0, 1 ) === '0') {
	$to_number = substr_replace ( $argv [1], '+81', 0, 1 ); // 先頭の0を+81に置き換え
} else if (substr ( $argv [1], 0, 1 ) === '+') {
	$to_number = $argv [1];
} else {
	$log->error ( "宛先の電話番号が不正です。To:$argv[1]" );
	$log->error ( 'End' );
	exit ( EXIT_ERROR );
}

// eventid抽出
if (preg_match ( "/eventid:(\d+)/i", $argv [3], $eventid ) !== 1) {
	$log->error ( "eventidが含まれていません。ARGC:$argc ARGV:" . var_export ( $argv, true ) );
	$log->error ( 'End' );
	exit ( EXIT_ERROR );
}

// message抽出
if (preg_match ( "/message:(.*)/is", $argv [3], $message ) !== 1) {
	$log->error ( "message:が含まれていません。ARGC:$argc ARGV:" . var_export ( $argv, true ) );
	exit ( EXIT_ERROR );
}

// TwiML URL
$URL = $SCRIPT_URL . '?cmd=notice&eventid=' . $eventid [1] . '&name=' . substr_replace ( $to_number, '0', 0, 3 ) . '&message=' . urlencode ( $message [1] );
$log->debug ( "URL:$URL" );

// Client生成
$client = new Services_Twilio ( $ACCOUNT_SID, $AUTH_TOKEN, $TWILIO_API_VERSION );

// Call
$call = $client->account->calls->create ( $TWILIO_NUMBER, $to_number, $URL, array (
		'Timeout' => $CALL_TIME
) );

$log->info ( "CallStart From:$TWILIO_NUMBER To:$to_number" );
$log->debug ( $call );

// Status確認
$check_count = 0;
$status_check = true;
while ( $status_check == true or $check_count > $check_limit ) {
	switch ($client->account->calls->get ( $call->sid )->status) {
		case "queued" : // 通話は発信待ち状態
		case "ringing" : // 呼び出し中
		case "in-progress" : // 相手が応答し、通話中
			$log->info ( "CallCheck From:$TWILIO_NUMBER To:$to_number Status:" . $client->account->calls->get ( $call->sid )->status );
			$check_count ++;
			sleep ( $check_interval );
			break;
		case "canceled" : // queued または ringing 中に、通話がキャンセルされた
		case "completed" : // 相手が応答し、通話が正常に終了
		case "busy" : // 相手からビジー信号を受信
		case "failed" : // 通話を接続できませんでした。通常は、ダイヤルした番号が存在しない
		case "no-answer" : // 相手が応答せず、通話が終了
			$status_check = false;
			$log->info ( "CallEnd   From:$TWILIO_NUMBER To:$to_number Status:" . $client->account->calls->get ( $call->sid )->status );
			break;
		default :
			$log->info ( "CallCheck From:$TWILIO_NUMBER To:$to_number Status:" . $client->account->calls->get ( $call->sid )->status );
			break;
	}
}

$log->info ( 'End' );
exit ( EXIT_OK );

?>
