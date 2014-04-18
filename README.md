zabbix-twilioとは
============

zabbixから[Twilio](http://twilio.kddi-web.com/)を使用して電話による自動通知を行い、 イベントの 障害対応コメントへ記録を残すことができます。

|ファイル名       |役割                                                                                     |
|-----------------|-----------------------------------------------------------------------------------------|
|twilio-call.php  |Twilioを使用して電話をかけるPHPスクリプト                                                |
|zabbix-twilio.php|TwilioへTwiMLを返し、キー入力に応じZabbixへイベントの障害対応コメントを登録するスクリプト|
|config.php       |スクリプトで使用する設定ファイル                                                         |
|Logger.php       |スクリプトが使用するログイングスクリプト                                                 |

動作環境
============

zabbix-twilioを使用するには以下のソフトウェアが必要です。

|ソフトウェア名          |バージョン       |
|------------------------|-----------------|
|Zabbix                  |2.0 以上         |
|PHPが動作するWebサーバー|PHPは5.3以上     |

ダウンロード
============

[こちら](https://github.com/begood-technology/zabbix-twilio/releases)からダウンロードできます。

インストール
============

## 1.スクリプトのインストール

-------------------------------------------

Zabbixのアラートスクリプトディレクトリ内へzabbix-twilioディレクトリを設置します。

例：Zabbixのアラートスクリプトディレクトリが(/usr/lib/zabbix/alertscripts/)だった場合

    /usr/lib/zabbix/alertscripts/zabbix-twilio

twilio-call.phpへ実行権限を付加します。また、logsディレクトリのパーミッションを変更。

    chmod +x /usr/lib/zabbix/alertscripts/zabbix-twilio/twilio-call.php
    chmod 777 /usr/lib/zabbix/alertscripts/zabbix-twilio/logs

設置したzabbix-twilioディレクトリ内にある`zabbix-twilio.php`スクリプトをPHPが動作するWEBサーバーのドキュメントルート(/var/www/htmlなど)へ移動させます。

例：WEBサーバーのドキュメントルートが/var/www/html/で/var/www/htmlへ設置する場合

    mkdir /var/www/html/zabbi-twilio
    mv /usr/lib/zabbix/alertscripts/zabbix-twilio/zabbix-twilio.php /var/www/html/zabbix-twilio/
　
　

## 2.twilio-phpライブラリのインストール

-------------------------------------------

Twilioの[こちら](http://www.twilio.com/docs/libraries)のページ内にあるtwilio-phpライブラリをダウンロードし、設置します。

    # wget https://github.com/twilio/twilio-php/archive/latest.zip
    # unzip latest.zip
    # mv twilio-php-latest/ /usr/lib/zabbix/zabbix-twilio/lib/
　
　
## 3.スクリプトと設定ファイルの修正

-------------------------------------------

下記ファイル(zabbix-twilio.phpとconfig.php)の設定値を適応修正する必要があります。

ファイル名:**zabbix-twilio.php**

    // configファイル
    require_once '<config.phpのフルパス>';
　
　

ファイル名:**config.php**

|変数名         |設定内容                                                                           |
|---------------|-----------------------------------------------------------------------------------|
|$SCRIPT_DIR    |zabbix-twilioディレクトリのパス                                                    |
|$SCRIPT_URL    |zabbix-twilio.phpのURL(Twilioサーバーからアクセスできる必要があります)             |
|$ACCOUNT_SID   |TwilioのACCOUNT SID                                                                |
|$AUTH_TOKEN    |TwilioのAUTH TOKEN                                                                 |
|$TWILIO_NUMBER |Twilioで使用する電話番号(発信番号)                                                 |
|$CALL_TIME     |電話呼出時間(秒)                                                                   |
|$MESSAGE_NOTICE|通知時に読み上げるメッセージ(マクロ $$MESSAGE$$[^1] と$$DIGITS$$[^2]が使用できます)|
|$MESSAGE_RETYPE|確認キーが一致しない時読み上げるメッセージ(マクロ $$DIGITS$$[^2]が使用できます)    |
|$MESSAGE_REG_OK|Zabbixへの登録がOKの場合に読み上げるメッセージ                                     |
|$MESSAGE_REG_NG|Zabbixへの登録がNGの場合に読み上げるメッセージ                                     |
|$DIGITS        |確認キー(1-9の数値で指定し、一致するとZabbixへ登録する。桁数はREGKEYで指定可能。)  |
|$DIGITS_NUM    |確認キーの桁数                                                                     |
|$DIGITS_TIMEOUT|確認キーの入力待ち時間                                                             |
|$ZABBIX_API    |登録を行うZabbixサーバーのAPI URL                                                  |
|$ZABBIX_USER   |イベント登録時に使用するZabbixユーザーの名前                                       |
|$ZABBIX_PASS   |Zabbixユーザーのパスワード                                                         |


[^1]: Zabbixのアクションで設定するメッセージに`message:<任意の文字列>`を指定したものと置き換えます。
[^2]: `config.php`内の`$DIGITS`確認キーの値と置き換えます。

使用方法
============

## 1.メディアタイプの作成

--------------------------------------------------

ZabbixのWeb管理画面にログインし、以下の手順でメディアタイプを作成します。

タブの`管理`->`メディアタイプ`の右上にある`メディアタイプ`の作成ボタンをクリックします。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss01.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss01.png" style="width:100%;height:100%" /></a>

タイプを`スクリプト`を選択し、名前とスクリプト名を入力します。
スクリプト名にはスクリプトのパスを入力します。`AlertScriptsPath`からの相対パスになります。

以下、入力例

    名前：Twilio
    スクリプト名：zabbix-twilio/twilio-call.php

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss02.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss02.png" style="width:100%;height:100%" /></a>

## 2.連絡先の追加

--------------------------------------------------

1.で作成したメディアタイプを以下の手順でユーザーに追加します。

タブの`管理`->`ユーザー`で追加したユーザー名をクリックします。タブの`メディア`ページ内にある`追加`をクリック。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss03.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss03.png" style="width:100%;height:100%" /></a>

タイプの所で先ほど作成したタイプを選択し、送信先に電話番号を入力します。追加をクリックし、保存ボタンを押します。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss04.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss04.png" style="width:100%;height:100%" /></a>

## 3.アクションの設定

--------------------------------------------------

電話通知を行う為のアクションを以下の手順にて作成します。

タブの`設定`->`アクション`の右上にある`アクションの作成`をクリックします。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss05.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss05.png" style="width:100%;height:100%" /></a>

タブの`アクション`にある`デフォルトのメッセージ`に`eventid:{EVENT.ID}`を**必ず入れてください**。
`eventid:{EVENT.ID}`は障害対応イベントのコメントへ登録するときに使用します。(注意:`eventid:`と{EVENT.ID}の間にスペースを入れないで下さい)<BR>
また、任意で`message:<任意の文字列>`を入れて、`config.php`内の`$MESSAGE_NOTICE`に`$$MESSAGE$$`マクロを入れることで`message:`で指定した文字列を読み上げることができます。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss06.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss06.png" style="width:100%;height:100%" /></a>

タブの`アクションの実行内容`で新規をクリックし、メール通知などと同じように通知設定を行って保存をクリック。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss07.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss07.png" style="width:100%;height:100%" /></a>

以上で設定は完了となります。障害が発生すると電話通知がされるようになります。
　　
　　
電話通知が来ると設定したメッセージが読み上げあられ、確認キーが一致すると下記のようにZabbixの障害対応コメントへ登録されます。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss08.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss08.png" style="width:100%;height:100%" /></a>

以下がコメントの内容。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss09.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss09.png" style="width:100%;height:100%" /></a>

## 4.(参考)エスカレーション設定

--------------------------------------------------

電話に出れない場合や電話に出ても対応できない場合などに次の人へエスカレーションする場合は下記Zabbixの設定で実現できます。

下記は対応可能者(障害対応コメントがコメントありになるまで)がでるまで3人に対して順番に掛けていき、最大3順する設定方法の例になります。

アクション設定で`アクションの実行内容`ページで`新規`をクリック

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss10.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss10.png" style="width:100%;height:100%" /></a>

`デフォルトのアクション実行ステップの間隔`で電話を掛ける間隔を設定します。コール時間以上に設定し下さい。<BR>
`ステップ`の`開始`と`終了`にステップ数をを入れて下さい。
`次のメディアのみ使用`で`Twilio`を選択。<BR>
`アクションの実行条件`に`障害対応済み=コメントなし`の条件を追加して下さい。追加をクリックし、保存して下さい。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss11.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss11.png" style="width:100%;height:100%" /></a>

同じ方法で3人目や2順目以降を登録すれば対応可能な人がでるまでエスカレーションを行います。

<a href="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss12.png" target="_blank"><img src="http://54.199.40.83/phest/sites/zabbix-twilio/output/local/image/ss01/ss12.png" style="width:100%;height:100%" /></a>

問い合わせ先
============


zabbix-prj@begood-tech.com

参考
============

- [Zabbix](http://www.zabbix.com/jp/)
- [Twilio](http://twilio.kddi-web.com/)
- [pg_monz](http://pg-monz.github.io/pg_monz/)
- [Phest](https://github.com/chatwork/Phest)

LICENSE
============

Licensed under MIT,  see [LICENSE](https://github.com/chatwork/Phest/blob/master/LICENSE)

