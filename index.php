<?php
/**
 * @file
 * User has successfully authenticated with Twitter. Access tokens saved to session and DB.
 */

/* Load required lib files. */
session_start();
require_once('twitteroauth.php');
require_once('config.php');

//データベースにコネクトする
$db = new PDO("mysql:host=localhost;dbname=", "", "");
$db->exec("SET CHARACTER SET utf8");
$db->exec("SET NAMES utf8");
$db->exec("SET time_zone = '+9:00'");

//ツイッターからログインして戻ってくるコールバッグ
if($_SESSION['oauth_token'] && $_SESSION['oauth_token_secret'] && $_REQUEST['oauth_verifier']){
	
	//アクセストークンを手に入れる
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	
	//アクセストークンを用いて、ログインユーザ情報を手に入れる
	$connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
	$data = $connection->get('users/show', array('user_id' => $access_token["user_id"]));
	//ユーザ情報からユーザをDBに存在するか確認する
	$sql = "SELECT * FROM users WHERE twitter_id = ?;";
	$tmp = $db->prepare($sql);
	$tmp->execute(array($data->id_str));
	$res = $tmp->fetchAll(PDO::FETCH_ASSOC);
	
	//ユーザが既に登録されている場合、データを更新
	if(count($res) > 0){
		$sql = "UPDATE users SET name = ?,image = ? WHERE id = ?;";
		$tmp = $db->prepare($sql);
		$tmp->execute(array($data->screen_name,$data->profile_image_url,$res[0]["id"]));
	//ユーザがまだ登録されていない場合、データの挿入
	}else{
		$sql = "INSERT INTO users (id,`twitter_id`,`name`,`image`,created,modified) VALUES (null,?,?,?,now(),now());";
		$tmp = $db->prepare($sql);
		$tmp->execute(array($data->id_str,$data->screen_name,$data->profile_image_url));
	}
	
	$_SESSION['access_token'] = $access_token;
	unset($_SESSION['oauth_token']);
	unset($_SESSION['oauth_token_secret']);
	
	//処理が終了したので自分にリダイレクト
	header('Location:'.SITE_URL);
	exit();
}

//ログインしていない場合はログインさせる
if(empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {

	/* Build TwitterOAuth object with client credentials. */
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	 
	/* Get temporary credentials. */
	$request_token = $connection->getRequestToken(OAUTH_CALLBACK);
	
	/* Save temporary credentials to session. */
	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	 
	//ツイッターのログイン画面に飛ばす
    $url = $connection->getAuthorizeURL($token);
    header('Location: ' . $url); 
	exit();
}

//ログイン済みの場合
//ユーザ情報からユーザをDBに存在するか確認する
$sql = "SELECT * FROM users WHERE twitter_id = ?;";
$tmp = $db->prepare($sql);
$tmp->execute(array($_SESSION['access_token']["user_id"]));
$res = $tmp->fetchAll(PDO::FETCH_ASSOC);

$user = $res[0];

?>
<img src="<?= $user["image"] ?>"/><?= $user["name"] ?>
