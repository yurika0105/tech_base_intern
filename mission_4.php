<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
</head>
<body>

<?php
header('Content-Type:text/html;charset=utf8');

$namae = $_POST['namae'];
$comment = $_POST['comment'];
$number = $_POST['number'];
$pass = $_POST['pass']."<>";
$pass2 = $_POST['pass2'];
$pass3 = $_POST['pass3'];
$time = date('Y年m月d日 H:i:s');


//データベースに接続
$dsn='mysql:host=localhost;dbname='データベース名';charset=utf8;';
$user='ユーザー名';
$password='パスワード';
$pdo = new PDO($dsn,$user,$password);
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);


/*テーブル削除する時使う
$sql="DROP TABLE keijibandata";
$results=$pdo->query($sql);*/


//データを入れるテーブル作成
$sql="CREATE TABLE IF NOT EXISTS keijibandata"
."("
."id INT NOT NULL AUTO_INCREMENT,"
."namae char(32),"
."comment TEXT,"
."time TEXT,"
."PRIMARY KEY (id)"
.");";
//SQLクエリ実行(アロー演算子 ～の）
$stmt = $pdo->query($sql);


//パスワードを入れるテーブル作成
$sql="CREATE TABLE IF NOT EXISTS passdata"
."("
."password TEXT"
.");";
//SQLクエリ実行(アロー演算子 ～の）
$stmt = $pdo->query($sql);


/*今入っているデータを全削除するとき使う
$sql='TRUNCATE TABLE keijibandata';
$result=$pdo->query($sql);*/


//名前とコメント欄が空でない場合
if(!empty($namae) && !empty($comment) && !empty($pass)){
 //かつ編集投稿番号欄が空の場合
	if(empty($number)){
  		//入力データを追加
  		$sql=$pdo->prepare("INSERT INTO keijibandata(namae,comment,time) VALUES(:namae,:comment,:time)");
		$sql->bindParam(':namae',$namae,PDO::PARAM_STR);
  		$sql->bindParam(':comment',$comment,PDO::PARAM_STR);
  		$sql->bindParam(':time',$time,PDO::PARAM_STR);
  		$namae=$_POST['namae'];
  		$comment=$_POST['comment'];
  		$time=date('Y年m月d日 H:i:s');
  		$sql->execute();
  		
  		//今入っているパスワードを削除
  		$sql='DELETE FROM passdata';
  		$result=$pdo->query($sql);
  		//パスワードデータを追加
  		$sql=$pdo->prepare("INSERT INTO passdata(password) VALUES(:password)");
  		$sql->bindParam(':password',$password,PDO::PARAM_STR);
  		$password=$_POST['pass'];
  		$sql->execute();
	}
}


//削除機能
if(!empty($_POST['sakujyo']) && !empty($_POST['pass2'])){
	$pass2 = $_POST['pass2'];
	$sql='SELECT*FROM passdata';
	$results=$pdo->query($sql);
	foreach ($results as $line){
		$passw = $line['password'];
		//パスワードがあってたら
		if($passw == $pass2){
			$sakujyo = $_POST['sakujyo'];
			$sql='SELECT*FROM keijibandata where id=(select max(id) from keijibandata)';
			$results=$pdo->query($sql);
			foreach ($results as $line){
				$lastid = $line['id'];
				//削除される行が最終行であったら投稿番号を詰める
				if($lastid == $sakujyo){
   					//入っていた値と同じであったら削除
					$sql="DELETE FROM keijibandata WHERE id=$sakujyo";
					$result=$pdo->query($sql);
					echo $sakujyo."番が削除されました。";

					//投稿番号を削除した行まで詰める
					$sql="ALTER TABLE keijibandata AUTO_INCREMENT = $sakujyo";
					$results=$pdo->query($sql);

				}else{
					//入っていた値と同じであったら削除
					$sql="DELETE FROM keijibandata WHERE id=$sakujyo";
					$result=$pdo->query($sql);
					echo $sakujyo."番が削除されました。";
				}
			}
		}else{
		echo "パスワードが違います。";
		}
	}
}


//編集番号の値を取得し、フォームに値を取得
if(!empty($_POST['hensyuu']) && !empty($_POST['pass3'])){
	$pass3 = $_POST['pass3'];
	$sql='SELECT*FROM passdata';
	$results=$pdo->query($sql);
	foreach ($results as $line){
		$passw = $line['password'];
		if($passw == $pass3){
			$hensyuu = $_POST['hensyuu'];
			$sql="SELECT*FROM keijibandata WHERE id=$hensyuu";
			$results=$pdo->query($sql);
			foreach ($results as $line){
				$data1 = $line['namae'];
				$data2 = $line['comment'];
				$data0 = $line['id'];
				echo $hensyuu."番を編集しようとしています。";
			}
		}else{
		echo "パスワードが違います。";
		}
	}
}


//編集
//投稿番号欄が空でない場合
if(!empty($_POST['number'])){
	$number = $_POST['number'];
	$sql=$pdo->prepare("UPDATE keijibandata set namae=:nm,comment=:cm,time=:tm WHERE id=$number");
	$sql->bindParam(':nm',$nm,PDO::PARAM_STR);
	$sql->bindParam(':cm',$cm,PDO::PARAM_STR);
	$sql->bindParam(':tm',$tm,PDO::PARAM_STR);
	$nm=$_POST['namae'];
	$cm=$_POST['comment'];
	$tm=date('Y年m月d日 H:i:s');
	$sql->execute();
	echo $number."番を編集しました。";
}

?>

<form action="mission_4.php" method="post">
名前：<input type="text" name="namae" value="<?php echo $data1; ?>" ><br>
コメント：<input type="text" name="comment"  value="<?php echo $data2; ?>"><br>
<input type="hidden" name="number" value="<?php echo $data0; ?>">
<input type="text" name="pass" placeholder="パスワード">
<input type="submit" value="送信"><br>
<br>
</form>

<form action="mission_4.php" method="post">
<input type="text" name="sakujyo" placeholder="削除対象番号"><br>
<input type="text" name="pass2" placeholder="パスワード">
<input type="submit" value="削除">
<br>
<br>
</form>

<form action="mission_4.php" method="post">
<input type="text" name="hensyuu" placeholder="編集対象番号"><br>
<input type="text" name="pass3" placeholder="パスワード">
<input type="submit" value="編集">
<br>

<?php
//データを表示
$sql='SELECT*FROM keijibandata ORDER BY id ASC';
$results=$pdo->query($sql);
foreach ($results as $line){
 echo $line['id'].',';
 echo $line['namae'].',';
 echo $line['comment'].',';
 echo $line['time'].'<br>';
}

?>

</form>

</body>
</html>
