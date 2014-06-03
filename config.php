<?php
// **********************************************
// *** SCRIPT ***
// **********************************************
// SCRIPTを設置したディレクトリ
$SCRIPT_DIR = '/usr/lib/zabbix/alertscripts/zabbix-twilio/';
// zabbix-twilio.phpスクリプトを設置したURL(TwilioからアクセスできるURL)
$SCRIPT_URL = 'http://<Server IP>/zabbix-twilio/zabbix-twilio.php';


// **********************************************
// *** ログ ***
// **********************************************
// ログファイル
$LOG_DIR= $SCRIPT_DIR . '/logs';
// Debugログ出力(true:あり, false:なし)
$DEBUG_FLG = false;


// **********************************************
// *** Twilio ***
// **********************************************
// Twilio REST API version
$TWILIO_API_VERSION = '2010-04-01';
// ACCOUNT SID
$ACCOUNT_SID = '<ACCOUNT_SID>';
// AUTH TOKEN
$AUTH_TOKEN = '<AUTH_TOKEN>';
// 発信番号
$TWILIO_NUMBER = '+81<TWILIO_NUMBER>';
// 呼出時間
$CALL_TIME = 12;


// **********************************************
// *** IVR ***
// **********************************************
// 自動通知用メッセージ(最初に掛けたときに流すメッセージ)
// $$MESAGE$$ を使用するとZabbixのアクションのメッセージで指定したvoice:の値と置き換えます。
// $$REGKEY$$ を使用するとZabbixへの登録確認用キ変数$REGKEYの値と置き換えます。
$MESSAGE_NOTICE = '監視センターからの自動通知です。$$MESSAGE$$ が発生しました。対応可能であれば,$$DIGITS$$,を押して下さい。対応が難しい場合は、このまま電話をお切り下さい';
// 確認キーが一致しない時のメッセージ
// $$REGKEY$$ を使用するとZabbixへの登録確認用キ変数$REGKEYの値と置き換えます。
$MESSAGE_RETYPE = '入力された番号が一致しないか認識できませんでした。再度入力して下さい。対応可能であれば,$$DIGITS$$,を押して下さい。対応が難しい場合は、このまま電話をお切り下さい';
// Zabbixへの登録がOKの場合のメッセージ
$MESSAGE_REG_OK = 'ザビックスへの登録が,完了しました。通話は終了します。';
// Zabbixへの登録がNGの場合のメッセージ
$MESSAGE_REG_NG = 'ザビックスへの登録が,失敗しました。通話は終了します。';
// 確認キー。1-9の数値で指定。一致するとZabbixへ登録する。桁数はREGKEYで指定可能。
$DIGITS = 1;
// 確認キーの桁数。
$DIGITS_NUM = 1;
// 確認キーの入力待ち時間
$DIGITS_TIMEOUT = 20;


// **********************************************
// *** ZABBIX ***
// **********************************************
// ZabbixサーバーのAPI URL
$ZABBIX_API = 'http://localhost/zabbix/api_jsonrpc.php';
// イベント登録時に使用するZabbixユーザーの名前
$ZABBIX_USER = '<username>';
// 上記ユーザーのパスワード
$ZABBIX_PASS = '<password>';
?>
