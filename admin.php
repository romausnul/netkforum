<? // WR-forum Lite v 2.3 UTF-8 //  07.01.2023 г.  //  WR-Script.ru

error_reporting (E_ALL); //error_reporting(0);
ini_set('register_globals','off'); // Все скрипты написаны для этой настройки php

$podpis_pokaz=TRUE; // Показывать подпись участников ВСЕМ (включая не зарегистрированных и поисковиков)
include "data/config.php";

$skey="657567"; // Секретный ключ НЕ МЕНЯТЬ !!! 
$adminpass=$password; // Авторизация


function replacer ($text) { // ФУНКЦИЯ очистки кода
$text=str_replace("&#032;",' ',$text);
$text=str_replace(">",'&gt;',$text);
$text=str_replace("<",'&lt;',$text);
$text=str_replace("\"",'&quot;',$text);
$text=preg_replace("/\n\n/",'<p>',$text);
$text=preg_replace("/\n/",'<br>',$text);
$text=preg_replace("/\\\$/",'&#036;',$text);
$text=preg_replace("/\r/",'',$text);
$text=preg_replace("/\\\/",'&#092;',$text);
$text=str_replace("\r\n","<br> ",$text);
$text=str_replace("\n\n",'<p> ',$text);
$text=str_replace("\n",'<br> ',$text);
$text=str_replace("\t",'',$text);
$text=str_replace("\r",'',$text);
$text=str_replace('   ',' ',$text);
return $text; }


function unreplacer ($text) { // ФУНКЦИЯ замены спецсимволов конца строки на обычные
$text=str_replace("&lt;br&gt;","<br>",$text);
$text=str_replace("&#124;","|",$text);
return $text;}


function nospam() { global $max_key,$rand_key; // Функция АНТИСПАМ
if (array_key_exists("image", $_REQUEST)) { $num=replacer($_REQUEST["image"]);
for ($i=0; $i<10; $i++) {if (md5("$i+$rand_key")==$num) {imgwr($st,$i); die();}} }
$xkey=""; mt_srand(time()+(double)microtime()*1000000);
$dopkod=mktime(0,0,0,date("m"),date("d"),date("Y")); // доп.код: меняется каждые 24 часа
$stime=md5("$dopkod+$rand_key");// доп.код
echo'Защитный код: <noindex>';
for ($i=0; $i<$max_key; $i++) {
$snum[$i]=mt_rand(0,9); $psnum=md5($snum[$i]+$rand_key+$dopkod);
echo "<img src=antispam.php?image=$psnum border='0' alt=''>\n";
$xkey=$xkey.$snum[$i];}
$xkey=md5("$xkey+$rand_key+$dopkod"); //число + ключ из data/config.php + код меняющийся кажые 24 часа
print"</noindex> <input name='usernum' class=post type='text' style='WIDTH: 70px;' maxlength=$max_key size=6>
<input name=xkey type=hidden value='$xkey'>
<input name=stime type=hidden value='$stime'>";
return; }


// Выбран ВЫХОД - очищаем куки 11-11-18
if(isset($_GET['event'])) { if ($_GET['event']=="clearcooke") { 
$url="http".(($_SERVER['SERVER_PORT']==443)?"s":"")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; $forum_url=str_replace('admin.php?event=clearcooke','',"$url");
setcookie("wrforumm","",time()-3600); Header("Location: $forum_url"); exit; } }

if (isset($_COOKIE['wrforumm'])) { // Сверяем имя/пароль из КУКИ с заданным в конфиг файле
$text=$_COOKIE['wrforumm'];
$text=trim($text); // Вырезает ПРОБЕЛьные символы 
if (mb_strlen($text)>60) exit("Попытка взлома - длина переменной куки сильно большая!");
$text=replacer($text);
$exd=explode("|",$text); $name1=$exd[0]; $pass1=$exd[1];
if (($name1!=$adminname and $name1!=$modername) or ($pass1!=$adminpass and $pass1!=$moderpass)) {sleep(1); setcookie("wrforumm", "0", time()-3600); Header("Location: admin.php"); exit;}

} else { // ЕСЛИ ваще нету КУКИ

if (isset($_POST['name']) & isset($_POST['pass'])) { // Если есть переменные из формы ввода пароля
$name=str_replace("|","I",$_POST['name']); $pass=str_replace("|","I",$_POST['pass']);
$text="$name|$pass|";
$text=trim($text); // Вырезает ПРОБЕЛьные символы 
if (mb_strlen($text)<4) exit("$back Вы не ввели имя или пароль!");
$text=replacer($text);
$exd=explode("|",$text); $name=$exd[0]; $pass=$exd[1];

//$msg_onpage=md5("$pass+$skey"); exit("$msg_onpage"); // РАЗБЛОКИРУЙТЕ для получения MD5 своего пароля!

//--А-Н-Т-И-С-П-А-М--проверка кода--
if ($antispam==TRUE) {
if (!isset($_POST['usernum']) or !isset($_POST['xkey']) or !isset($_POST['stime']) ) exit("данные из формы не поступили!");
$usernum=replacer($_POST['usernum']); $xkey=replacer($_POST['xkey']); $stime=replacer($_POST['stime']);
$dopkod=mktime(0,0,0,date("m"),date("d"),date("Y")); // доп.код. Меняется каждые 24 часа
$usertime=md5("$dopkod+$rand_key");// доп.код
$userkey=md5("$usernum+$rand_key+$dopkod");
if (($usertime!=$stime) or ($userkey!=$xkey)) exit("введён ОШИБОЧНЫЙ код!");}


// Сверяем введённое имя/пароль с заданным в конфиг файле и пишем в лог инфу
$tektime=time(); $verno="0";
if ($name==$adminname & md5("$pass+$skey")==$adminpass) $verno="1";
if ($name==$modername & md5("$pass+$skey")==$moderpass) $verno="1";
$fp=fopen("$datadir/adminlog.csv","a+");
flock ($fp,LOCK_EX); 
fputs($fp,"$tektime|$verno|$name|0||\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// присваиваются куки АДМИНИСТРАТОРУ
if ($name==$adminname & md5("$pass+$skey")==$adminpass) {$wrforumm="$adminname|$adminpass|$tektime|"; setcookie("wrforumm", $wrforumm, time()+18000); Header("Location: admin.php"); exit;}
// присваиваются куки МОДЕРАТОРУ
if ($name==$modername & md5("$pass+$skey")==$moderpass) {$wrforumm="$modername|$moderpass|$tektime|"; setcookie("wrforumm", $wrforumm, time()+18000); Header("Location: admin.php"); exit;}
exit("Ваши данные <B>ОШИБОЧНЫ</B>!</center>");

} else { // если нету данных, то выводим ФОРМУ ввода пароля


// 11-11-2018 г. Новый блок авторизации
echo '<html><head><META HTTP-EQUIV="Pragma" CONTENT="no-cache"><META HTTP-EQUIV="Cache-Control" CONTENT="no-cache"><META content="text/html; charset=UTF-8" http-equiv=Content-Type><style>
body {background: #D5EAFF; font-family: "Roboto", sans-serif; font-size: 15px;}
.login-page {width: 350px;padding: 6% 0 0;margin: auto;}
.form button:hover,.form button:active,.form button:focus {background: #1CB5FF;}
.form .message {margin: 15px 0 0;color: #b3b3b3;}
.form .message a {color: #0080FF;text-decoration: none;}
.form {position: relative;z-index: 1;background: #FFFFFF;max-width: 350px;margin: 0 auto 100px;padding: 45px;text-align: center;box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);}
.form input {font-family: "Roboto", sans-serif;outline: 0;background: #f2f2f2;width: 100%;border: 0;margin: 0 0 15px;padding: 15px;box-sizing: border-box;font-size: 14px;}
.form button {font-family: "Roboto", sans-serif;text-transform: uppercase;outline: 0;background: #0080C0;width: 100%;border: 0;padding: 15px;color: #FFFFFF;font-size: 14px;-webkit-transition: all 0.3 ease;transition: all 0.3 ease;cursor: pointer;}
</style></head><body>
<div class="login-page">
<div class="form">
Авторизация: WR-Forum 2.3<BR><BR>
<form action="admin.php" method=POST name=pswrd>
<input type="text" name=name value="" placeholder="логин"/>
<input type="password" name=pass placeholder="пароль"/>';


if ($antispam==TRUE) nospam(); // АНТИСПАМ !


print"<button>Войти</button><p class=\"message\">Проблемы при входе? <a href=\"admin.php?event=clearcooke\">Очистить КУКИ</a></p></form></div></div>
<SCRIPT language=JavaScript>document.pswrd.name.focus();</SCRIPT>
<center><small>Powered by <a href=\"https://www.wr-script.ru\" title=\"Скрипт форума\" class='copyright'>WR-Forum Lite</a> &copy; 2.3 UTF-8<br></small></center></body></html>";
exit;}





} // АВТОРИЗАЦИЯ ПРОЙДЕНА!

$gbc=$_COOKIE['wrforumm']; $gbc=explode("|", $gbc); $gbname=$gbc[0];$gbpass=$gbc[1];$gbtime=$gbc[2];
if ($gbname==$adminname) $ktotut="1"; else $ktotut="2"; // Кто вошёл: админ или модер?

// Определяем URL форума 11-11-2018 поддержка http / https
$url="http".(($_SERVER['SERVER_PORT']==443)?"s":"")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; $forum_url=str_replace('admin.php','',"$url");






// РАССЫЛКА сообщений участникам форума
if(isset($_GET['event'])) { if ($_GET['event']=="rassilochka") {
$name=replacer($_POST['name']);
$email=replacer($_POST['email']);
if (isset($_POST['autoscribe'])) $autoscribe="1"; else $autoscribe="0";
$userdata=replacer($_POST['userdata']); if (mb_strlen($userdata)<5) exit("Вы не выбрали участника форума, которому отправялем сообщение!");
$dt=explode("|", $userdata); $username=$dt[1]; $useremail=$dt[2];
$msg=$_POST['msg'];
if ($autoscribe!="1") { // Разово записываем текст рассылки в шаблон!
$fp=fopen("$datadir/mailtext.csv","w");
flock ($fp,LOCK_EX); 
fputs($fp,$msg);
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);}

// Для Выбора схемы - раскоментируйте её и закоментируйте текущую символами //
//$bdcolor="#79BBEF"; $fcolor="#00293E"; // Светлоголубой
$bdcolor="#FF9A00"; $fcolor="#833C07"; // Оранжевый
//$bdcolor="#FFE51A"; $fcolor="#FF8000"; // Жёлто-оранжевый
//$bdcolor="#00E900"; $fcolor="#005300"; // Светло-зеленый
//$bdcolor="#FB5037"; $fcolor="#620000"; // Красный
//$bdcolor="#800080"; $fcolor="#350035"; // Сиреневенький
//$bdcolor="#007800"; $fcolor="#000000"; // Темно зеленый
//$bdcolor="#D2A500"; $fcolor="#4A3406"; // Золотой
//$bdcolor="#BCC0C0"; $fcolor="#646464"; // Серый
//$bdcolor="#FFA8FF"; $fcolor="#800080"; // Розовый

// ТАБЛИЦА стилей зарыта ЗДЕСЬ !!!
$shapka="<html>
<head>
<META http-equiv=Content-Type content='text/html; charset=UTF-8'>
<style>
BODY,TD {FONT-FAMILY: verdana,arial,helvetica; FONT-SIZE: 14px;}
.pismo {BORDER-BOTTOM:$bdcolor 1px solid;}
.pismo2 {BORDER-LEFT:$bdcolor 1px solid; BORDER-BOTTOM:$bdcolor 1px solid;}
.remtop {font-weight: bold; color: $fcolor; padding:5px; border-top: 1px solid $fcolor; border-bottom: 1px solid $fcolor; background-color: $bdcolor;}
.remdata {font-weight: bold; margin:0; display:inline; color: $fcolor;}
input,textarea {font-family: Verdana; text-decoration: none; color: #000000; cursor: default; background-color: #FFFFFF; border-style: solid; border-width: 1px; border-color: $bdcolor;}
</style>
</head>
<BODY leftMargin=0 topMargin=0 rightMargin=0 bottomMargin=0 marginheight=0 marginwidth=0>";

// Настройки для отправки писем
$headers=null;
$headers.="From: $name <$email>\n";
$headers.="X-Mailer: PHP/".phpversion()."\n";
$headers.="Content-Type: text/html; charset=UTF-8";

$msg=str_replace("\r\n", "<br>",$msg);
$msg=str_replace("%name", "<B>$username</B>",$msg);
$msg=str_replace("%forum_name", "<B>$forum_name</B>",$msg);
$msg=str_replace("%forum_urllogin", "<B><a href='".$forum_url."tools.php?event=login'>".$forum_url."tools.php?event=login</a></B>",$msg);
$msg=str_replace("%forum_url", "<B><a href='$forum_url'>$forum_url</a></B>",$msg);

// Собираем всю информацию в теле письма
$allmsg="$shapka
<table cellpadding=5 cellspacing=0 align=center>
<TR><TD colspan=2><div class=remtop align=center>Сообщение c сайта \"<a href='$forum_url'>$forum_url</a>\"</div></TD></TR>
<TR><TD class=pismo><P class=remdata>Имя</P></TD><TD class=pismo2><B>$name<B></TD></TR>
<TR><TD class=pismo><P class=remdata>E-mail</P></TD><TD class=pismo2><a href='mailto:$email'>$email</a></td></tr>
<TR><TD class=pismo><P class=remdata>Дата отправки:</P></TD><TD class=pismo2>$date г. в $time</td></tr>
<TR><TD class=pismo><P class=remdata>Сообщение</P></TD><TD class=pismo2 align=left>$msg</td></tr>
</table>";

$printmsg="$allmsg 
<center><BR>Cообщение <B><font color=navy>успешно отправлено</font></B><BR><BR>
</body></html>";

$allmsg.="<BR><BR><BR>* Это сообщение отправлено с форума.</body></html>";

mail("$useremail", "Сообщение с сайта: $forum_name", $allmsg, $headers); // Отправляем письмо майлеру на съедение ;-)

print "<script language='Javascript'>function reload() {location=\"admin.php?event=massmail&user=$useremail&autoscribe=$autoscribe\"}; setTimeout('reload()', 3000);</script>$printmsg"; exit;

}}








if (isset($_GET['newuserpass'])) { // АДМИН меняет пароль юзеру

if (isset($_POST['newpass'])) {$newpass=replacer($_POST['newpass']); $email=replacer($_GET['email']);
$newpass=md5("$newpass"); // Шифруем пароль пользователя в МД5

// Ищем юзера с таким емайлом. Если есть - меняем
$email=strtolower($email); unset($fnomer); unset($ok); $oldpass="";
$lines=file("$datadir/user.php"); $ui=count($lines); $i=$ui;
do {$i--; $rdt=explode("|",$lines[$i]); 
$rdt[5]=strtolower($rdt[5]);
if ($rdt[5]===$email) {$oldpass=$rdt[3]; $fnomer=$i; $name=$rdt[2];}
} while($i > 1);

if (isset($fnomer)) { // обновление строку юзера в БД
$i=$ui; $dt=explode("|", $lines[$fnomer]);
$txtdat=$lines[$fnomer];
$txtdat=str_replace("$name|$oldpass","$name|$newpass",$txtdat);
$fp=fopen("$datadir/user.php","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<=(sizeof($lines)-1);$i++) {if ($i==$fnomer) fputs($fp,"$txtdat"); else fputs($fp,$lines[$i]);}
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp); }

Header("Location: admin.php?event=userwho"); exit; }}




// Блок удаления ВСЕХ НЕАКТИВИРОВАННЫХ УЧАСТНИКОВ
if(isset($_GET['delalluser'])) { $records="<?die;?> rn|time|name|password|zvezda|email|pol|drdate|delta_gmt|user_skin|icq|url|gorod|interes|sign|avatar|activation|\r\n";
$file=file("$datadir/user.php"); $maxi=count($file)-1; $i=0;
$fp=fopen("$datadir/user.php","w"); // удаляем строки с не активированными записями участников
flock ($fp,LOCK_EX);
do { $i++; $dt=explode("|",$file[$i]); 
if ($dt[16]==FALSE) $records=$records; else $records.=$file[$i]; } while($i<$maxi);
ftruncate ($fp,0);
fputs($fp, $records);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php?newstatistik"); exit; }




// Добавление IP-юзера в БАН
if (isset($_GET['badip'])) {
if (isset($_POST['ip'])) $ip=$_POST['ip'];
if (isset($_POST['to_time'])) $to_time=$_POST['to_time']; else $to_time="1";
if (isset($_GET['ip_get'])) {$ip=$_GET['ip_get']; $msg="За добавление нежелательных сообщений на форум! ЗА СПАМ!!!";} 
else $msg=$_POST['text'];
if (mb_strlen($ip)<8) exit("Введите IP по формату X.X.X.X, где Х - число от 1 до 255! Сейчас запрос пуст или IP НЕ указан!");
$from_time=time(); $to_time=$from_time+86400*$to_time*31; // Заблокирован с по такой то даты
$lock="0"; // Блокировка только на запись? 1 - полная. На чтение в том числе (пока не реализовано)
$text="$from_time|$to_time|$ip|$lock|$msg||"; $text=stripslashes($text); $text=htmlspecialchars($text,ENT_COMPAT,"UTF-8"); $text=str_replace("\r\n", "<br>", $text);
$fp=fopen("$datadir/ipblock.csv","a+");
flock ($fp,LOCK_EX);
fputs($fp,"$text\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php?event=blockip"); exit; }



// Удаления юзера из БАНА
if (isset($_GET['delip'])) { $xd=$_GET['delip'];
$file=file("$datadir/ipblock.csv"); $dt=explode("|",$file[$xd]); 
$fp=fopen("$datadir/ipblock.csv","w");
flock ($fp,LOCK_EX);
for ($i=0;$i< sizeof($file);$i++) { if ($i==$xd) unset($file[$i]); }
fputs($fp, implode("",$file));
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php?event=blockip"); exit; }



// АКТИВАЦИЯ пользователя
if(isset($_GET['event'])) { if ($_GET['event']=="activate") {

$key=$_GET['key']; $email=$_GET['email']; $page=$_GET['page'];

// защиты от взлома по ключу и емайлу
if (mb_strlen($key)<6 or strlen($key)>6 or !ctype_digit($key)) exit("$back. Вы ошиблись при вводе ключа. Ключ может содержать только 6 цифр.");
$email=stripslashes($email); $email=htmlspecialchars($email,ENT_COMPAT,"UTF-8");
$email=str_replace("|","I",$email); $email=str_replace("\r\n","<br>",$email);
if (mb_strlen($key)>30) exit("Ошибка при вводе емайла");

// Ищем юзера с таким емайлом и ключом. Если есть - меняем статус на пустое поле
$email=strtolower($email); unset($fnomer); unset($ok);
$lines=file("$datadir/user.php"); $ui=count($lines); $i=$ui;
do {$i--; $rdt=explode("|",$lines[$i]); 
$rdt[5]=strtolower($rdt[5]);
if ($rdt[5]===$email and $rdt[16]===$key) {$name=$rdt[2]; $pass=$rdt[3]; $fnomer=$i;}
if ($rdt[5]===$email and $rdt[16]==="") $ok="1";
} while($i > 1);

if (isset($fnomer)) {
// обновление строки юзера в БД
$i=$ui; $dt=explode("|", $lines[$fnomer]);
$txtdat="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|$dt[15]|$dt[16]|";
$fp=fopen("$datadir/user.php","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<=(sizeof($lines)-1);$i++) {if ($i==$fnomer) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]);}
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
}
if (!isset($fnomer) and !isset($ok)) exit("$back. Вы ошиблись в воде активационного ключа или емайла.</center>");
if (isset($ok)) $add="Запись активирована ранее"; else $add="$name, Пользователь успешно зарегистрирован.";

print"<html><head><link rel='stylesheet' href='$forum_skin' type='text/css'></head><body>
<script language='Javascript'>function reload() {location=\"admin.php?event=userwho&page=$page\"}; setTimeout('reload()', 2500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
Спасибо, <B>$add</B>.<BR><BR>Через несколько секунд Вы будете автоматически перемещены на страницу с участниками форума.<BR><BR>
<B><a href='admin.php?event=userwho&page=$page'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
exit;

}
}



if(isset($_GET['delfile'])) { // Блок УДАЛЕНИЯ / обнуления любого файла по маске
if ($_GET['delfile']=="chat") {unlink ("$datadir/chat.csv"); $fp=fopen("$datadir/chat.csv","a+");}
if ($_GET['delfile']=="adminlog") unlink ("$datadir/adminlog.csv");
if ($_GET['delfile']=="ipblock") unlink ("$datadir/ipblock.csv");
Header("Location: admin.php"); exit;}




// Блок ПЕРЕСЧЁТА кол-ва тем и сообщений +++ 08.2012 г.
if(isset($_GET['event'])) { if ($_GET['event']=="revolushion") {
$mainlines=file("$datadir/wrforum.csv"); $countmf=count($mainlines)-1; $i="-1";$u=$countmf-1;$k="0";

do {$i++; $dt=explode("|",$mainlines[$i]);

if ($dt[3]==FALSE) { $fid=$dt[2];
if ((is_file("$datadir/$fid.csv")) && (sizeof("$datadir/$fid.csv")>0)) {
$fl=file("$datadir/$fid.csv"); $kolvotem=count($fl); $kolvomsg="0";
for ($itf=0; $itf<$kolvotem; $itf++) {
$forumdt=explode("|",$fl[$itf]);
$id="$forumdt[2]$forumdt[3]"; $tema="$forumdt[5]";
 if ((!ctype_digit($id)) or (mb_strlen($id)!=7)) print"- В теме с названием '<B>$tema</B>': <a href='index.php?id=$fid'>index.php?id=<B>$fid</B></a> ' есть ошибка: <font color=red>Потерян идентификатор, то есть потеряна тема</font><br>";
 else { 
  if (is_file("$datadir/$id.csv")) {
  $msgfile=file("$datadir/$id.csv"); $countmsg=count($msgfile); $kolvomsg=$kolvomsg+$countmsg;
  } else print"- Проблема с темой с названием '<B>$tema</B>': <a href='index.php?id=$id'>index.php?id=<B>$id</B></a> - <font color=red>отсутствует файл с темой (видимо была удалена некорректно)!</font><br>";
 }
} // for

if ($kolvotem=="0") {$dt[11]=""; $dt[12]=""; $dt[13]=""; $dt[14]="";}
$mainlines[$i]="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$kolvotem|$kolvomsg|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|\r\n";
}

else { $kolvotem="0"; $kolvomsg="0";
$mainlines[$i]="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$kolvotem|$kolvomsg|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|\r\n";}
}
else $mainlines[$i]="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]||0|0|0|0|0|||||\r\n";

} while($i < $countmf);

// сохраняем обновлённые данные о кол-ве тем и сообщений в файле
$file=file("$datadir/wrforum.csv");
$fp=fopen("$datadir/wrforum.csv","w");
flock ($fp,LOCK_EX); 
for ($i=0;$i< sizeof($file);$i++) fputs($fp,$mainlines[$i]);
flock ($fp,LOCK_UN);
fclose($fp);

print "<center><BR><BR><BR>Всё успешно пересчитано.</center><script language='Javascript'><!--
function reload() {location=\"admin.php\"}; setTimeout('reload()', 3000);
--></script>";
exit; }}







// Блок удаления УЧАСТНИКА ФОРУМА
if (isset($_GET['usersdelete'])) { $usersdelete=$_GET['usersdelete'];

$first=$_POST['first']; $last=$_POST['last']; $page=$_GET['page']; $delnum=null; $i=0;

// Сравнимаем кол-во строк в файле ЮЗЕРОВ и их СТАТИСТИКУ
if (count(file("$datadir/user.php")) != count(file("$datadir/userstat.csv"))) exit("Статистика участников повреждена! Запустите блок: '<a href='admin.php?newstatistik'>Пересчитать статистику участников</a>',<br> а затем уже можно будет удалять участников!");

do {$dd="del$first"; if (isset($_POST["$dd"])) { $delnum[$i]=$first; $i++;} $first++; } while ($first<=$last);
$itogodel=count($delnum); $newi=0; 
if ($delnum=="") exit("Сделайте выбор хотябы одного участника!");
$file=file("$datadir/user.php"); $itogo=sizeof($file); $lines=null; $delyes="0";
for ($i=0; $i<$itogo; $i++) { // цикл по файлу с данными
for ($p=0; $p<$itogodel; $p++) {if ($i==$delnum[$p]) $delyes=1;} // цикл по строкам для удаления
// если нет метки на удаление записи - формируем новую строку массива, иначе - нет
if ($delyes!=1) {$lines[$newi]=$file[$i]; $newi++;} else $delyes="0"; }

// пишем новый массив в файл
$newitogo=count($lines); 
$fp=fopen("$datadir/user.php","w");
flock ($fp,LOCK_EX);
// если всех юзеров удаляем, тогда ничего туда ВПУТИТЬ :-))
if (isset($lines[0])) { for ($i=0; $i<$newitogo; $i++) fputs($fp,$lines[$i]); } else fputs($fp,"");
flock ($fp,LOCK_UN);
fclose($fp);

// Удаляем инфу о юзере из блока статистики - ДОРАБОТАТЬ блок!!!!
// сейчас делаю просто удалить ту запись, которая соответствует номеру
// но в идеале нужно проверять всю статистику и собирать файл
// заново - чтобы исключить любые ошибки

$file=file("$datadir/userstat.csv"); $itogo=sizeof($file); $lines=null; $delyes="0"; $newi=0;
for ($i=0; $i<$itogo; $i++) { // цикл по файлу с данными
for ($p=0; $p<$itogodel; $p++) {if ($i==$delnum[$p]) $delyes=1;} // цикл по строкам для удаления
// если нет метки на удаление записи - формируем новую строку массива, иначе - нет
if ($delyes!=1) {$lines[$newi]=$file[$i]; $newi++;} else $delyes="0"; }

// пишем новый массив в файл
$newitogo=count($lines); 
$fp=fopen("$datadir/userstat.csv","w");
flock ($fp,LOCK_EX);
// если статистику всех юзеров удаляем, тогда ничего туда ВПУТИТЬ :-))
if (isset($lines[0])) {for ($i=0; $i<$newitogo; $i++) fputs($fp,$lines[$i]);} else fputs($fp,"");
flock ($fp,LOCK_UN);
fclose($fp);

Header("Location: admin.php?event=userwho&page=$page"); exit; } 







if(isset($_GET['newstatistik'])) { // Блок ПЕРЕСЧЁТА СТАТИСТИКИ участников

$lines=null; $ok=null;
// 1. Открываем и считываем в память файл с юзерами
$ulines=file("$datadir/user.php"); $ui=count($ulines);

// 2. Открываем файл статистики
$slines=file("$datadir/userstat.csv"); $si=count($slines)-1;

// Цикл по кол-ву юзеров в базе
for ($i=1;$i<$ui;$i++) {
$udt=explode("|", $ulines[$i]);
if ($i<=$si) $sdt=explode("|",$slines[$i]); else $sdt[0]="";

if ($udt[0]==$sdt[0]) {$udt[0]=str_replace("\r\n","",$udt[0]); $ok=1; 
if (isset($sdt[5]) and isset($sdt[6]) and isset($sdt[7]) and isset($sdt[8])) 
{$lines[$i]="$slines[$i]";} else {$lines[$i]="$udt[0]|0|$udt[2]|0||0|0|0|0||||\r\n";}} // если RN=RN - значит данные верны

// Цикл в файле статистики - поиск строку текущего юзера
if ($ok!="1") {

for ($j=1;$j<$si;$j++) {
$sdt=explode("|", $slines[$j]);
if ($udt[0]==$sdt[0]) {$ok=1; $lines[$i]=$slines[$j]; }// если RN=RN - значит данные верны
}

if ($ok!="1") $lines[$i]="$udt[0]|0|$udt[2]|0||0|0|0|0||||\r\n"; // создаём юзера с нулевой статистикой
}
$ok=null; $ii=count($lines);}

$fp=fopen("$datadir/userstat.csv","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
fputs($fp,"rn_user|time|name|lock|lock_time|itogotem|itogomsg|repa|kosyaki|nikname|ip|rezerved|\r\n");
for ($i=1;$i<=$ii;$i++) fputs($fp,"$lines[$i]");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

Header("Location: admin.php?event=userwho"); exit; }







// Блок изменения СТАТУСА участника=08.2015 г.
if(isset($_GET['newstatus'])) { if ($_GET['newstatus'] !="") { $newstatus=$_GET['newstatus']-1; $status=$_POST['status'];
if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
if (mb_strlen($status)<3) exit("новый статус участника <B> < 3 символов </B> - это не серьёзно!");
$status=htmlspecialchars($status,ENT_COMPAT,"UTF-8"); $status=stripslashes($status);
$status=str_replace("|"," ",$status); $status=str_replace("\r\n","<br>",$status);
$lines=file("$datadir/userstat.csv"); $i=count($lines);
$dt=explode("|", $lines[$newstatus]);
$record="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$status|$dt[10]|$dt[11]|";

$fp=fopen("$datadir/userstat.csv","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<=(sizeof($lines)-1);$i++) { if ($i==$newstatus) fputs($fp,"$record\r\n"); else fputs($fp,$lines[$i]); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php?event=userwho&page=$page"); exit; } }




// Блок изменения ЗВЁЗД участника
if(isset($_GET['newreiting'])) { if ($_GET['newreiting'] !="") { $newreiting=$_GET['newreiting']-1; $reiting=$_POST['reiting'];
if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
$reiting=htmlspecialchars($reiting,ENT_COMPAT,"UTF-8"); $reiting=stripslashes($reiting);
$reiting=str_replace("|"," ",$reiting); $reiting=str_replace("\r\n","<br>",$reiting);
$lines=file("$datadir/user.php"); $i=count($lines);
$dt=explode("|", $lines[$newreiting]);
$txtdat="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$reiting|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|$dt[15]|$dt[16]|";

$fp=fopen("$datadir/user.php","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<=(sizeof($lines)-1);$i++) { if ($i==$newreiting) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php?event=userwho&page=$page"); exit; } }




// изменяем РЕПУТАЦИЮ юзера
if(isset($_GET['newrepa'])) {
if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
$text=$_POST['repa']; $usernum=$_POST['usernum']-1;
$text=htmlspecialchars($text,ENT_COMPAT,"UTF-8"); $text=stripslashes($text);
$text=str_replace("|"," ",$text); $repa=str_replace("\r\n","<br>",$text);
$lines=file("$datadir/userstat.csv");
$dt=explode("|", $lines[$usernum]);
$txtdat="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$repa|$dt[8]|$dt[9]|$dt[10]|$dt[11]|";
$fp=fopen("$datadir/userstat.csv","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<=(sizeof($lines)-1);$i++) { if ($i==$usernum) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php?event=userwho&page=$page"); exit; }





// Блок удаления файла, прикреплённого в сообщении

if(isset($_GET['deletefoto'])) { $deletefoto=replacer($_GET['deletefoto']);
$fid=replacer($_GET['fid']); $id=replacer($_GET['id']);
if (is_file("$filedir/$deletefoto")) unlink ("$filedir/$deletefoto"); // удаляем файл 
Header("Location: admin.php?fid=$fid&id=$id"); exit;}





// Добавляем/снимаем ШТРАФЫ ЮЗЕРУ
if(isset($_GET['userstatus'])) {
if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
$text=$_POST['submit']; $status=$_POST['status']; $usernum=$_POST['usernum']-1;
$text=htmlspecialchars($text,ENT_COMPAT,"UTF-8"); $text=stripslashes($text);
$text=str_replace("|"," ",$text); $submit=str_replace("\r\n","<br>",$text);
if (!ctype_digit($status)) $status=0;
$status=$status+$submit; // корректируем статус (+1 или -1)
if($status<0 or $status>5) exit("$back статус пользователя БОЛЬШЕ ЛИБО РАВЕН НУЛЮ, НО МЕНЬШЕ ЛИБО РАВЕН ПЯТИ!");
$lines=file("$datadir/userstat.csv");
if (!isset($lines[$usernum])) exit("ошибка! Нет такого пользователя в файле статистики!"); // если нет такой строка в файле статистики
$dt=explode("|", $lines[$usernum]); 
$dt[6]=str_replace("\r\n","",$dt[6]); $dt[7]=str_replace("\r\n","",$dt[7]);
$txtdat="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$status|$dt[9]|$dt[10]|$dt[11]|";
$fp=fopen("$datadir/userstat.csv","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<=(sizeof($lines)-1);$i++) { if ($i==$usernum) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php?event=userwho&page=$page"); exit; }



























// Блок ПЕРЕМЕЩЕНИЯ ВВЕРХ/ВНИЗ РАЗДЕЛА или ТОПИКА
if(isset($_GET['movetopic'])) { if ($_GET['movetopic'] !="") {
$move1=$_GET['movetopic']; $where=$_GET['where']; 
if ($where=="0") $where="-1";
$move2=$move1-$where;
$file=file("$datadir/wrforum.csv"); $imax=sizeof($file);
if (($move2>=$imax) or ($move2<"0")) exit(" НИЗЯ туда двигать!");
$data1=$file[$move1]; $data2=$file[$move2];

$fp=fopen("$datadir/wrforum.csv","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА 
// меняем местами два соседних раздела
for ($i=0; $i<$imax; $i++) {if ($move1==$i) fputs($fp,$data2); else {if ($move2==$i) fputs($fp,$data1); else fputs($fp,$file[$i]);}}
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php"); exit; }}




// Блок УДАЛЕНИЯ выбранного РАЗДЕЛА или ФОРУМА +++
if(isset($_GET['fxd'])) {
$fid=replacer($_GET['fxd']); if ($fid=="" or strlen($fid)!=3) exit("Ошибка, выбирите рубрику для удаления, либо ошибка скрипта!");

// считываем все файлы в папке data попорядку, удалем те, которые начинаются на $fid,
// (файлы с темами, голосованием -vote, IP-шниками голосования -ip, $fid - в темами)
if ($handle=opendir($datadir)) {
while (($file=readdir($handle)) !== false)
if (!is_dir($file)) { 
if (mb_strlen($file)==16 and stristr("-vote",$file)) unlink ("$datadir/$file"); // Удаляем файл с ГОЛОСОВАНИЯМИ в разделе
if (mb_strlen($file)==14 and stristr("-ip",$file)) unlink ("$datadir/$file"); // Удаляем файл с IP-голосовавших
$tema=mb_substr($file,0,3); if($tema==$fid) unlink ("$datadir/$file"); // Удаляем все темы в удаляемом РАЗДЕЛЕ
// Дублирующая функция if($file=="$fid.csv") unlink ("$datadir/$fid.csv"); // Удаляем сам РАЗДЕЛ
} closedir($handle); } else echo'Ошибка!';

// удаляем строку, соответствующую теме в файле со всеми темами
$file=file("$datadir/wrforum.csv");
$fp=fopen("$datadir/wrforum.csv","w");
flock ($fp,LOCK_EX);
for ($i=0;$i< sizeof($file);$i++) {$dt=explode("|",$file[$i]); if ($dt[2]==$fid) unset($file[$i]);}
fputs($fp, implode("",$file));
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php"); exit; }





// Блок УДАЛЕНИЯ выбранного ПОСЛЕДНЕГО СООБЩЕНИЯ или очистки всего файла с последними темами +++
if(isset($_GET['lxd'])) {
$id=replacer($_GET['lxd']); if ($id=="" or strlen($id)!=7) exit("Ошибка, выбирете сообщение для удаления, либо ошибка скрипта!");
// считываем файл news.csv и удаляем строку, соответствующую сообщению в файле
$file=file("$datadir/news.csv");
$fp=fopen("$datadir/news.csv","w");
flock ($fp,LOCK_EX);
for ($i=0;$i< sizeof($file);$i++) {$dt=explode("|",$file[$i]); if ("$dt[2]$dt[3]"==$id) unset($file[$i]);}
if ($id!="9999999") fputs($fp, implode("",$file));
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php"); exit; }






// БЛОК ПЕРЕНУМЕРАЦИИ ТЕМЫ +++ 22.09.2012  (не используется с версии 2.0.2. Удалить в 2017 году!)
if (isset($_GET['rename'])) { if ($_GET['rename'] !="") {

$fid=$_GET['id']; $id_old=$_GET['rename']; $page=$_GET['page'];
$id_old=mb_substr($id_old,3,4);
// ID темы хранится в самой теме, в рубрике, в 10-ке последних, на главной в последней теме
// везде нужно исправить!!! везде!
// 1. Считываем рубрикатор, генерируем новый ID темы

// БЛОК ГЕНЕРИРУЕТ СЛЕДУЮЩИЙ ПО ПОРЯДКУ НОМЕР ТЕМЫ, начиная просмотр с 1000 * 23.09.2012
// считываем весь файл с темами в память
$id=1000; $allid=null; $records=file("$datadir/$fid.csv"); $imax=count($records); $i=$imax;
if ($i > 0) { do {$i--; $rd=explode("|",$records[$i]); $allid[$i]="$rd[2]$rd[3]"; } while($i>0);
do $id++; while(in_array($id,$allid) or is_file("$datadir/$fid$id.csv")); }

// Считываем содержимое РУБРИКИ и делаем замену |ID старый| на новый по всему файлу
$rec=file_get_contents("$datadir/$fid.csv"); $rec=str_replace("|$fid|$id_old|","|$fid|$id|",$rec);

$fp=fopen("$datadir/$fid.csv","w+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
fputs($fp,"$rec");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

rename("$datadir/$fid$id_old.csv", "$datadir/$fid$id.csv"); // Переименовываем файл
$rec=file_get_contents("$datadir/$fid$id.csv"); $rec=str_replace("|$fid|$id_old|","|$fid|$id|",$rec);

$fp=fopen("$datadir/$fid$id.csv","w+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
fputs($fp,"$rec");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// 4. Считываем содержимое ГЛАВНОЙ и делаем замену |ID старый| на новый по всему файлу
$rec=file_get_contents("$datadir/wrforum.csv"); $rec=str_replace("|$fid$id_old|","|$fid$id|",$rec);
$fp=fopen("$datadir/wrforum.csv","w+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
fputs($fp,"$rec");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// 5. Считываем содержимое ПОСЛЕДНИХ 20 тем и делаем замену |ID старый| на новый по всему файлу
$rec=file_get_contents("$datadir/news.csv"); // Считываем содержимое
$rec=str_replace("|$fid|$id_old|","|$fid|$id|",$rec); // Заменяем |ID старый| на новый по всему файлу
$fp=fopen("$datadir/news.csv","w+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
fputs($fp,"$rec");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// короче в цикле сделать! В начале цикл от 1 до 4-х, потом из массива имён файлов выбирать
// нужный

Header("Location: admin.php?id=$fid&page=$page"); exit; } }













// Блок удаления выбранной ТЕМЫ [изменён в 2016г.]

if (isset($_GET['xd'])) { if ($_GET['xd'] !="") {
if (isset($_GET['page'])) $page=$_GET['page']; else $page="0";
$id=replacer($_GET['xd']); $fid=mb_substr($id,0,3); $id3=mb_substr($id,3,4);

if ((!ctype_digit($id)) or (mb_strlen($id)!=7)) exit("<B>$back. Ошибочный идентификатор темы - xd! Ошибка скрипта. Эту тему можно удалить только вручную!</B>");
$file=file("$datadir/$fid.csv");

if (is_file("$datadir/$id-vote.csv")) unlink("$datadir/$id-vote.csv"); // удаляем файл с ГОЛОСОВАНИЕМ
if (is_file("$datadir/$id-ip.csv")) unlink("$datadir/$id-ip.csv"); // удаляем файл с голосовавшими IP
$minmsg=1; $delf=null; // Считаем кол-во сообщений
if (is_file("$datadir/$id.csv")) {$lines=file("$datadir/$id.csv"); $minmsg=count($lines); unlink ("$datadir/$id.csv");}

// удаляем строку, соответствующую теме в файле с текущими темами
$fp=fopen("$datadir/$fid.csv","w");
$kolvotem=sizeof($file)-1; // кол-во тем для уточнения на главной
$newlines=null;
flock ($fp,LOCK_EX);
for ($i=0;$i<sizeof($file)+1;$i++) {
$dt=explode("|",$file[$i]);
if ("$dt[2]$dt[3]"!=$id) {
$filename="$dt[2]$dt[3].csv"; if (is_file("$datadir/$filename")) $ftime=filemtime("$datadir/$filename"); else $ftime="";
$newlines[$i]="$ftime|$dt[2]$dt[3]|"; // собираем дату последнего доступа к файлу и номер темы
} else unset($file[$i]); // удаляем строку
} //for
fputs($fp, implode("",$file));
flock ($fp,LOCK_UN);
fclose($fp);

// Блок вычитает 1-цу из кол-ва тем и вычитает кол-во сообщений, обновляет последнюю удалённую тему
unset($lines); $lines=file("$datadir/wrforum.csv"); $imax=count($lines);
// находим по fid номер строки
for ($ii=0;$ii<$imax;$ii++) {$kdt=explode("|",$lines[$ii]); if ($kdt[2]==$fid) {$mnumer=$ii; $ii=$imax;}}
$dt=explode("|",$lines[$mnumer]);
$dt[7]=$dt[7]-$minmsg; if ($dt[7]<0) $dt[7]="0";
if ($kolvotem<="0") $dt[7]="0";

// если удаляемая тема стоит на главной как последняя или там пусто, то удаляем её с главной
if ($dt[11]=="" or $dt[11]==$id or $dt[7]==0) {
if (isset($newlines)) {
$imax=count($newlines); if ($imax>1) rsort($newlines);
$ddt=explode("|",$newlines[0]); //print_r($newlines); exit;
$filename="$ddt[1].csv"; if (is_file("$datadir/$filename")) {$filedata=file("$datadir/$filename"); $imax=count($filedata)-1;} else {$imax="0"; $filedata="";}
$ddt=explode("|",$filedata[$imax]); $dt[11]="$ddt[2]$ddt[3]";$dt[12]="$ddt[4]";$dt[13]="$ddt[8]";$dt[14]="$ddt[5]";}} // if-ы

$text="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$kolvotem|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|";
$file=file("$datadir/wrforum.csv");
$fp=fopen("$datadir/wrforum.csv","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);
for ($ii=0;$ii< sizeof($file);$ii++) { if ($mnumer!=$ii) fputs($fp,$file[$ii]); else fputs($fp,"$text\r\n"); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// удаляем сообщение из 15-и последних [изменён в 2016г.]
$file=file("$datadir/news.csv");
$fp=fopen("$datadir/news.csv","w");
flock ($fp,LOCK_EX);
for ($i=0; $i< sizeof($file); $i++) { $dt=explode("|",$file[$i]); 
if ($dt[2]==$fid and $dt[3]==$id3) unset($file[$i]); }
fputs($fp, implode("",$file));
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

Header("Location: admin.php?id=$fid&page=$page"); exit; } }






// Блок УДАЛЕНИЯ выбранного СООБЩЕНИЯ [изменён в 2016г.]

if (isset($_GET['topicxd'])) { if ($_GET['topicxd'] !="") {
$id=$_GET['id']; $fid=mb_substr($id,0,3); $topicxd=$_GET['topicxd']-1;
if (isset($_GET['page'])) $page=$_GET['page']; else $page="1";
$file=file("$datadir/$id.csv"); $delmsg="";
if (count($file)==1) exit("В ТЕМЕ должно остаться хотябы <B>одно сообщение!</B>");

$fp=fopen("$datadir/$id.csv","w");
flock ($fp,LOCK_EX);
for ($i=0;$i< sizeof($file);$i++) { 
if ($i==$topicxd) {$dt=explode("|",$file[$i]); $username=$dt[8]; $filname=$dt[13]; $delmsg=$file[$i]; unset($file[$i]); } }
fputs($fp, implode("",$file));
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
$topicxd--;

if (is_file("$filedir/$filname")) unlink("$filedir/$filname"); // Удаялем прикреплённый файл

$file=file("$datadir/$id.csv"); //переписываем автора последнего сообщения в теме
$dt=explode("|",$file[count($file)-1]); $avtor=$dt[8]; $time=$dt[4]; $mnumer="-1";

// Блок вычитает 1-цу из кол-ва сообщений на главной
$lines=file("$datadir/wrforum.csv"); $i=count($lines);
// находим по fid номер строки
for ($ii=0;$ii< sizeof($lines);$ii++) {$kdt=explode("|",$lines[$ii]); if ($kdt[2]==$fid and $kdt[11]==$id) $mnumer=$ii;}

if ($mnumer!="-1") { // Если изменения проведены не в той теме, что указана на главной странице 
$dt=explode("|",$lines[$mnumer]);
$dt[7]--; if ($dt[7]<0) $dt[7]="0";
$text="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$id|$time|$avtor|$dt[14]|";
$file=file("$datadir/wrforum.csv");
$fp=fopen("$datadir/wrforum.csv","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);
for ($ii=0;$ii< sizeof($file);$ii++) { if ($mnumer!=$ii) fputs($fp,$file[$ii]); else fputs($fp,"$text\r\n"); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
} // if $mnumer!=-1

// удаляем сообщение из 15-и последних
$ddt=explode("|",$delmsg);
$file=file("$datadir/news.csv");
$fp=fopen("$datadir/news.csv","w");
flock ($fp,LOCK_EX);
for ($i=0; $i< sizeof($file); $i++) { $dt=explode("|",$file[$i]); 
if ($dt[2]==$fid and $dt[3]==$ddt[3] and $dt[4]==$ddt[4]) unset($file[$i]); }
fputs($fp, implode("",$file));
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// -1 к репе и -1 к сообщению юзера!
$ufile="$datadir/userstat.csv"; $ulines=file("$ufile"); $ui=count($ulines)-1; $ulinenew=""; $fileadd=0;
if ($filname!=null) $fileadd=$repaaddfile; // Если юзер удаляет файл, то ему ещё -Х в РЕПУ
$tektime=time();
for ($i=0;$i<=$ui;$i++) {$udt=explode("|",$ulines[$i]);
if ($udt[2]==$username) { // Ищем юзера по имени в файле userstat.csv
$udt[6]--; $udt[7]=$udt[7]-$fileadd-$repaaddmsg;
$ulines[$i]="$udt[0]|$tektime|$udt[2]|$udt[3]|$udt[4]|$udt[5]|$udt[6]|$udt[7]|$udt[8]|$udt[9]|$udt[10]|$udt[11]|\r\n";}
$ulinenew.="$ulines[$i]";}
// Пишем данные в файл
$fp=fopen("$ufile","w");
flock ($fp,LOCK_EX);
fputs($fp,"$ulinenew");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

Header("Location: admin.php?id=$id&page=$page#m$topicxd"); exit; } }





// Добавление ФОРУМА / РАЗДЕЛА +++
if(isset($_GET['event'])) { if ($_GET['event']=="addmainforum") {
$zag=$_POST['zag']; $msg=$_POST['msg']; $id="101";
$ftype=$_POST['ftype']; if ($ftype==TRUE) $ftype="1"; else $ftype="0";
$zag=str_replace("|","I",$zag); $msg=str_replace("|","I",$msg);
if (mb_strlen($zag)<3) exit("$back <B>и введите заголовок! Его длина должна быть более 3 символов!</B>");

// пробегаем по файлу с номерами разделов/топиков - ищем наибольшее и добавляем +1
if (is_file("$datadir/wrforum.csv")) { $lines=file("$datadir/wrforum.csv"); 
$imax=count($lines); $i=0;
do {$dt=explode("|",$lines[$i]); if ($id<$dt[2]) $id=$dt[2]; $i++; } while($i<$imax);
$id++; }
if ($id<101) $id=101; if ($id>999) exit("Номер не может быть более 999");
$rn=10000+$imax*10;
$record="$rn|$rn|$id|$ftype|$zag|$msg|0|0|999|0||||||"; $record=replacer($record);

// создаём пустой файл с рубриками
if ($ftype=="0") { $fp=fopen("$datadir/$id.csv","a+");
flock ($fp,LOCK_EX); fputs($fp,""); fflush ($fp); flock ($fp,LOCK_UN); fclose($fp); }

// запись данных на главную страницу
$fp=fopen("$datadir/wrforum.csv","a+");
flock ($fp,LOCK_EX); fputs($fp,"$record\r\n"); fflush ($fp); flock ($fp,LOCK_UN); fclose($fp);
Header("Location: admin.php"); exit; }






// Блок СОРТИРОВКИ УЧАСТНИКОВ
if(isset($_GET['event'])) { if ($_GET['event']=="sortusers") { $kaksort=$_POST['kaksort']; $lines=null;

// Считываем оба файла в память
$dat="$datadir/user.php"; $dlines=file("$dat"); $di=count($dlines);
$stat="$datadir/userstat.csv"; $slines=file("$stat"); $si=count($slines);

$msguser=1000; // общее кол-во оставленных сообщений - надо считать, пробигаясь по всей БД (в блоке пересчитать статистику)

if ($di!=$si) exit("$back - Необходимо Пересчитать статистику участников!!! Файл стистики повреждён!!!");

for ($i=1;$i<$di;$i++) {
$dt=explode("|",$dlines[$i]);
$st=explode("|",$slines[$i]);

if ($dt[0]!=$st[0]) exit("$back необходимо Пересчитать статистику участников!!! Файл стистики повреждён!!!");

/* временно отключил сбоит 01.2023
// при склеивании на первое место ставим нужный параметр
if ($kaksort==1) {$name=strtolower($dt[2]); $prm="$name";} // 1 - Имени $dt[2]
if ($kaksort==2) {$msg="0".+9999-$st[6]; $prm="$msg";} // 2 - Кол-ву сообщений $st[6]
if ($kaksort==3) {$msg="0".+99-$dt[4]; $prm="$msg";} // 3 - Кол-ву звёзд dt[4]
if ($kaksort==4) {$msg="0".+9000-$st[6]; $prm="$msg";} // 4 - Репутации $st[7]
if ($kaksort==5) {$datereg=$dt[1]; $prm="$datereg";} // 5 - Дате регистрации $dt[1]
if ($kaksort==6) {$aktiv=$st[1]; $prm="$aktiv";} // 6 - Активности $dt[1]/$st[1]
*/
// Склеиваем два файла в одну переменную
$lines[$i]="$prm|$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|$dt[15]|$dt[16]|$st[1]|$st[2]|$st[3]|$st[4]|$st[5]|$st[6]|$st[7]|$st[8]|$st[9]|$st[10]|$st[11]|\r\n";

} // конец FOR

// сортируем массив
setlocale(LC_ALL,'Russian_Russia.65001'); // 11.2018! РАЗРЕШАЕМ РАБОТУ ФУНКЦИЙ, работающих с регистром и с РУССКИМИ БУКВАМИ
//setlocale(LC_ALL,'ru_RU.CP1251'); // ! РАЗРЕШАЕМ РАБОТУ ФУНКЦИЙ, работающих с регистором и с РУССКИМИ БУКВАМИ
sort($lines); // сортируем дни по возрастанию

// разделяем на два массива и по очереди их сохраняем
$dlines="<?die;?> rn|time|name|password|zvezda|email|pol|drdate|delta_gmt|user_skin|icq|url|gorod|interes|sign|avatar|activation|\r\n";
$slines="rn_user|time|name|lock|lock_time|itogotem|itogomsg|repa|kosyaki|nikname|ip|rezerved|\r\n";

for ($i=0;$i<$di-1;$i++) {
$nt=explode("|",$lines[$i]);
$dlines.="$nt[1]|$nt[2]|$nt[3]|$nt[4]|$nt[5]|$nt[6]|$nt[7]|$nt[8]|$nt[9]|$nt[10]|$nt[11]|$nt[12]|$nt[13]|$nt[14]|$nt[15]|$nt[16]|$nt[17]|\r\n";
$slines.="$nt[1]|$nt[18]|$nt[19]|$nt[20]|$nt[21]|$nt[22]|$nt[23]|$nt[24]|$nt[25]|$nt[26]|$nt[27]||\r\n";
}

// запись данных
$fp=fopen("$datadir/user.php","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);
fputs($fp,"$dlines");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

$fp=fopen("$datadir/userstat.csv","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);
fputs($fp,"$slines");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

Header("Location: admin.php?event=userwho"); exit; }}





// Редактирование ФОРУМА / РАЗДЕЛА
if ($_GET['event']=="frdmainforum") {
$fid=$_POST['fid']; $frd=$_POST['frd'];
$ftype=$_POST['ftype']; $zag=$_POST['zag']; if ($zag=="") exit("$back <B>и введите заголовок!</B>");
$zag=str_replace("|","I",$zag);
$rn1=$_POST['rn1']; $rn2=$_POST['rn2']; 
$msg=$_POST['msg']; $msg=str_replace("|","I",$msg); $msg=str_replace("\r\n", "<br>", $msg);
if ($ftype==FALSE) { 
$addmax=$_POST['addmax']; $zvezdmax=$_POST['zvezdmax'];
$kt=$_POST['kt']; $km=$_POST['km'];
$idtemka=$_POST['idtemka'];  $namem=$_POST['namem']; $temka=$_POST['temka']; $timetk=$_POST['timetk'];
$txtmf="$rn1|$rn2|$fid|$ftype|$zag|$msg|$kt|$km|$addmax|$zvezdmax||$idtemka|$timetk|$namem|$temka|"; $txtmf=replacer($txtmf);

} else $txtmf="$rn1|$rn2|$fid|$ftype|$zag|$msg|0|0|0|0|0|||||";

$txtmf=htmlspecialchars($txtmf,ENT_COMPAT,"UTF-8"); $txtmf=stripslashes($txtmf); $txtmf=str_replace("\r\n","<br>",$txtmf);

$file=file("$datadir/wrforum.csv");
$fp=fopen("$datadir/wrforum.csv","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА 

/////////!!!!!!!!!!!!!!!!!!!!!!  if ($frd!=$i) - вариант плохой !!!!!!!! работать только по ключу
// $fid или $rn!!! // БЛОК передалать!!!!!!!!!!!!

for ($i=0;$i< sizeof($file);$i++) { if ($frd!=$i) fputs($fp,$file[$i]); else fputs($fp,"$txtmf\r\n"); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php"); exit; }

















if ($_GET['event']=="rdtema") { // РЕДАКТИРОВАНИЕ ТЕМЫ  [изменён в 2016г.]

$rd=replacer($_POST['rd']); $id=$rd; $id_old=mb_substr($id,3,4); // уникальный номер темы, которую необходимо заменить
$id_new=$id_old; // запоминаем старый id
$fid_new=$_POST['changefid']; $fid=mb_substr($rd,0,3);
if (isset($_GET['page'])) $page=$_GET['page']; else $page="0";
$zag_old=replacer($_POST['oldzag']); // старое название темы (до переименования)
$name=$_POST['name']; $who=$_POST['who']; $email=$_POST['email'];
$zag=replacer($_POST['zag']); if (mb_strlen($zag)<3) exit("$back <B>и введите заголовок ТЕМУ!</B>");
$timetk=$_POST['timetk']; $goto=$_POST['goto'];
if ($_POST['viptema']==TRUE) $viptema="1"; else $viptema="0";
if ($_POST['open_tema']==TRUE) $open_tema="1"; else $open_tema="0";

if ($goto==1) $goto="admin.php?id=$fid_new"; else $goto="admin.php?id=$fid&page=$page";

// БЛОК объединения тем  [изменён в 2016г.]

// I. в $temaplus.csv нужно удалить строку с этой темой
$temaplus=replacer($_POST['temaplus']); // в эту тему присоединяем
$temakuda=replacer($_POST['temakuda']); // в начало или конец темы
if (mb_strlen($temaplus)>1 and is_file("$datadir/$temaplus.csv")) {
if ($temaplus==$id) exit("Ошибка. Не выбрана тема для присоединения, либо выбрана та же самая тема.");
$id_new=mb_substr($temaplus,3,4);
//print"$rd - rd (выбранная тема)<br> $temaplus - temaplus (к какой теме присоединяем)<br>$temakuda - temakuda (в начало - 0, в конец - 1)<br>$fid - fid<br><br>";
$file=file("$datadir/$fid.csv");
$fp=fopen("$datadir/$fid.csv","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<sizeof($file);$i++) { $rdt=explode("|",$file[$i]); 
//print"- $rdt[2]$rdt[3]!=$temaplus -$rdt[5]<br>";
if ("$rdt[2]$rdt[3]"!="$temaplus") fputs($fp,$file[$i]); else $starzag=$rdt[5];}
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// II. соединяем файлы вместе
$record1=file_get_contents("$datadir/$rd.csv"); // Считываем содержимое
$record2=file_get_contents("$datadir/$temaplus.csv"); // Считываем содержимое
if ($temakuda==TRUE) $records="$record2$record1"; else $records="$record1$record2";
$records=str_replace("|$id_new|","|$id_old|",$records);
$records=str_replace("|$starzag|","|$zag|",$records); // Меняем название темы во всём файле
$fp=fopen("$datadir/$rd.csv","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ
fputs($fp,$records);
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
unlink("$datadir/$temaplus.csv"); //удаляем файл

// III. Если есть голосование, одно оставляем, второе удаляем.
$g_name[1]="$datadir/$fid$id_old"."-ip.csv"; $g_name[2]="$datadir/$fid$id_old"."-vote.csv";
$g_name[3]="$datadir/$fid_new$id_new"."-ip.csv"; $g_name[4]="$datadir/$fid_new$id_new"."-vote.csv";
if (is_file($g_name[3]) and !is_file($g_name[1])) {rename($g_name[3],$g_name[1]); rename($g_name[4],$g_name[2]);}
if (is_file($g_name[3])) unlink($g_name[3]); if (is_file($g_name[4])) unlink($g_name[4]); //удаляем файлы голосования
//print"$g_name[1]<br>$g_name[2]<br>$g_name[3]<br>$g_name[4]<br>";
} // КОНЕЦ БЛОКа объединения тем
//print"$rd- rd<br>$id=id<br>$id_old - id_old<br>$id_new - id_new<br>$fid - fid<br>$fid_new - fid_new<br>$zag - zag<br>$zag_old - zag_old<br>$open_tema - open_tema<br><br>";exit;

if ($fid_new==$fid) { // ЕСЛИ ТЕМА ОСТАЁТСЯ В ТОЙЖЕ РУБРИКЕ

$file=file("$datadir/$fid.csv");
$fp=fopen("$datadir/$fid.csv","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА 
for ($i=0;$i<sizeof($file);$i++) { $kt=explode("|",$file[$i]);
if ("|$fid|$id_new|"=="|$kt[2]|$kt[3]|") {$text="$kt[0]|$kt[1]|$kt[2]|$kt[3]|$kt[4]|$zag|$kt[6]|$kt[7]|$kt[8]|$kt[9]|$viptema|$open_tema|$kt[12]|$kt[13]|"; fputs($fp,"$text\r\n");} 
else fputs($fp,$file[$i]); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

} else { // ЕСЛИ У ТЕМЫ МЕНЯЕТСЯ РУБРИКА

// ! Генерируем имя файлу с темой (СТАРЫЙ механизм) и проверяем свободно ли?
if (file_exists("$datadir/$fid_new$id_new.csv")) {do $id_new=mt_rand(1000,9999); while (file_exists("$datadir/$fid_new$id_new.csv"));}

// 1. Удаляем тему в текущем топике
touch("$datadir/$fid.csv");
$file=file("$datadir/$fid.csv");
$fp=fopen("$datadir/$fid.csv","w+");
$kolvotem2=sizeof($file)-1; // кол-во тем для уточнения на главной
flock ($fp,LOCK_EX); 
for ($i=0;$i<sizeof($file);$i++) {$kdt=explode("|",$file[$i]); if ($rd=="$kdt[2]$kdt[3]") {$text=$file[$i]; unset($file[$i]);}}
fputs($fp, implode("",$file));
flock ($fp,LOCK_UN);
fclose($fp);

// 2. Создаём копию темы в новом топике
touch("$datadir/$fid_new.csv");
$file=file("$datadir/$fid_new.csv");
$kolvotem1=sizeof($file)+1; // кол-во тем для уточнения на главной
$fp=fopen("$datadir/$fid_new.csv","a+");
flock ($fp,LOCK_EX);
$text=str_replace("|$fid|$id_old|","|$fid_new|$id_new|",$text); // меняем в файле ID и FID на новые
if ($zag_old!=$zag) $text=str_replace("|$zag_old|","|$zag|",$text); // меняем заголовок
fputs($fp,"$text"); // пишем в конец файла
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// 3. Переименовываем файл с темой на $fid_new$id_new.csv
$records=file_get_contents("$datadir/$id.csv"); // Считываем содержимое
$fp=fopen("$datadir/$id.csv","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ
$records=str_replace("|$fid|$id_old|","|$fid_new|$id_new|",$records); // меняем в файле ID и FID на новые
if ($zag_old!=$zag) $records=str_replace("|$zag_old|","|$zag|",$records); // меняем заголовок
fputs($fp,$records);
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
rename("$datadir/$id.csv", "$datadir/$fid_new$id_new.csv");

// 4. ЗАМЕНЯЕМ СТАРОЕ НАЗВАНИЕ ТЕМЫ НА НОВОЕ в файле с темой
$records=file_get_contents("$datadir/$fid_new$id_new.csv"); // Считываем содержимое
$fp=fopen("$datadir/$fid_new$id_new.csv","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ
if ($zag_old!=$zag) $records=str_replace("|$zag_old|","|$zag|",$records); // меняем заголовок
fputs($fp,$records);
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// 5. Если есть голосование, то меняем имя файлу
$g_name[1]="$datadir/$fid$id_old"."-ip.csv"; $g_name[2]="$datadir/$fid$id_old"."-vote.csv";
$g_name[3]="$datadir/$fid_new$id_new"."-ip.csv"; $g_name[4]="$datadir/$fid_new$id_new"."-vote.csv";
if (is_file($g_name[1]) and is_file($g_name[2])) {rename($g_name[1],$g_name[3]); rename($g_name[2],$g_name[4]);}
//if (is_file($g_name[3])) unlink($g_name[3]); if (is_file($g_name[4])) unlink($g_name[4]); //удаляем файлы голосования

// 6. запускаем пересчёт (копия блока ПЕРЕСЧЁТ revolushion)
$mainlines=file("$datadir/wrforum.csv"); $countmf=count($mainlines)-1; $i="-1";$u=$countmf-1;$k="0";
do {$i++; $dt=explode("|",$mainlines[$i]);
if ($dt[3]==FALSE) { $fid=$dt[2];
if ((is_file("$datadir/$fid.csv")) && (sizeof("$datadir/$fid.csv")>0)) {
$fl=file("$datadir/$fid.csv"); $kolvotem=count($fl); $kolvomsg="0";
for ($itf=0; $itf<$kolvotem; $itf++) {
$forumdt=explode("|",$fl[$itf]);
$idtemp="$forumdt[2]$forumdt[3]"; $tema="$forumdt[5]";
if ((!ctype_digit($idtemp)) or (mb_strlen($idtemp)!=7)) print"";
else {if (is_file("$datadir/$idtemp.csv")) {$msgfile=file("$datadir/$idtemp.csv"); $countmsg=count($msgfile); $kolvomsg=$kolvomsg+$countmsg;} }
} // for
if ($kolvotem=="0") {$dt[11]=""; $dt[12]=""; $dt[13]=""; $dt[14]="";}
$mainlines[$i]="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$kolvotem|$kolvomsg|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|\r\n";
} else { $kolvotem="0"; $kolvomsg="0";
$mainlines[$i]="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$kolvotem|$kolvomsg|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|\r\n";}
} else $mainlines[$i]="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]||0|0|0|0|0|||||\r\n";
} while($i < $countmf);

$file=file("$datadir/wrforum.csv"); // сохраняем точные данные о кол-ве тем и сообщений в файл
$fp=fopen("$datadir/wrforum.csv","w");
flock ($fp,LOCK_EX); 
for ($i=0;$i< sizeof($file);$i++) fputs($fp,$mainlines[$i]);
flock ($fp,LOCK_UN);
fclose($fp);
} // ЕСЛИ У ТЕМЫ МЕНЯЕТСЯ РУБРИКА

// 6. Если в news.csv есть эта тема, то удаляем её нафиг (удаляем тему из 10-КИ последних)
$file=file("$datadir/news.csv");
$fp=fopen("$datadir/news.csv","w");
flock ($fp,LOCK_EX);
for ($i=0; $i< sizeof($file); $i++) {$dt=explode("|",$file[$i]); if ($dt[2]==$fid and $dt[3]==$id_old) unset($file[$i]);}
fputs($fp, implode("",$file));
flock ($fp,LOCK_UN);
fclose($fp);

Header("Location: $goto"); exit; }


} // if $event==rdtema





// ДОБАВЛЕНИЕ ТЕМЫ или ОТВЕТА - ШАГ 1
if(isset($_GET['event'])) {
if (($_GET['event']=="add_tema") or ($_GET['event']=="add_msg")) {

if ($_GET['event']=="add_tema") {$add_tema=TRUE; $add_msg=FALSE;} // Если добавляем ТЕМУ
if ($_GET['event']=="add_msg") {$add_msg=TRUE; $add_tema=FALSE;} // Если добавляем СООБЩЕНИЕ

// ПОЛУЧАЕМ ДАННЫЕ ИЗ ФОРМЫ
if (isset($_POST['who'])) $who=$_POST['who']; else $who="";
if (isset($_POST['email'])) $email=$_POST['email']; else $email="";
if (isset($_POST['page'])) $page=$_POST['page']; else $page="";
if (isset($_POST['maxzd'])) $maxzd=$_POST['maxzd']; else $maxzd="0"; if ($maxzd==null) $maxzd="0";
if ((!ctype_digit($maxzd)) or (mb_strlen($maxzd)>2)) exit("$back. Попытка взлома по звёздам или ошибка в файле статистики");
if (isset($_POST['name'])) $name=$_POST['name']; else $name=""; $name=trim($name);
if (isset($_POST['msg'])) $msg=$_POST['msg']; else $msg="";
if (isset($_POST['zag'])) $zag=$_POST['zag']; else $zag="";
if (isset($_GET['id'])) {$fid=$_GET['id']; $id=$fid;} else {$fid=""; $id="";}
if (mb_strlen($fid)>3) $fid=mb_substr($fid,0,3); if (mb_strlen($id)==7) $id=mb_substr($id,3,4);
if (!ctype_digit($fid) or strlen($fid)!=3 or !is_file("$datadir/$fid.csv")) exit("$back. Попытка взлома через номер рубрики. Рубрика отсутствует. Номер должен содержать только 3 цифры!");

$ip=$_SERVER['REMOTE_ADDR']; $tektime=time(); $pageadd="";
$in=0; $maxzd=$_POST['maxzd']; if (!ctype_digit($maxzd) or strlen($maxzd)>2) exit("<B>$back. Попытка взлома. Хакерам здесь не место.</B>");

// проходим по всем разделам и топикам - ищем запращиваемый. Если wrforum.csv - пуст, то подключаем резервную копию.
$realbase=TRUE; if (is_file("$datadir/wrforum.csv")) $mainlines=file("$datadir/wrforum.csv");
if (!isset($mainlines)) $datasize=0; else $datasize=sizeof($mainlines);
if ($datasize<=0) {if (is_file("$datadir/wrf-copy.csv")) {$realbase="0"; $mainlines=file("$datadir/wrf-copy.csv"); $datasize=sizeof($mainlines);}}
if ($datasize<=0) exit("$back. Проблемы с Базой данных - проведите ремонт базы данных через блок ПЕРЕРАСЧЁТ!");

$realfid=FALSE; $fotodetali=""; $i=count($mainlines);
$lines_tema=file("$datadir/$fid.csv"); $itogotem=count($lines_tema); $j=$itogotem;
do {$i--; $dt=explode("|",$mainlines[$i]);
if ($dt[2]==$fid) { $realfid=$i+1; $i=0;
 if ($dt[3]==TRUE) exit("$back. Данной ветки форума не существует (есть только раздел с таким именем!)"); // присваиваем $realfid - № п/п строки
 if ($itogotem>=$dt[8]) exit("$back. Превышено ограничение на кол-во допустимых тем в данной рубрике! Не более <B>$dt[8]</B> тем!");
}
} while($i>0);
if ($realfid==FALSE) exit("$back. Ошибка с номером рубрики. Рубрика отсутствует в базе!");
$realfid--;

// проверка Логина/Пароля юзера. Может он хакер, тогда облом ему
// Этап 1
if (isset($_POST['userpass'])) $userpass=replacer($_POST['userpass']); else $userpass=""; $realname="";
if (isset($_COOKIE['wrfcookies'])) {
    $wrfc=$_COOKIE['wrfcookies']; $wrfc=htmlspecialchars($wrfc,ENT_COMPAT,"UTF-8"); $wrfc=stripslashes($wrfc);
    $wrfc=explode("|", $wrfc); $wrfname=$wrfc[0]; $wrfpass=$wrfc[1];
} else {$who="0"; $wrfname=null; $wrfpass=null;}

// ШАГ 2
$rn_user=FALSE; if ($who==TRUE) { $who=0;
if ($wrfname!=null & $wrfpass!=null) {
$lines=file("$datadir/user.php"); $i=count($lines);
do {$i--; $rdt=explode("|", $lines[$i]);
   if (mb_strlen($rdt[3])>1) { $realname=strtolower($rdt[2]);
   if (strtolower($wrfname)===$realname & $wrfpass===$rdt[3]) {$rn_user=$rdt[0]; $name=$wrfname; $who="1";} }
} while($i > "1");
if ($rn_user==FALSE) {setcookie("wrfcookies","",time()); exit("Ошибка при работе с КУКИ! <font color=red><B>
Вы не сможете оставить сообщение, попробуйте подать его как гость.</B></font> Ваш логин и пароль не найдены 
в базе данных, попробуйте зайти на форум вновь. Если ошибка повторяется - обратитесь к администратору форума.");}
}}

$ok=FALSE;

if ($j>0) { do {$j--; $tdt=explode("|",$lines_tema[$j]); // Если есть темы в разделе

if ($add_msg==TRUE) { // Если добавляем СООБЩЕНИЕ
if ($tdt[3]==$id) { $ok=TRUE; if ($tdt[11]==FALSE) exit("$back тема закрыта и добавление сообщений запрещено!"); }
} // $add_msg==TRUE

if ($add_tema==TRUE) { // ЕСЛИ добавляем ТЕМУ
$ok=TRUE;
// функция АНТИФЛУД: повторное добавление темы запрещено!
if ($tdt[5]==$zag) exit("$back. Тема с заголовком \"$tdt[5]\" (<a href='index.php?id=$tdt[2]$tdt[3]'>ссылка на тему</a>) уже создана на форуме! Спамить на форуме запрещено!");
} // $add_tema==TRUE

} while($j>0);
} // if $j>0

// БЛОК ГЕНЕРИРУЕТ СЛЕДУЮЩИЙ ПО ПОРЯДКУ НОМЕР ТЕМЫ, начиная просмотр с 1000
if ($add_tema==TRUE) { $id=1000; $id="$fid$id";
$allid=null; $records=file("$datadir/$fid.csv"); $imax=count($records); $i=$imax;
if ($i > 0) { do {$i--; $rd=explode("|",$records[$i]); 
$allid[$i]="$dt[2]$dt[3]"; } while($i>0);
do $id++; while(in_array($id,$allid) or is_file("$datadir/$id.csv"));
} else $id=$fid."1000"; 
if (mb_strlen($id)!=7) exit("$back. Номер темы должен быть числом. Критическая ошибка скрипта или попытка взлома");
$id=mb_substr($id,3,4); // Нам нужен чистый ID из 4-х символов
} // if $add_tema==TRUE


$name=wordwrap($name,30,' ',1); // разрываем длинные строки
$zag=wordwrap($zag,50,' ',1); if (mb_strlen(ltrim($zag))<3) exit("$back ! Ошибка в вводе данных заголовка!");
$name=str_replace("|","I",$name);
$who=str_replace("|","&#124;",$who);
$email=str_replace("|","&#124;",$email);
$zag=str_replace("|","&#124;",$zag);
$msg=str_replace("|","&#124;",$msg);

$vip_tema="0"; // VIP-тема. Если указать 1, то такая тема будет показываться вверху!
$open_tema="1"; // Тема открыта для добавления сообщений? 1/0
$golos="0"; // Есть голосование в теме? 1/0
$rn="10000";

// Если добавляем ТЕМУ
if ($add_tema==TRUE) {$rn_tema="10000"; $text_tema="$rn|$rn_tema|$fid|$id|$tektime|$zag|$who|$rn_user|$name|$email|$vip_tema|$open_tema|$ip||"; $text_tema=replacer($text_tema);}
else {
if (is_file("$datadir/$fid$id.csv")) {$linesn=file("$datadir/$fid$id.csv"); $in=count($linesn)-1;}
$rn_tema=10000+($in+1)*10; } // Генерируем следующий по порядку RN с шагом в 10 единиц!

$text_msg="$rn_tema|$golos|$fid|$id|$tektime|$zag|$who|$rn_user|$name|$email|$vip_tema|$open_tema|$ip|"; // добавление сообщения!
$text_msg=replacer($text_msg); $exd=explode("|",$text_msg); $name=$exd[8]; $zag=$exd[5]; $msg=replacer($msg);

if (!isset($name) || strlen($name) > $maxname || strlen($name) <1) exit("$back Ваше <B>Имя пустое, или превышает $maxname</B> символов!</B></center>");
if (preg_match("/[^(\\w)|(\\x7F-\\xFF)|(\\-)]/",$name)) exit("$back Ваше имя содержит запрещённые символы. Разрешены русские и английские буквы, цифры, подчёркивание и тире.");
if (mb_strlen(ltrim($zag))<3 || strlen($zag) > $maxzag) exit("$back Слишком короткое название темы или <B>название превышает $maxzag</B> символов!</B></center>");
if (mb_strlen(ltrim($msg))<2 || strlen($msg) > $maxmsg) exit("$back Ваше <B>сообщение короткое или превышает $maxmsg</B> символов.</B></center>");
if (!preg_match('/^([0-9a-zA-Z]([-.w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-w]*[0-9a-zA-Z].)+[a-zA-Z]{2,9})$/si',$email) and strlen($email)>30 and $email!="") exit("$back и введите корректный E-mail адрес!</B></center>");

// Здесь в index.php идёт загрузка файла. В админке этого нет.
$text_msg=$text_msg."|$msg||";

if(isset($_GET['topicrd'])) { // Выбрано редактирование СООБЩЕНИЯ
$topicrd=replacer($_GET['topicrd']); // номер ячейки, которую необходимо заменить
$oldmsg=replacer($_POST['oldmsg']);
$oldmsg=str_replace("\r\n","<br>",$oldmsg);
$oldmsg=str_replace("|","&#124;",$oldmsg);
$oldmsg=str_replace(":kovichka:", "'",$oldmsg); // РАЗшифровываем символ '
$msg=replacer($msg);
$file=file("$datadir/$fid$id.csv");
$fs=count($file)-1; $i="-1";
$timetek=time(); $timefile=filemtime("$datadir/$fid$id.csv"); 
$timer=$timetek-$timefile; // узнаем сколько прошло времени (в секундах)
$records=file_get_contents("$datadir/$fid$id.csv");
$records=str_replace("|$oldmsg|","|$msg|",$records); // Делаем замену |старое сообщение| на новое

//print"$records";

$fp=fopen("$datadir/$fid$id.csv","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА 
fputs($fp,$records);
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);


if ($timer<0) {$viptime=strtotime("+2 year"); touch("$datadir/$fid$id.csv",$viptime);}
Header("Location: admin.php?id=$fid$id&page=$page"); exit; }

print"<html><head><link rel='stylesheet' href='$forum_skin' type='text/css'></head><body>";

if ($add_msg==TRUE) { //при ОТВЕТе В ТЕМЕ
$in=$in+2; $pageadd=""; $page=ceil($in/$msg_onpage); if ($page!=1) $pageadd="&page=$page";

// ЗАЩИТА ОТ ФЛУДА: проверяем, давно ли реактивировали тему
$timetek=time(); $timefile=filemtime("$datadir/$fid$id.csv"); $timer=$timetek-$timefile; // сколько секунд назад?
if ($timer<$antiflud and $timer>0) exit("$back тема была активна менее $timer секунд назад. Подождите чуть-чуть.");

// ЕСЛИ введена команда АП!, то меняем дату создания файла и тема самая первая будет
if (strtolower($msg)=="ап!") { touch("$datadir/$fid$id.csv");
print "<script language='Javascript'>function reload() {location=\"index.php?id=$fid$id&$pageadd#m$in\"}; setTimeout('reload()', 1500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
Спасибо, <B>$name</B>, тема реактивирована.<BR><BR>Через несколько секунд Вы будете автоматически перемещены в текущую тему <BR><B>$zag</B>.<BR><BR>
<B><a href='admin.php?id=$fid$id&$pageadd#m$in'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
exit;}
} // $add_msg==TRUE

$razdelname="";
if ($realbase=="1" and $maxzd<1) { // Если подключена рабочая база, а не копия
$lines=file("$datadir/wrforum.csv"); $max=sizeof($lines)-1;
$dt=explode("|", $lines[$realfid]); $dt[7]++; $main_id="$fid$id";
if ($add_tema==TRUE) $dt[6]++;
$txtdat="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$main_id|$tektime|$name|$zag|";
$razdelname=$dt[4];
$fp=fopen("$datadir/wrforum.csv","a+"); // запись данных на главную страницу
flock ($fp,LOCK_EX);
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<=$max;$i++) {if ($i==$realfid) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]);}
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
} // if ($realbase==TRUE)


if ($maxzd<1) { // запись в отдельный файл нового сообщения
$newmessfile="$datadir/news.csv";
if (is_file("$datadir/$fid.csv")) $nlines=count(file("$datadir/$fid.csv")); else $nlines=1;
if (is_file("$datadir/$fid$id.csv")) $nlines2=count(file("$datadir/$fid$id.csv"))+1; else $nlines2=1;
$newlines=file($newmessfile); $ni=count($newlines)-1; $flag=FALSE; $newlineexit="";
$ntext=$text_msg."$nlines|$nlines2|"; $ntext=str_replace("
", "<br>", $ntext);

// Блок проверяет, есть ли уже новое сообщение в этой теме. Если есть - отсеивает. На выходе - массив без этой строки.
if($ni>=15) {unset($newlines[0]); $ni--; $newlines=array_values($newlines); $flag=TRUE;} // Если в файле более 15 сообщений, то старое (первое) удаляем!
for ($i=0;$i<=$ni;$i++) { $ndt=explode("|",$newlines[$i]); if ("$fid$id"!="$ndt[2]$ndt[3]") $newlineexit.="$newlines[$i]"; else $flag=TRUE; }
if ($flag==TRUE) {$newlineexit.=$ntext; $fp=fopen($newmessfile,"w");} else {$newlineexit=$ntext; $fp=fopen($newmessfile,"a+");}
flock ($fp,LOCK_EX);
fputs($fp,"$newlineexit\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
} // if ($maxzd<1)


// БЛОК добавляет +Х к сообщению, репе, кол-ву тем, созданных юзером
if (isset($_COOKIE['wrfcookies']) and ($ok!=FALSE)) {
$ufile="$datadir/userstat.csv"; $ulines=file("$ufile"); $ui=count($ulines)-1; $ulinenew=""; $fileadd=0;
// Если юзер загружает файл - то ему ещё +Х в РЕПУ
if (isset($_FILES['file']['name']) and $repaaddfile!=FALSE) {if (mb_strlen($_FILES['file']['name'])>1) $fileadd=$repaaddfile;}

// Записываем данные в файл REPA.csv
$repa=$fileadd; if ($add_tema==TRUE) {$repa=$repa+$repaaddtem; $pochemu="За добавление темы <a href='index.php?id=$fid' target=_blank>$zag</a>";
} else {$repa=$repa+$repaaddmsg; $pochemu="За добавление сообщения в теме <a href='index.php?id=$fid$id' target=_blank>$zag</a>";}
$today=time();
$fp=fopen("$datadir/repa.csv","a+");
flock ($fp,LOCK_EX);
fputs($fp,"$today|+$repa|$wrfname||$pochemu||||\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

for ($i=0;$i<=$ui;$i++) { $udt=explode("|",$ulines[$i]); // Ищем юзера по имени в файле userstat.csv
if ($udt[2]==$wrfname) { $udt[6]++; $udt[7]=$udt[7]+$fileadd;
if ($add_tema==TRUE) {$udt[5]++; $udt[7]=$udt[7]+$repaaddtem;} else $udt[7]=$udt[7]+$repaaddmsg;
$ulines[$i]="$udt[0]|$tektime|$udt[2]|$udt[3]|$udt[4]|$udt[5]|$udt[6]|$udt[7]|$udt[8]|$udt[9]|$ip|$udt[11]|\r\n";}
$ulinenew.="$ulines[$i]";}
$fp=fopen("$ufile","w"); // Пишем данные в файл
flock ($fp,LOCK_EX);
fputs($fp,"$ulinenew");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);} // ЕСЛИ ЕСТЬ КУКА У ЮЗЕРА И ОН ЕСТЬ в user.php


if ($add_tema==TRUE) { // Добавление ТЕМЫ - запись данных

$fp=fopen("$datadir/$fid.csv","a+"); // Пишем В ТОПИК
flock ($fp,LOCK_EX);
fputs($fp,"$text_tema\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

$fp=fopen("$datadir/$fid$id.csv","a+"); // Пишем В ТЕМУ
flock ($fp,LOCK_EX);
fputs($fp,"$text_msg\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

print "<script language='Javascript'>function reload() {location=\"admin.php?id=$fid$id\"}; setTimeout('reload()', 1500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
Спасибо, <B>$name</B>, за добавление темы!<BR><BR>Через несколько секунд Вы будете автоматически перемещены в созданную тему.<BR><BR>
<B><a href='admin.php?id=$fid$id'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
exit; }

if ($add_msg==TRUE) { //ОТВЕТ В ТЕМЕ - запись данных
$timetek=time(); $timefile=filemtime("$datadir/$fid$id.csv"); 
$timer=$timetek-$timefile; // узнаем сколько прошло времени (в секундах) 
$fp=fopen("$datadir/$fid$id.csv","a+");
flock ($fp,LOCK_EX);
fputs($fp,"$text_msg\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
//////////// ПЕРЕДАЛАТЬ! ВИП темы сейчас по другому работают!//////////////////
if ($timer<0) {$viptime=strtotime("+2 year"); touch("$datadir/$id.csv",$viptime);}

print "<script language='Javascript'>function reload() {location=\"admin.php?id=$fid$id&$pageadd#m$in'\"}; setTimeout('reload()', 1500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
Спасибо, <B>$name</B>, Ваш ответ успешно добавлен.<BR><BR>Через несколько секунд Вы будете автоматически перемещены в текущую тему <BR><B>$zag</B>.<BR><BR>
<B><a href='admin.php?id=$fid$id&$pageadd#m$in'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
exit; }
}











// Сделать копию БД
if ($_GET['event']=="makecopy") {
if (is_file("$datadir/wrforum.csv")) $lines=file("$datadir/wrforum.csv");
if (!isset($lines)) $datasize=0; else $datasize=sizeof($lines);
if ($datasize<=0) exit("Проблемы с Базой данных - база повреждена. Размер=0!");
if (copy("$datadir/wrforum.csv", "$datadir/wrf-copy.csv")) exit("<center><BR>Копия база данных создана.<BR><BR><h3>$back</h3></center>"); else exit("Ошибка создания копии БАЗЫ Данных. Попробуйте создать вручную файл wrf-copy.csv в папке $datadir и выставить ему права на ЗАПИСЬ - 666 или полные права 777 и повторите операцию создания копии!"); }

// Восстановить из копии БД
if ($_GET['event']=="restore") {
if (is_file("$datadir/wrf-copy.csv")) $lines=file("$datadir/wrf-copy.csv");
if (!isset($lines)) $datasize=0; else $datasize=sizeof($lines);
if ($datasize<=0) exit("Проблемы с копией базы данных - она повреждена. Восстановление невозможно!");
if (copy("$datadir/wrf-copy.csv", "$datadir/wrforum.csv")) exit("<center><BR>БД восстановлена из копии.<BR><BR><h3>$back</h3></center>"); else exit("Ошибка восстановления из копии БАЗЫ Данных. Попробуйте вручную файлам wrf-copy.csv и wrforum.csv в папке $datadir выставить права на ЗАПИСЬ - 666 или полные права 777 и повторите операцию восстановления!"); }



// КОНФИГУРИРОВАНИЕ форума, шаг 2: сохранение данных
if ($_GET['event']=="config") {

// обработка полей пароль админа/модератора
if (mb_strlen($_POST['newpassword'])<1 or strlen($_POST['newmoderpass'])<1) exit("$back разрешается длина пароля МИНИМУМ 1 символ!");
if ($_POST['newpassword']!="скрыт") {$pass=trim($_POST['newpassword']); $_POST['password']=md5("$pass+$skey");}
if ($_POST['newmoderpass']!="скрыт") {$pass=trim($_POST['newmoderpass']); $_POST['moderpass']=md5("$pass+$skey");}

// защита от дурака. Дожились, уже в админке защиту приходится ставить...
$fd=stripslashes($_POST['forum_info']); $fd=str_replace("\\","/",$fd); $fd=str_replace("?>","? >",$fd); $fd=str_replace("\"","'",$fd); $forum_info=str_replace("\r\n","<br>",$fd);

mt_srand(time()+(double)microtime()*1000000); $rand_key=mt_rand(1000,9999); // Генерируем случайное число для цифрозащиты

$gmttime=($_POST['delta_gmt'] * 60 * 60); // Считаем смещение

$newsmiles=$_POST['newsmiles'];

$i=count($newsmiles); $smiles="array(";
for($k=0; $k<$i; $k=$k+2) {
  $j=$k+1; $s1=replacer($newsmiles[$k]); $s2=replacer($newsmiles[$j]);
  $smiles.="\"$s1\", \"$s2\""; if ($k!=($i-2)) $smiles.=",";
} $smiles.=");";

$_POST['forum_name']=replacer($_POST['forum_name']);

$rektxt=stripslashes($_POST['reklamatext']);
$rektxt=str_replace("\\","/",$rektxt);
$rektxt=str_replace("?>","? >",$rektxt);
$rektxt=str_replace("\"","'",$rektxt);
$reklamatext=str_replace("\r\n","<br>",$rektxt);
if (mb_strlen($reklamatext)>1000) substr($reklamatext,1000);

$repa=$_POST['repa']; $status=$_POST['status'];
$userrepa="array("; $userstatus="array("; // Собираем статус и рупутацию в массивы
for($k=0; $k<8; $k++) {
$r=replacer($repa[$k]); $s=replacer($status[$k]);
  $userrepa.="\"$r\""; if ($k!=7) $userrepa.=",";
  $userstatus.="\"$s\""; if ($k!=7) $userstatus.=",";
} $userrepa.=");"; $userstatus.=");";

if (!isset($_POST['forum_lock'])) $forum_lock="0"; else $forum_lock="1";
if (!isset($_POST['sendmail'])) $sendmail="0"; else $sendmail="1";
if (!isset($_POST['random_name'])) $random_name="0"; else $random_name="1";
if (!isset($_POST['admin_send'])) $admin_send="0"; else $admin_send="1";
if (!isset($_POST['antimat'])) $antimat="0"; else $antimat="1";
if (!isset($_POST['antispam2k'])) $antispam2k="0"; else $antispam2k="1";
if (!isset($_POST['antispam'])) $antispam="0"; else $antispam="1";
if (!isset($_POST['onlineb'])) $onlineb="0"; else $onlineb="1";
if (!isset($_POST['nosssilki'])) $nosssilki="0"; else $nosssilki="1";
if (!isset($_POST['reklama'])) $reklama="0"; else $reklama="1";
if (!isset($_POST['specblok1'])) $specblok1="0"; else $specblok1="1";

if (!isset($_POST['specblok2'])) $specblok2="0"; else $specblok2="1";
if (!isset($_POST['statistika'])) $statistika="0"; else $statistika="1";
if (!isset($_POST['g_add_tema'])) $g_add_tema="0"; else $g_add_tema="1";
if (!isset($_POST['g_add_msg'])) $g_add_msg="0"; else $g_add_msg="1";
if (!isset($_POST['activation'])) $activation="0"; else $activation="1";
if (!isset($_POST['liteurl'])) $liteurl="0"; else $liteurl="1";
if (!isset($_POST['quikchat'])) $quikchat="0"; else $quikchat="1";
if (!isset($_POST['showsmiles'])) $showsmiles="0"; else $showsmiles="1";
if (!isset($_POST['can_up_file'])) $can_up_file="0"; else $can_up_file="1";
if (!isset($_POST['antiflud'])) $antiflud="0"; else $antiflud="1";
if (!isset($_POST['ipblok'])) $ipblok="0"; else $ipblok="1";

if ($_POST['datadir']!=$datadir and !is_dir($_POST['datadir'])) rename("$datadir", $_POST['datadir']); // Если меняем имя директории, то переименовываем папку с текущей

$configdata="<? // WR-forum Lite v 2.3 UTF-8 //  07.01.2023 г.  //  WR-Script.ru\r\n".
"$"."forum_name=\"".$_POST['forum_name']."\"; // Название форума показывается в теге TITLE и заголовке\r\n".
"$"."forum_info=\"".$forum_info."\"; // Краткое описание форума\r\n".
"$"."adminname=\"".$_POST['adminname']."\"; // Логин администратора\r\n".
"$"."password=\"".$_POST['password']."\"; // Пароль администратора защифрован md5()\r\n".
"$"."modername=\"".$_POST['modername']."\"; // Логин модератора\r\n".
"$"."moderpass=\"".$_POST['moderpass']."\"; // Пароль модератора защифрован md5()\r\n".
"$"."adminemail=\"".$_POST['newadminemail']."\"; // Е-майл администратора\r\n".
"$"."forum_lock=\"".$forum_lock."\"; // ОТКЛЮЧИТЬ добавление тем/сообщений\r\n".
"$"."random_name=\"".$random_name."\"; // При загрузке файла генерировать ему имя случайным образом?\r\n".
"$"."repaaddmsg=\"".$_POST['repaaddmsg']."\"; // Сколько очков репутации добавлять за добавление сообщения?\r\n".
"$"."repaaddtem=\"".$_POST['repaaddtem']."\"; // Сколько очков репутации добавлять за добавлении темы?\r\n".
"$"."repaaddfile=\"".$_POST['repaaddfile']."\"; // Сколько очков репутации добавлять при загрузке файла?\r\n".
"$"."sendmail=\"".$sendmail."\"; // Включить отправку сообщений? 1/0\r\n".
"$"."admin_send=\"".$admin_send."\"; // Мылить админу сообщения о вновь зарегистрированных пользователях? 1/0\r\n".
"$"."statistika=\"".$statistika."\"; // Показывать статистику на главной странице? 1/0\r\n".
"$"."antimat=\"".$antimat."\"; // включить АНТИМАТ да/нет - 1/0\r\n".
"$"."antispam=\"".$antispam."\"; // Задействовать АНТИСПАМ\r\n".
"$"."antispam2k=\"".$antispam2k."\"; // Задействовать АНТИСПАМ вопрос-ответ\r\n".
"$"."antispam2kv=\"".$_POST['antispam2kv']."\"; // вопрос АНТИСПАМА 2\r\n".
"$"."antispam2ko=\"".$_POST['antispam2ko']."\"; // ответ АНТИСПАМА 2\r\n".
"$"."max_key=\"".$_POST['max_key']."\"; // Кол-во символов в коде ЦИФРОЗАЩИТЫ\r\n".
"$"."rand_key=\"".$rand_key."\"; // Случайное число для цифрозащиты\r\n".
"$"."guest_name=\"".$_POST['newguest_name']."\"; // Как называем не зарег-ся пользователей\r\n".
"$"."user_name=\"".$_POST['newuser_name']."\"; // Как называем зарег-ся\r\n".
"$"."g_add_tema=\"".$g_add_tema."\"; // Разрешить гостям создавать темы? 1/0\r\n".
"$"."g_add_msg=\"".$g_add_msg."\"; // Разрешить гостям оставлять сообщения? 1/0\r\n".
"$"."activation=\"".$activation."\"; // Требовать активации через емайл при регистрации? 1/0\r\n".
"$"."maxname=\"".$_POST['newmaxname']."\"; // Максимальное кол-во символов в имени\r\n".
"$"."maxzag=\"".$_POST['maxzag']."\"; // Масимальный кол-во символов в заголовке темы\r\n".
"$"."maxmsg=\"".$_POST['newmaxmsg']."\"; // Максимальное количество символов в сообщении\r\n".
"$"."tem_onpage=\"".$_POST['newtem_onpage']."\"; // Кол-во отображаемых тем на страницу (15)\r\n".
"$"."msg_onpage=\"".$_POST['newmsg_onpage']."\"; // Кол-во отображаемых сообщений на каждой странице (10)\r\n".
"$"."uq=\"".$_POST['uq']."\"; // По сколько человек выводить список участников\r\n".
"$"."onlineb=\"".$onlineb."\"; // Показывать блок кто на форуме\r\n".
"$"."specblok1=\"".$specblok1."\"; // Включить БЛОК 15-и самых обсуждаемых тем?\r\n".
"$"."specblok2=\"".$specblok2."\"; // Включить БЛОК 10 самых активных пользователей?\r\n".
"$"."nosssilki=\"".$nosssilki."\"; // Запретить гостям добавлять сообщения со ссылками?\r\n".
"$"."liteurl=\"".$liteurl."\";// Подсвечивать УРЛ? 1/0\r\n".
"$"."max_f_size=\"".$_POST['max_f_size']."\"; // Максимальный размер аватара в байтах\r\n".
"$"."datadir=\"".$_POST['datadir']."\"; // Папка с данными форума\r\n".
"$"."showsmiles=\"".$showsmiles."\";// Включить/отключить графические смайлы\r\n".
"$"."can_up_file=\"".$can_up_file."\"; // Разрешить загрузку фото 0 - нет, 1 - только зарегистрированным\r\n".
"$"."filedir=\"".$_POST['filedir']."\"; // Каталог куда будет закачан файл\r\n".
"$"."max_upfile_size=\"".$_POST['max_upfile_size']."\"; // максимальный размер файла в байтах\r\n".
"$"."forum_skin=\"".$_POST['forum_skin']."\"; // Текущий скин форума\r\n".
"$"."back=\"<html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'><meta http-equiv='Content-Language' content='ru'></head><body><center>Вернитесь <a href='javascript:history.back(1)'><B>назад</B></a>\"; // Удобная строка\r\n".
"$"."smiles=".$smiles."// СМАЙЛИКИ (имя файла, символ для вставки, -//-)\r\n".
"$"."delta_gmt=\"".$_POST['delta_gmt']."\"; // Учитываем кол-во часов со смещением относительно хостинга по формуле: ЧЧ * 3600\r\n".
"$"."date=date(\"d.m.Y\", time()+$gmttime); // число.месяц.год\r\n".
"$"."time=date(\"H:i:s\",time()+$gmttime); // часы:минуты:секунды\r\n".
"$"."chatmaxmsg=\"".$_POST['chatmaxmsg']."\"; // Макс. символов в сообщении чата\r\n".
"$"."chatrefresh=\"".$_POST['chatrefresh']."\"; // Частота обновления чата\r\n".
"$"."chatmsg_onpage=\"".$_POST['chatmsg_onpage']."\"; // Количество видимых сообщений чата\r\n".
"$"."chatinput=\"".$_POST['chatinput']."\"; // Длина строки ввода сообщения чата\r\n".
"$"."chatframesize=\"".$_POST['chatframesize']."\"; // Длина фрейма чата\r\n".
"$"."antiflud=\"".$antiflud."\"; // Задействовать АНТИФЛУД\r\n".
"$"."fludtime=\"".$_POST['fludtime']."\"; // Антифлуд-время в секундах\r\n".
"$"."ipblok=\"".$ipblok."\"; // Запретить голосовать более раза с одного IP 0/1\r\n".
"$"."userstatus=".$userstatus."// Звания при накоплении баллов репутации\r\n".
"$"."userrepa=".$userrepa."// Баллы репутации необходимые для смены статуса\r\n".
"$"."quikchat=\"".$quikchat."\";// Показывать БЛОК 'Мини-чат на главной'\r\n".
"$"."reklama=\"".$reklama."\"; // Показывать блок рекламы и объявлений 0/1\r\n".
"$"."reklamatitle=\"".$_POST['reklamatitle']."\"; // Заголовок блока рекламы и объявлений\r\n".
"$"."reklamatext=\"".$reklamatext."\"; // Текст блока\r\n?>";

$file=file("data/config.php");
$fp=fopen("data/config.php","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА 
fputs($fp,$configdata);
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
Header("Location: admin.php?event=configure"); exit;}


} // конец if isset($event)




// шапка для всех страниц форума

if (isset($_COOKIE['wrfcookies'])) {
$wrfc=$_COOKIE['wrfcookies']; $wrfc=explode("|", $wrfc);
$wrfname=$wrfc[0];$wrfpass=$wrfc[1];$wrftime1=$wrfc[2];$wrftime2=$wrfc[3];
if (time()>($wrftime1+50)) { $tektime=time();
$wrfcookies="$wrfc[0]|$wrfc[1]|$tektime|$wrftime1|";
setcookie("wrfcookies", $wrfcookies, time()+1728000);
$wrfc=$_COOKIE['wrfcookies']; $wrfc=explode("|", $wrfc);
$wrfname=$wrfc[0];$wrfpass=$wrfc[1];$wrftime1=$wrfc[2];$wrftime2=$wrfc[3]; }}

// БЛОК подключает копию главного файла при повреждении
if (is_file("$datadir/wrforum.csv")) $mainlines=file("$datadir/wrforum.csv"); $imax=count($mainlines); $i=$imax;
if (!isset($mainlines)) $datasize=0; else $datasize=sizeof($mainlines);
if ($datasize<=0) {if (is_file("$datadir/wrf-copy.csv")) {$mainlines=file("$datadir/wrf-copy.csv"); $datasize=sizeof($mainlines);}}
if ($datasize<=0) exit("<center><b>Файл РУБРИК отсутствует! Зайдите в <a href='admin.php'>админку</a> и создайте рубрики!</b>");


$error=FALSE; $frname=null; $frtname=""; $rfid="";

// ДЛЯ ссылки типа razdel=
if (isset($_GET['razdel'])) {
do {$i--; $dt=explode("|", $mainlines[$i]);
if ($dt[0]==$_GET['razdel']) {$rfid=$i; $frname="$dt[2] »";}
} while($i >0);
$i=$imax;}

if (isset($_GET['id'])) { // Блок выводит в статусной строке: ТЕМА » РАЗДЕЛ » ФОРУМ
$id=$_GET['id'];
if (mb_strlen($id)==3 and !is_file("$datadir/$id.csv")) $error="ый Вами раздел";
if (mb_strlen($id)==7 and !is_file("$datadir/$id.csv")) $error="ая Вами тема";
if (!ctype_digit($id)) $error="ая Вами тема или раздел";

if(mb_strlen($id)>3) {$fid=mb_substr($id,0,3); $id=mb_substr($id,3,4);} else $fid=$id;

// проходим по всем разделам и топикам - ищем запрашиваемый
do {$i--; $dt=explode("|", $mainlines[$i]);
if ($dt[2]==$fid) { $frname="$dt[4] »";
if (isset($dt[11])) { if($dt[11]>0) $maxtem=$dt[11]; else $maxtem="999";}}
} while($i >0);

// Блок считывает название темы для отображения в шапке форума
if (mb_strlen($id)>3 and is_file("$datadir/$fid.csv")) {
$lines=file("$datadir/$fid.csv"); $imax=count($lines); $i=$imax;
do {$i--; $dt=explode("|",$lines[$i]);
if($dt[2]==$fid) $frtname="$dt[5] »";
} while ($i>0); }


if ($error==TRUE) { // ЗАПРЕЩАЕМ ИНДЕКСАЦИЮ страниц с цитированием / УДАЛЁННЫЕ РАЗДЕЛЫ / ТЕМЫ!
$topurl="data/top.html";
ob_start(); include $topurl; $topurl=ob_get_contents(); ob_end_clean();
$topurl=str_replace("<meta name=\"Robots\" content=\"index,follow\">",'<meta name="Robots" content="noindex,follow">',$topurl);
print"$topurl";
if (mb_strlen($error)>1) exit("</td></tr></table><div align=center><br>Извините, но запрашиваем$error отсутствует.<br>
Рекомендую перейти на главную страницу форума по <a href='$forum_url'>этой ссылке</a>,<br>
и найти интересующую Вас тему.<br></div></td></tr></table></td></tr></table></td></tr></table></body></html>"); }

// здесь проверяем СУЩЕСТВУЕТ ЛИ СТРАНИЦА, на которую пришёл юзер
if (mb_strlen($id)==3) { $lines=file("$datadir/$id.csv"); $imax=count($lines);
if (isset($_GET['page'])) $page=$_GET['page']; else $page=1;
$maxikpage=ceil($imax/$msg_onpage); }

} // if (isset($_GET['id']))





 



// печатаем ВЕРХУШКУ форума если есть файл
?>
<html>
<head>
<title>Админка » <?print"$frtname $frname $forum_name";?></title>
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Language" content="ru">
<meta name="description" content="<? print"$forum_info - $forum_name";?>">
<meta http-equiv="keywords" content="<? print"$frtname $frname $forum_name";?>">
<meta name="Resource-type" content="document">
<meta name="document-state" content="dynamic">
<meta name="Robots" content="index,follow">
<link rel="stylesheet" href="<?=$forum_skin?>" type="text/css">
<style>
// Стили для НОВЫХ КНОПОК-ЧЕКБОКСОВ [изменён в 2016г.]
div{clear: both; margin:0 0px;}
input.key:empty {margin-left:-2999px;}
input.key:empty ~ label {position:relative; float:left; line-height:1.6em; text-indent:4em; margin:0.2em 0; cursor:pointer;}

input.key:empty ~ label:before {content:'\2718'; text-indent:2.4em; color:#900; position:absolute; display:block;
top:0; bottom:0; left:0; width:3.6em; background-color:#c33; border-radius:0.3em; box-shadow:inset 0 0.2em 0 rgba(0,0,0,0.3);}

input.key:empty ~ label:after {position: absolute; display: block; top: 0; bottom: 0; left: 0; content: ' ';
width: 3.6em; background-color: #c33; border-radius: 0.3em; box-shadow: inset 0 0.2em 0 rgba(0,0,0,0.3);}

input.key:empty ~ label:after { width: 1.4em; top: 0.1em; bottom: 0.1em; margin-left: 0.1em;
background-color: #fff; border-radius: 0.15em; box-shadow: inset 0 -0.2em 0 rgba(0,0,0,0.2);}

input.key:checked ~ label:before {background-color:#393; content:'\2714'; text-indent:0.5em; color:#6f6;}
input.key:checked ~ label:after {margin-left:2.1em;}
</style>
<SCRIPT language=JavaScript>
<!--
function x () {return;}
function FocusText() {
 document.REPLIER.msg.focus();
 document.REPLIER.msg.select();
 return true; }
function DoSmilie(addSmilie) {
 var revisedMessage;
 var currentMessage=document.REPLIER.msg.value;
 revisedMessage=currentMessage+addSmilie;
 document.REPLIER.msg.value=revisedMessage;
 document.REPLIER.msg.focus();
 return;
}
function DoPrompt(action) { var revisedMessage; var currentMessage=document.REPLIER.msg.value; }
//-->
</SCRIPT>
</head>

<body bgcolor="#E5E5E5" text="#000000" link="#006699" vlink="#5493B4" bottomMargin=0 leftMargin=0 topMargin=0 rightMargin=0 marginheight="0" marginwidth="0">

<table width=100% cellspacing=0 cellpadding=10 align=center><tr><td class=bodyline>
<table width=100% cellspacing=0 cellpadding=0>
<tr>
<td><a href="index.php">Форум WR-Forum Lite - версия</a>
<br><div align=center>Вы вошли как <B><font color=red>Администратор</font></B></td>
<td align="center" valign="middle"><span class="maintitle"><a href=admin.php><h3><font color=red>Панель администрирования<br></font> <?=$forum_name?></h3></a></span>
<table width=80%><TR><TD align=center><span class="gen"><?=$forum_info?><br><br></span></TD></TR></TABLE>
</td></tr></table>

<style>
#nav8, #nav8 ul, #nav8 li {margin: 0;padding: 0;border: 0;}
#nav8, #nav8 ul {background: rgb(210,210,210);}
#nav8 {border-spacing: 0 0;position: relative;z-index: 50;width: 100%;}
#nav8 ul {position: absolute;display: none;}
#nav8 td:hover ul {display: block;}
#nav8 ul li {list-style: none;}
#nav8 .right {direction: rtl;}
#nav8 .right ul {right: 0;}
#nav8 .right li {direction: ltr;}
#nav8 a {display: block;padding: 7px 0;text-align: center;text-decoration: none;}
#nav8 ul a {padding: 7px 15px;text-align: left;}
#nav8 td:hover, #nav8 li:hover {background: rgb(96,145,172);}
#nav8 td {text-align: center;}
</style>

<table id="nav8" cellspacing="0"><tr>

<td><B>Работа с данными</B><ul>
<li><a href="admin.php?event=makecopy">Сделать резервную копию (главная стр.)</a>
<li><a href='admin.php?event=restore' class=mainmenu onclick="return confirm('Если Вы уже делали копию ранее, а сейчас видите проблемы со скриптом, то восстановить можно. Заменить главный файл форума из копии (сделанной ВАМИ РАНЕЕ)? Уверены?')">Восстановить из копии (главн. стр.)</a>
<li><a href="admin.php?event=revolushion">Пересчитать кол-во тем и сообщений</a>
<li><a href="admin.php?event=seebasa">Просмотр БД</a>
</ul></td>

<td><a href="admin.php?event=userwho">Участники</a><ul>
<li><a href="admin.php?newstatistik">Пересчитать статистику (запуск при поврежении БД)</a>
<li><a href="admin.php?event=massmail">Автоматическая рассылка писем</a>
<li><a href="?delalluser=yes" title="УДАЛИТЬ" onclick="return confirm('Будут удалены ВСЕ НЕ АКТИВИРОВАННЫЕ УЧЁТНЫЕ ЗАПИСИ! Удалить? Уверены?')">Удалить НЕ АКТИВИРОВАННЫХ</a>
</ul></td>

<td><b>Безопасность</b><ul>
<li><a href="admin.php?event=blockip">IP-Блокировка</a>
</ul></td>

<td><a href="admin.php?event=configure">Настройки</a><ul>
</ul></td>

<td><b>Помощь</b><ul>
<li><a href="https://www.wr-script.ru/wiki/wr-forum.php">История версий, планы обновлений</a>
<li><a href="https://www.wr-script.ru/forum/README.html">Инструкция по настройке форума</a>
<li><a href="https://www.wr-script.ru/forum/index.php?id=102">Форум: Вопросы и ответы о форуме 390+</a>
<li><a href="https://www.wr-script.ru/forum/index.php?id=1021081">Форум: проблемы и вопросы разработчику</a>
<li><a href="https://www.wr-script.ru/info/vosstanovlenie_dostuka_k_adminke.php">Восстановление доступа к админпанели</a>
<li><a href="https://www.wr-script.ru/info/kak-perekodirovat-win1251-v-utf-8.php">Разработчикам: Перекодировать в UTF-8</a>
<li><a href="https://www.wr-script.ru/info/wr-forum-2_strukture.php">Разработчикам: структура БД форума</a>
<li><a href="https://www.wr-script.ru/info/kak_sdelat_noviy_skin_dlya_wr-forum.php">Разработчикам: как сделать скин</a>
</ul></td>

<td><a href="admin.php?event=clearcooke">ВЫХОД</a><ul>
</td></tr></table>


<table cellspacing=0 cellpadding=2><tr><td align=center valign=middle>


<? 
if (is_file("$datadir/wrf-copy.csv")) {
if (count(file("$datadir/wrf-copy.csv"))<1) $a2="<font color=red size=+1>НО файл копии ПУСТ! Срочно пересоздайте!</font><br> (смотрите права доступа, если эо сообщение повторяется)"; else $a2="";
$a1=round((time()-filemtime("$datadir/wrf-copy.csv"))/86400); if ($a1<1) $a1="сегодня</font>, это есть гуд!"; else $a1.="</font> дней назад.";
$add="<br><B><center>Копия была создана <font color=red size=+1>".$a1." $a2</B>"; if ($a1>90) $add.="Да уж, больше 3-х месяцев ниодной копии не делали. Испытываете судьбу? Делайте БЕГОМ!"; if ($a1>10) $add.="Вы что! СРОЧНО делайте копию! А вдруг сбой? Как будете данные восстанавливать?!!"; if ($a1>5) $add.="Пора делать копию. Берегите свои нервы. Чтобы быть спокойным при сбое ;-)"; $add.="</center>";} else $add="";

// читаем файл с именами пользователей в память чтобы показать последнего
$userlines=file("$datadir/user.php");
$ui=count($userlines)-1;
$tdt=explode("|",$userlines[$ui]);

print"</td></tr></table>$add
<table width=100% cellspacing=0 cellpadding=2><tr><td><span class=gensmall>Сегодня: $date г. - $time</td></tr></table>";











// выводим ГЛАВНУЮ СТРАНИЦУ ФОРУМА
if (!isset($_GET['event'])) {

if (!isset($_GET['id'])) {
echo'
<table width=100% cellpadding=2 cellspacing=1 class=forumline>
<tr><th width=60% colspan=2 class=thCornerL height=25 nowrap=nowrap>Форумы</th>
<th width=10% class=thTop nowrap=nowrap>Тем/Макс.</th>
<th width=7% class=thCornerR nowrap=nowrap>Ответов</th>
<th width=28% class=thCornerR nowrap=nowrap>Обновление</th></tr>';

// Выводим msg_onpage сообщений на текущей странице

$addform="<form action='admin.php?event=addmainforum' method=post name=REPLIER1><table width=100% cellpadding=4 cellspacing=1 class=forumline><tr> <td class=catHead colspan=2 height=28><span class=cattitle>Добавление Раздела / Форума</span></td></tr><tr><td class=row1 align=right><b><span class=gensmall>Тип добавляемого пункта</span></b></td><td class=row1><input type=radio name=ftype value='razdel'> Раздел &nbsp;&nbsp;<input type=radio name=ftype value=''checked> Форум</td></tr><tr><td class=row1 align=right valign=top><span class=gensmall><B>Заголовок</B></td><td class=row1 align=left valign=middle><input type=text class=post value='' name=zag size=70></td></tr><tr><td class=row1 align=right valign=top><span class=gensmall>Описание</td><td class=row1 align=left valign=middle><textarea cols=100 rows=3 size=700 class=post name=msg></textarea></td></tr><tr><td class=row1 colspan=2><center><input type=submit class=mainoption value='     Добавить     '></td></span></tr></table></form>";

if (!is_file("$datadir/wrforum.csv")) exit("<h3>Восстановите БД из копии. Файл wrforum.csv несуществует или добавьте форум/раздел.</h3>$addform"); 

$lines=file("$datadir/wrforum.csv"); $datasize=sizeof($lines);

if ($datasize==0) exit("<h3>Файл wrforum.csv пуст - добавьте форум или раздел.</h3>$addform");

$i=count($lines); $n="0"; $a1="-1"; $u=$i-1; $fid="0"; $itogotem="0"; $itogomsg="0";

do {$a1++; $dt=explode("|",$lines[$a1]);
$fid=$dt[2];

echo'<tr height=30><td class=row1>';

if ($ktotut==1) { // только админ может управлять разделами
print"<table><TR>
<td width=10 bgcolor=#A6D2FF><B><a href='admin.php?movetopic=$a1&where=1' title='переместить ВВЕРХ'>&#9650;</a></B></td>
<td width=10 bgcolor=#DEB369><B><a href='admin.php?movetopic=$a1&where=0' title='переместить ВНИЗ'>&#9660;</a></B></td>
<td width=10 bgcolor=#22FF44><B><a href='admin.php?frd=$a1' title='РЕДАКТИРОВАТЬ'>.P.</a></B></td>
<td width=10 bgcolor=#FF2244><B><a href='admin.php?fxd=$dt[2]' title='УДАЛИТЬ' onclick=\"return confirm('Будет удалён раздел и ВСЕ ТЕМЫ В НЁМ! Удалить? Уверены?')\" >.X.</a></B></td>
</tr></table>"; }

echo'</td>';

// определяем тип: форум или заголовок
if ($dt[3]==TRUE) print "<td class=catLeft colspan=1><span class=cattitle><center>$dt[4]</td><td class=rowpic colspan=4 align=right>&nbsp;</td></tr>";

else {

$newtema=""; $page=1; $msgsize=""; $pageadd="";
if (is_file("$datadir/$dt[11].csv")) { $msgsize=sizeof(file("$datadir/$dt[11].csv")); // считаем кол-во страниц в файле
if (mb_strlen($dt[13])>20) {$dt[13]=mb_substr($dt[13],0,20); $dt[13].="..";}
if (mb_strlen($dt[14])>28) {$dt[14]=mb_substr($dt[14],0,28); $dt[14].="..";}
if ($msgsize>$msg_onpage) $page=ceil($msgsize/$msg_onpage); else $page=1;
if ($page!=1) $pageadd="&page=$page";
if (mb_strlen($dt[12])<5) $dt[12]=time();
if (date("d.m.Y",$dt[12])==$date)  $dt[12]="сегодня в ".csve("H:m",$dt[12]); else $dt[12]=date("d.m.y - H:m",$dt[12]);
$newtema="<span class=gensmall>тема: <a href=\"admin.php?id=$dt[11]$pageadd#m$msgsize\" title='$dt[14]'>$dt[14]</a> <BR>автор: <B>$dt[13]</B><BR>дата: <B>$dt[12]</B></span>";
} // is_file...$dt[11]

if ($dt[9]>=1) {$maxzvezd="*Доступна участникам, имеющим <font color=red><B>$dt[9]</B> звезд";
if ($dt[9]==1) $maxzvezd.="у"; if ($dt[9]>=2 and $dt[9]<=4) $maxzvezd.="ы";
$maxzvezd.=" минимум</font>";} else $maxzvezd=null;

print "
<td width=60% class=row1 valign=middle><span class=forumlink><a href=\"admin.php?id=$fid\">$dt[4]</a> $maxzvezd<BR></span><small>$dt[5]</small></td>
<td width=7% class=row2 align=center><small>$dt[6] / $dt[8]</small></td>
<td width=7% class=row2 align=center valign=middle><small>$dt[7]</small></td>
<td width=28% class=row2 align=left>$newtema</td></tr>\r\r\n";

$itogotem=$itogotem+$dt[6]; $itogomsg=$itogomsg+$dt[7]; }
} while($a1 < $u);
echo'</table><BR>';

// Выбрано редактирование ФОРУМА
if (isset($_GET['frd'])) { if ($_GET['frd']!="") { $frd=$_GET['frd'];
$lines=file("$datadir/wrforum.csv");
$dt=explode("|",$lines[$frd]);
if ($dt[8]>0) $addmax=$dt[8]; else $addmax="999"; if ($dt[9]<=0) $dt[9]="0";
$dt[5]=str_replace("<br>","\r\n",$dt[5]);

print "<form action='admin.php?event=frdmainforum' method=post name=REPLIER1><table width=100% cellpadding=4 cellspacing=1 class=forumline><tr> <td class=catHead colspan=2 height=28><span class=cattitle>Редактирование Раздела / Форума</span></td></tr>
<tr><td class=row1 align=right>Тип редактируемого пункта</td><td class=row1><input type=hidden name=fid value='$dt[2]'>
<input type=hidden name=rn1 value='$dt[0]'><input type=hidden name=rn2 value='$dt[1]'>";
if ($dt[3]==TRUE) print "<input type=hidden name=ftype value='1'>Раздел</td></tr><tr><td class=row1 align=right valign=top><span class=gensmall><B>Заголовок</B></td><td class=row1 align=left valign=middle><input type=text value='$dt[4]' name=zag size=70><input type=hidden name=msg value=''></td></tr>";
else {print "
<input type=hidden name=ftype value='0'>Форум</td></tr><tr><td class=row1 align=right valign=top><B>Заголовок</B></td><td class=row1 align=left valign=middle><input class=post type=text value='$dt[4]' name=zag size=70></td></tr>
<tr><td class=row1 align=right valign=top>Описание</td><td class=row1 align=left valign=middle><textarea cols=80 rows=6 class=post size=500 name=msg>$dt[5]</textarea>
<input type=hidden name=kt value='$dt[6]'>
<input type=hidden name=km value='$dt[7]'>
<input type=hidden name=idtemka value='$dt[11]'>
<input type=hidden name=timetk value='$dt[12]'>
<input type=hidden name=namem value='$dt[13]'>
<input type=hidden name=temka value='$dt[14]'>
</td></tr>
<TR><TD align=right class=row1>Максимальное кол-во тем в форуме</TD><TD class=row1><input type=text class=post name=addmax value='$addmax' maxlength=3></TD></TR>
<input type=hidden name=zvezdmax value='$dt[9]'>
<TR><TD align=right class=row1>Заблокировать по звёздам</TD><TD class=row1><input type=text class=post size=5 maxlength=1 name=zvezdmax value='$dt[9]'>
Если ввести число от 1 до 9, то ТОЛЬКО участники с указанным кол-вом звёзд могут обсуждать эту ветку форума.</TD></TR>";}

print"<tr><td colspan=2 class=row1><input type=hidden name=frd value='$frd'><SCRIPT language=JavaScript>document.REPLIER1.zag.focus();</SCRIPT><center><input type=submit class=mainoption value='     Изменить     '></td></span></tr></table></form><BR>";
} } // Конец редактирования ФОРУМА

else { if ($ktotut==1) print "$addform"; }


if ($statistika==TRUE) {

if ($g_add_tema==TRUE) $c1="разрешено"; else $c1="запрещено";
if ($g_add_msg==TRUE) $c2="разрешено"; else $c2="запрещено";
$codename=urlencode($tdt[2]);
print"<table width=100% cellpadding=3 cellspacing=1 class=forumline><tr><td class=catHead colspan=2 height=28><span class=cattitle>Статистика</span></td></tr><tr>
<td class=row1 align=center valign=middle rowspan=2>.</td>
<td class=row1 align=left width=95%><span class=gensmall>Сообщений: <b>$itogomsg</b><br>Тем: <b>$itogotem</b><br>Всего зарегистрировано участников: <b><a href=\"admin.php?event=userwho\">$ui</a></b><br>Последним зарегистрировался: 
<a href=\"admin.php?event=profile&pname=$codename\">$tdt[2]</a><BR>
Гостям <B>$c1</B> создавать темы и <B>$c2</B> отвечать в темах<BR>
</span></td></tr></table>"; 

// СТАТИСТИКА -= Последние сообщения с форума =-
if (is_file("$datadir/news.csv")) { $newmessfile="$datadir/news.csv";
$lines=file($newmessfile); $i=count($lines); //if ($i>10) $i=10; (РАСКОМЕНТИРУЙ - ВОТ ГДЕ СИЛА!!! ;-))
if ($i>=1) {
print"<br><table width=100% cellpadding=0 cellspacing=1 class=forumline>
<tr><td class=catHead colspan=2 height=28><span class=cattitle>Последние сообщения
<a href='admin.php?lxd=9999999' title='УДАЛИТЬ' onclick=\"return confirm('Будут удалены все последние сообщения! Удалить? Уверены?')\" >.X.</a></B>
</span></td>
</tr>
<tr><td rowspan=20 class=row1 align=center valign=middle>.</td><td class=row1>";

$mmax=count($mainlines);
$a1=$i-1;$u="-1"; // выводим данные по возрастанию или убыванию
do {$dt=explode("|", $lines[$a1]); $a1--;

if (isset($dt[1])) { // Если строчка потерялась в скрипте (пустая строка) - то просто её НЕ выводим
$msg=htmlspecialchars($dt[14],ENT_COMPAT,"UTF-8");
$msg=str_replace('[b]'," ",$msg); $msg=str_replace('[/b]'," ",$msg);
$msg=str_replace('[RB]'," ",$msg); $msg=str_replace('[/RB]'," ",$msg);
$msg=str_replace('[Code]'," ",$msg); $msg=str_replace('[/Code]'," ",$msg);
$msg=str_replace('[Quote]'," ",$msg); $msg=str_replace('[/Quote]'," ",$msg);
$msg=str_replace('[img]'," картинка: ",$msg); $msg=str_replace('[/img]'," ",$msg);
$msg=str_replace("<br>","\r\n", $msg);
$msg=str_replace("'","`",$msg);
$msg=str_replace('&amp;lt;br&amp;gt;'," \r\r\n", $msg);
$msg=str_replace('&lt;br&gt;'," \r\r\n", $msg);

$k=$mmax; $mainr=""; // Ищем название рубрики, как находим - присваимваем значение и выходим из цикла!
do {$k--; $mdt=explode("|",$mainlines[$k]);
if ($mdt[2]==$dt[2]) {$mainr="<a href='$forum_url/admin.php?id=$mdt[2]' class=nav>$mdt[4]</a>"; $k=0;}
} while($k>0);

if (date("d.m.Y",$dt[4])==$date)  $dt[4]="сегодня в ".csve("H:i",$dt[4]); else $dt[4]=date("d.m.y - H:i",$dt[4]);

if ($dt[17]>$msg_onpage) $page=ceil($dt[17]/$msg_onpage); else $page=1; // Считаем страницу
if ($page!=1) $pageadd="&page=$page"; else $pageadd="";

if ($dt[6]==TRUE) {$codename=urlencode($dt[8]); $name="<B><a href='admin.php?event=profile&pname=$codename'>$dt[8]</a></B>";} else $name="гость $dt[8]";
print"
<table><tr><td width=10 bgcolor=#FF2244><B><a href='admin.php?lxd=$dt[2]$dt[3]' title='УДАЛИТЬ' onclick=\"return confirm('Будет удалена ссылка на сообщение! Удалить? Уверены?')\" >.X.</a></B></td><TD>
<span class=gensmall>$dt[4]: 
<strong>$mainr</strong> » <B><a href='admin.php?id=$dt[2]$dt[3]$pageadd#m$dt[17]' title='$msg \r\n\r\n Отправлено $dt[4]'>$dt[5]</a></B> - $name.</td></tr></table>";
} // если строчка потерялась
$a11=$u; $u11=$a1;
} while($a11 < $u11);

echo'</span></td></tr></table>';
}

} // Конец блока последних сообщений
}

} // конец главной страницы





// Общая переменная!
if (isset($_GET['id'])) {

if (mb_strlen($_GET['id'])==3) { $fid=replacer($_GET['id']); $id=replacer($_GET['id']); }
else $id=replacer($_GET['id']);



if (mb_strlen($id)==3) { // выводим страницу С ТЕМАМИ выбранной РУБРИКИ

$maxzd=null; // Уточняем статус по кол-ву ЗВЁЗД в теме
$imax=count($mainlines);
do {$imax--; $ddt=explode("|", $mainlines[$imax]); if ($ddt[0]==$fid) $maxzd=$ddt[12]; } while($imax>"0");
if (!ctype_digit($maxzd)) $maxzd=0;

print "
<table><tr><td><span class=nav>&nbsp;&nbsp;&nbsp;<a href=admin.php class=nav>$forum_name</a> » <a href=admin.php?id=$fid class=nav>$frname</a></span></td></tr></table>
<table width=100% cellpadding=2 cellspacing=1 class=forumline><tr>
<th width=3% class=thCornerL height=25 nowrap=nowrap>X/P</th>
<th width=57% class=thCornerL height=25 nowrap=nowrap>Тема</th>
<th width=10% class=thTop nowrap=nowrap>Cообщений</th>
<th width=12% class=thCornerR nowrap=nowrap>Автор</th>
<th width=18% class=thCornerR nowrap=nowrap>Обновления</th></tr>";

$addbutton="<table width=100%><tr><td align=left valign=middle><span class=nav><a href=\"admin.php?id=$fid&newtema=add\"><img src='$forum_skin/newthread.gif' border=0></a>&nbsp;</span></td>";


// определяем есть ли информация в файле с данными
if (is_file("$datadir/$fid.csv"))
{
$msglines=file("$datadir/$fid.csv");
if (count($msglines)>0) {

if (count($msglines)>$maxtem-1) $addbutton="<table width=100%><TR><TD>Количество допустимых тем в рубрике исчерпано.";

// Выводим msg_onpage сообщений на текущей странице
$lines=file("$datadir/$fid.csv");
$i=count($lines); $maxi=$i; $n="0";

////////////////// Механизм сортировки некорректный ////////////////
///////////////// Переделать его так: считываем последнюю строку каждого ФАЙЛА С ТЕМОЙ!
// Берём там дату создания сообщения, их сортируем и тогда скрипт будет работать корректно
// Замарочка с массивами - их 3 штуки. Тоже передалать!
// ПЕРЕДАЛАТЬ ЭТОТ БЛОК!!!!!!!!!!!!!!!!!

// БЛОК СОРТИРОВКИ: последние ответы ВВЕРХУ (по времени создания файла с темой)!
if ($i>1) { // Если в рубрике хотябы ДВЕ темы
do {$i--; $dt=explode("|",$lines[$i]);
   $filename="$dt[2]$dt[3].csv"; if (is_file("$datadir/$filename")) $ftime=filemtime("$datadir/$filename"); else $ftime="";
   $newlines[$i]="$dt[10]|$ftime|$dt[2]$dt[3]|$i|";
} while($i > 0);
sort($newlines);
//print"<PRE>"; print_r($newlines); exit;
// $newlines - массив с данными: ДАТА | ИМЯ_ФАЙЛА_С_ТЕМОЙ | № п/п |
// $lines - массив со всеми темами выбранной рубрики
$i=$maxi;
do {$i--; $dtn=explode("|", $newlines[$i]);
  $numtp="$dtn[3]"; $goodlines[$i]="$lines[$numtp]";
} while($i > 0);
$lines=null; $lines=$goodlines; // Записываем отсортированные по дате создания темы в переменную, которую будем использовать дальше
} // if ($i>1)
// КОНЕЦ блока сортировки
// ПЕРЕДАЛАТЬ ЭТОТ БЛОК!!!!!!!!!!!!!!!!!

// Исключаем ошибку вызова несуществующей страницы
if (!isset($_GET['page'])) $page=1; else { $page=$_GET['page']; if (!ctype_digit($page)) $page=1; if ($page<1) $page=1; }

// Показываем msg_onpage ТЕМ
$fm=$maxi-$tem_onpage*($page-1); if ($fm<"0") $fm=$tem_onpage;
$lm=$fm-$tem_onpage; if ($lm<"0") $lm="0";

$timetek=time();

do {$fm--; $dt=explode("|", $lines[$fm]);

 $num=$fm+2; $numid=$fm+1;

$filename="$dt[2]$dt[3]"; if (is_file("$datadir/$filename.csv")) { // если файл с темой существует - то показать тему
$msgsize=sizeof(file("$datadir/$filename.csv"));

$linetmp=file("$datadir/$filename.csv"); if (sizeof($linetmp)!=0) {
$pos=$msgsize-1; $dtt=explode("|", $linetmp[$pos]);}

print "<tr height=40><td width=3% class=row1><table><tr><td width=10 bgcolor=#22FF44><B><a href='admin.php?id=$id&rd=$dt[2]$dt[3]&page=$page' title='РЕДАКТИРОВАТЬ'>.P.</a></B></td><td width=10 bgcolor=#FF2244><B><a href='admin.php?xd=$dt[2]$dt[3]&page=$page' title='УДАЛИТЬ' onclick=\"return confirm('Будет удалена ТЕМА со всеми сообщениями! Удалить? Уверены?')\" >.X.</a></B></td></tr>
<!--<tr><td width=10 bgcolor=#F58405><B><a href='admin.php?rename=$dt[2]$dt[3]&id=$fid&page=$page' title='ПЕРЕНУМЕРОВАТЬ!'>.Н.</a></B></td></tr>-->
</nobr></table></td>
<td width=57% class=row1 valign=middle><span class=forumlink><b>";

if ($dt[10]==TRUE) echo'<font color=red>VIP </font>';

print"<a href=\"admin.php?id=$dt[2]$dt[3]\">$dt[5]</a>";

if ($msgsize>$msg_onpage) { // ВЫВОДИМ СПИСОК ДОСТУПНЫХ СТРАНИЦ ТЕМЫ
$maxpaget=ceil($msgsize/$msg_onpage); $addpage="";
echo'</b></span><small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="padding:6px;" class=pgbutt>Страницы: ';
if ($maxpaget<=5) $f1=$maxpaget; else $f1=5;
for($i=1; $i<=$f1; $i++) {if ($i!=1) $addpage="&page=$i"; print"<a href=admin.php?id=$dt[2]$dt[3]$addpage>$i</a> &nbsp;";}
if ($maxpaget>5) print "... <a href=admin.php?id=$dt[2]$dt[3]&page=$maxpaget>$maxpaget</a>"; }

print"</div></td><td class=row2 align=center>$msgsize</td><td class=row2><span class=gensmall>";

$codename=urlencode($dt[8]);
if ($dt[6]==TRUE) print "<a href='admin.php?event=profile&pname=$codename'>$dt[8]</a><BR><small>$user_name</small>"; else print"$dt[8]<BR><small>$guest_name</small>";


// защита if (strlen...) только если файл есть и имеет верный формат - выводим
if ($msgsize>=2) {$linesdat=file("$datadir/$filename.csv"); $dtdat=explode("|", $linesdat[$msgsize-1]);
if (mb_strlen($linesdat[$msgsize-1])>10) {$dt[0]=$dtdat[0]; $dt[1]=$dtdat[1]; $dt[2]=$dtdat[2]; $dt[5]=$dtdat[5]; $dt[6]=$dtdat[6];}}

if (date("d.m.Y",$dtt[4])==$date)  $dtt[4]="<B>сегодня</B> в ".csve("H:i:s",$dtt[4]); else $dtt[4]=date("d.m.y - H:i",$dtt[4]);
print "</span></td><td class=row2 align=left><span class=gensmall>Автор: <B>$dtt[8]</B><BR>дата/время: $dtt[4]</span></td></tr>\r\r\n";

} //if is_file

} while($lm < $fm);


// формируем переменную $pageinfo - со СПИСКОМ СТРАНИЦ
$pageinfo=""; $addpage=""; $maxpage=ceil(($maxi+1)/$msg_onpage); if ($page>$maxpage) $page=$maxpage;
$pageinfo.="<div style='padding:6px;' align=right class=pgbutt>Страницы: &nbsp;";
if ($page>3 and $maxpage>5) $pageinfo.="<a href=admin.php?id=$fid>1</a> ... ";
$f1=$page+2; $f2=abs($page-2); if ($f2=="0") $f2=1; if ($page>=$maxpage-1) $f1=$maxpage;
if ($maxpage<=5) {$f1=$maxpage; $f2=1;}
for($i=$f2; $i<=$f1; $i++) { if ($page==$i) $pageinfo.="<B>$i</B> &nbsp;"; 
else {if ($i!=1) $addpage="&page=$i"; $pageinfo.="<a href=admin.php?id=$fid$addpage>$i</a> &nbsp;";} }
if ($page<=$maxpage-3 and $maxpage>5) $pageinfo.="... <a href=admin.php?id=$fid&page=$maxpage>$maxpage</a>";
$pageinfo.='</div>';

print "</table>$pageinfo";

if ($maxi>0) { // БЫСТРЫЙ ПЕРЕХОД к теме

// 1. Необходимо создать новый массив со списком тем + кол-вом сообщений в теме!
$ii=$maxi; $cn=0; $i=0;
do {$dt=explode("|", $lines[$i]); 
//10001|10001|102|1002|1348390988|дизайн форума|0||dash||0|1|||
if (is_file("$datadir/$dt[2]$dt[3].csv")) $counter=sizeof(file("$datadir/$dt[2]$dt[3].csv"));
$records[$i]="$dt[5]|$dt[2]$dt[3]|$dt[4]|$counter|";
//print" $records[$i]<br>";
$i++;} while($i<$ii);
sort($records);
//print"<PRE>"; print_r($records); exit;
// 2. Сортируем массив по имени темы

echo '<table width=100% cellpadding=3 cellspacing=1 class=forumline><TR><TD class=catHead><span class=cattitle>Навигация</span></td></tr>
<tr><td class=row1 align=right><span class=gensmall>
Быстрый переход по темам &nbsp; <select onchange="window.location=(\'admin.php?id=\'+this.options[this.selectedIndex].value)">
<option>Выберите тему</option>';
$ii=$maxi; $cn=0; $i=0; $a='#DEDEDE'; $b='#FFFFFF';
do {$dt=explode("|", $records[$i]);
$dt[2]=date("d.m.y г.",$dt[2]);
$c=$b; $b=$a; $a=$c;
print" <option style='background-color:$a; font-size:13px;' value='$dt[1]'>- $dt[0] [$dt[3]], дата $dt[2] </option>\r\n"; $i++;} while($i<$ii);
echo'</optgroup></select></TD></TR></TABLE><br>'; } // if($maxi>0)
}}



// ------------ Выбрано редактирование ТЕМЫ
if (isset($_GET['rd'])) { if ($_GET['rd'] !="") { $rd=replacer($_GET['rd']); $i="-1";

// Бежим по массиву тем и ищем ту тему, которую вызвали на редактирование
do {$i++; $dt=explode("|",$lines[$i]);
if ("$dt[2]$dt[3]"===$rd) $i=$maxi; // ЕСЛИ нашли тему, значит завершаем цикл и дальше работаем со строкой
} while($i < $maxi);

if ($dt[10]==FALSE) {$vt1="checked"; $vt2="";} else {$vt2="checked"; $vt1="";}
if ($dt[11]==FALSE) {$ct2="checked"; $ct1="";} else {$ct1="checked"; $ct2="";}

print "<form action='admin.php?event=rdtema&page=$page' method=post name=REPLIER1><table cellpadding=4 cellspacing=1 width=100% class=forumline><tr><td class=catHead colspan=2 height=28><span class=cattitle>Редактирование Темы</span></td></tr>
<tr><td class=row1 align=right valign=top>Название темы</td>
<td class=row1 align=left valign=top><input type=text class=post value='$dt[5]' name=zag size=70>
<input type=radio name=open_tema value='1'$ct1/> <font color=blue><B>открыта</B></font>&nbsp;&nbsp; <input type=radio name=open_tema value='0'$ct2/> <font color=red><B>закрыта</B></font>
<input type=hidden name=rd value='$rd'>
<input type=hidden name=name value='$dt[8]'>
<input type=hidden name=who value='$dt[6]'>
<input type=hidden name=email value='$dt[9]'>
<input type=hidden name=oldzag value='$dt[5]'>
<input type=hidden name=timetk value='$dt[4]'></TD></TR>
<TR><TD class=row1 align=right>Добавить к этой теме другую?</td><td class=row1>";

if ($maxi>0) { // Выводим темы отосртированные по имени. Массив $records (см. выше)
echo '<select name="temaplus"><option value="">Хотите объединить? Выберите тему!</option>';
$ii=$maxi; $cn=0; $i=0; $a='#DEDEDE'; $b='#FFFFFF';
do {$dtt=explode("|", $records[$i]);
$dtt[2]=date("d.m.y г.",$dtt[2]);
$c=$b; $b=$a; $a=$c;
if ($rd!=$dtt[1]) print" <option style='background-color:$a; font-size:14px;' value='$dtt[1]'>- $dtt[0] [$dtt[3]], дата $dtt[2] </option>\r\n"; $i++;} while($i<$ii);
echo'</optgroup></select>
<input type=radio name=temakuda value="0"checked/> <font color=gray><B>в конец темы</B></font>&nbsp;&nbsp; <input type=radio name=temakuda value="1"/> <font color=black><B>В начало темы</B></font>
'; } // if($maxi>0)

print"</td></tr>
<tr><td class=row1 align=right valign=top>Переместить в другой раздел и перейти куда?</TD><TD class=row1>
<select style='width=440' name='changefid'>
<option selected value='$fid'>Нет. Оставить в текущем</option><br><br>";

$mainlines=file("$datadir/wrforum.csv");
$mainsize=sizeof($mainlines); if($mainsize<1) exit("$back файл данных повреждён или у вас всего одна рубрика!");
$ii=count($mainlines); $cn=0; $i=0;
do {$mdt=explode("|",$mainlines[$i]);
if ($mdt[3]==TRUE) {if ($cn!=0) {echo'</optgroup>'; $cn=0;} $cn++; print"<optgroup label='$mdt[4]'>";} else {print" <option value='$mdt[2]' >|-$mdt[4]</option>";}
$i++; } while($i <$ii);
$s2=""; $s1="checked"; // поменяйте и будет по умолчанию переход в новую рубрику
print"</optgroup></select>

<input type=radio name=goto value='0'$s1> в текущую рубрику &nbsp;&nbsp; <input type=radio name=goto value='1'$s2> туда куда переносим тему

</TD></TR><tr><td class=row1 align=right valign=top>Статус темы: обычная / VIP ?</TD><TD class=row1>
<input type=radio name=viptema value='0'$vt1/> <font color=gray><B>обычная тема</B></font>&nbsp;&nbsp; <input type=radio name=viptema value='1'$vt2/> <font color=red><B>VIP-тема</B></font> (всегда отображается первой на первой странице)
</td></tr><tr><td colspan=2 class=row1>
<SCRIPT language=JavaScript>document.REPLIER1.zag.focus();</SCRIPT><center><input type=submit class=mainoption value='     Изменить     '></td></span></tr></table></form>";
}

} else {

echo '<table width=100% cellpadding=4 cellspacing=1 class=forumline><tr> <td class=catHead colspan=2 height=28><span class=cattitle>Добавление темы</span></td></tr>
<tr><td class=row1 align=right valign=top rowspan=2><span class=gensmall>';

if (!isset($wrfname)) echo'<B>Имя</B> и E-mail<BR>';

print "<B>Заголовок темы</B><BR><B>Сообщение</B></td><td class=row1 align=left valign=middle rowspan=2>
<form action=\"admin.php?event=add_tema&id=$fid\" method=post name=REPLIER>";
if (isset($wrfname)) {print "<input type=hidden name=name value='$wrfname' class=post><input type=hidden name=who value='да'>";}
else {echo '<input type=text value="" name=name size=23 class=post> <input type=text value="" name=email size=24 class=post><br>';}
print "
<input type=hidden name=maxzd value=$maxzd>
<input type=text class=post value='' name=zag size=50><br>
<textarea cols=100 rows=6 size=500 name=msg class=post></textarea><BR>
<BR><input type=submit class=mainoption value='     Добавить     '></td></form>
<SCRIPT language=JavaScript>document.REPLIER.msg.focus();</SCRIPT>
</span></tr></table><BR>";
}
// --------------

}









if (mb_strlen($id)==7) { // выводим СООБЩЕНИЕ в текущей теме

// определяем есть ли информация в файле с данными
if (!is_file("$datadir/$id.csv")) exit("<BR><BR>$back. Извините, но такой темы на форуме не существует.<BR> Скорее всего её удалил администратор.");
$lines=file("$datadir/$id.csv"); $mitogo=count($lines); $i=$mitogo; $maxi=$i-1;

if ($mitogo>0) { $tblstyle="row1"; $printvote=null;

// Считываем СТАТИСТИКУ ВСЕХ УЧАСТНИКОВ
if (is_file("$datadir/userstat.csv")) {$ufile="$datadir/userstat.csv"; $ulines=file("$ufile"); $ui=count($ulines)-1;}

// Ищем тему в XХХ.csv - проверяем не закрыта ли тема? и сразу же ищем есть ли в топике
// Заодно формируем № строки предыдущей и следующей темы
$ok=FALSE; $closed=FALSE; $lasttema=FALSE; $nexttema=FALSE;
if (is_file("$datadir/$fid.csv")) {
$msglines=file("$datadir/$fid.csv"); $mg=count($msglines); $mgmax=$mg-1;
do {$mg--; $mt=explode("|",$msglines[$mg]);
if ("$mt[2]$mt[3]"==$id) { $ok=1;
if ($mt[11]==FALSE) $closed=TRUE;
if ($mg>=1) $lasttema=$mg; // № строки предыдущей темы
if ($mg<$mgmax) $nexttema=$mg+1; // № строки следующей темы
$mg=0; }
} while($mg >"0");}


$maxzd=null; // Уточняем статус по кол-ву ЗВЁЗД в теме
$imax=count($mainlines);
do {$imax--; $ddt=explode("|", $mainlines[$imax]); if ($ddt[2]==$fid) $maxzd=$ddt[9]; } while($imax>"0");
if (!ctype_digit($maxzd)) $maxzd=0;

// Исключаем ошибку вызова несуществующей страницы
if (!isset($_GET['page'])) $page=1; else {$page=$_GET['page']; if (!ctype_digit($page)) $page=1; if ($page<1) $page=1;}

// формируем переменную $pageinfo - со СПИСКОМ СТРАНИЦ
$pageinfo=""; $addpage=""; $maxpage=ceil(($maxi+1)/$msg_onpage); if ($page>$maxpage) $page=$maxpage;
$pageinfo.="<div align=center style='padding:6px;' class=pgbutt>Страницы: &nbsp;";
if ($page>3 and $maxpage>5) $pageinfo.="<a href=admin.php?id=$id>1</a> ... ";
$f1=$page+2; $f2=abs($page-2); if ($f2=="0") $f2=1; if ($page>=$maxpage-1) $f1=$maxpage;
if ($maxpage<=5) {$f1=$maxpage; $f2=1;}
for($i=$f2; $i<=$f1; $i++) { if ($page==$i) $pageinfo.="<B>$i</B> &nbsp;"; 
else {if ($i!=1) $addpage="&page=$i"; $pageinfo.="<a href=admin.php?id=$id$addpage>$i</a> &nbsp;";} }
if ($page<=$maxpage-3 and $maxpage>5) $pageinfo.="... <a href=admin.php?id=$id&page=$maxpage>$maxpage</a>";
$pageinfo.='</div>';

print"$pageinfo";

$fm=$msg_onpage*($page-1); if ($fm>$maxi) $fm=$maxi-$msg_onpage;
$lm=$fm+$msg_onpage; if ($lm>$maxi) $lm=$maxi+1;

do {$dt=explode("|", $lines[$fm]);

$fm++; $num=$maxi-$fm+2; $status=""; unset($youwr);

if (mb_strlen($lines[$fm-1])>5) { // Если строчка потерялась в скрипте (пустая строка) - то просто её НЕ выводим

if (isset($_GET['quotemsg'])) {
$quottime=date("d.m.y в H:i",$dt[4]);
$quotemsg=replacer($_GET['quotemsg']); if(ctype_digit($quotemsg) and $quotemsg==$fm) $qm="[Quote][b]$dt[8] $quottime пишет:[/b]\r\n".$dt[14]."[/Quote]";} else $qm="";

$msg=str_replace("[b]","<b>", $dt[14]);
$msg=str_replace("[/b]","</b>",$msg);
$msg=str_replace("[RB]","<font color=red><B>",$msg);
$msg=str_replace("[/RB]","</B></font>",$msg);
$msg=str_replace("&lt;br&gt;","<br>",$msg); // ЗАКОМЕНТИРОВАТЬ при ЧИСТОЙ установке скрипта или в 2017 году!
$msg=str_replace("[br]","<br>",$msg); // c 08.2015 г.
$msg=str_replace("[Quote]","<br><UL><B><U><small>Цитата:</small></U></B><table width=95% border=0 cellpadding=5 cellspacing=1 style=\"margin-left:18px;padding:5px;\"><tr><td class=quote>",$msg); $msg=str_replace("[/Quote]","</td></tr></table></UL>",$msg);
$msg=str_replace("[Code]","<br><UL><B><U>Код:</U></B><table width=95% border=0 cellpadding=10 cellspacing=1 style=\"margin-left:18px;padding:5px;\"><tr><td class=code>",$msg); $msg=str_replace("[/Code]","</td></tr></table></UL>",$msg);
$msg=str_replace("&lt;br&gt;","<br>",$msg);

if ($showsmiles==TRUE) {$i=count($smiles)-1; // заменяем текстовые смайлики на графические если разрешено
for($k=0; $k<$i; $k=$k+2) {$j=$k+1; $msg=str_replace("$smiles[$j]","<img src='smile/$smiles[$k].gif' border=0>",$msg);}}


// Если разрешена публикация УРЛов
if ($liteurl==TRUE) {$msg=' '.$msg; $msg=preg_replace ("/([^\[img\]])((https|http|ftp)+(s)?:(\/\/)([\w]+(.[\w]+))([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])?)/i", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $msg); $msg=ltrim($msg);}

// После замены АДРЕСА URL преобразуем код [img]
$msg=preg_replace('#\[img\](.+?)(jpg|gif|jpeg|png|bmp)\[/img\]#','<img src="$1$2" border="0">',$msg);

// Вставляем видео с ЮТУБ
$msg=preg_replace("/(\[Youtube\])(.+?)(\[\/Youtube\])/is","<center><object width=480px height=360px><param name=movie value=\"https://www.youtube.com/v/$2\"></param>
<param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param>
<embed src=\"https://www.youtube.com/v/$2\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=480px height=360px></embed></object></center>",$msg);

// считываем в память данные по пользователю
if (is_file("$datadir/user.php")) $userlines=file("$datadir/user.php"); $usercount=count($userlines);
if ($dt[6]==TRUE) { $iu=$usercount; $predup="0";
do {$iu--; $du=explode("|", $userlines[$iu]); if ($du[0]==$dt[7]) { 
$reiting=$du[4]; $youavatar=$du[15]; $email=$du[5]; $icq=$du[10]; $site=$du[11]; $userpn=$iu;

if (isset($_COOKIE['wrfcookies']) or $podpis_pokaz==TRUE) { $youwr=' '.$du[14];
if (mb_strlen($youwr)>10) {
$youwr=preg_replace('#\[img\](.+?)(jpg|gif|jpeg|png|bmp)\[/img\]#','<img src="$1$2" border="0">',$youwr);
$image=stristr($youwr, '<img src="');
$image=mb_substr($image,10);
$pos=strpos($image,'" border="0">');
$image=mb_substr($image,0,$pos);

if (@GetImageSize($image)==TRUE) { $size=GetImageSize($image); // $width=$size[0]; $height=$size[1];
if ($size[0]>350 or $size[1]>20) {
do {$size[0]=round($size[0]/2); $size[1]=round($size[1]/2);}
while ($size[0]>350 or $size[1]>20); }
$youwr=str_replace('border="0"',"border=\"0\" width=\"$size[0]\" height=\"$size[1]\"",$youwr);}
if (stristr($youwr,"<img")==FALSE) $youwr=preg_replace("#(\[url=([^\]]+)\](.*?)\[/url\])|(https://(www.)?[0-9a-z\.-]+\.[a-z]{2,6}[0-9a-z/\?=&\._-]*)#","<a href=\"$4\" target='_blank'>$4</a> ",$youwr);
$youwr=ltrim($youwr);} else $youwr=$du[14];
} //if (mb_strlen($youwr)>10)
} // if (isset($_COOK
} while($iu > "0");
}

if ($tblstyle=="row1") $tblstyle="row2"; else $tblstyle="row1";

if (!isset($m1)) {
$frname=str_replace(' »','',$frname); $frtname=str_replace(' »','',$frtname); //вырезаем лишние символы
print "<table border=0><tr><td><span class=nav><a href=admin.php class=nav>$forum_name</a> » <a href=admin.php?id=$fid class=nav>$frname</a> » <a href='admin.php?id=$dt[2]$dt[3]' class=nav><strong>$dt[5]</strong></a> &nbsp;</span></td>
<td width=10 bgcolor=#22FF44><B><a href='admin.php?id=$fid&rd=$dt[2]$dt[3]&page=$page' title='РЕДАКТИРОВАТЬ'>.P.</a></B></td>
<td width=10 bgcolor=#FF2244><B><a href='admin.php?xd=$dt[2]$dt[3]&page=$page' title='УДАЛИТЬ' onclick=\"return confirm('Будет удалена ТЕМА со всеми сообщениями! Удалить? Уверены?')\" >.X.</a></B></td></tr></table>";
echo'<table class=forumline width=100% cellspacing=1 cellpadding=3><tr>
<th class=thLeft width=150 height=26 nowrap=nowrap>Автор</th>
<th class=thRight nowrap=nowrap>Сообщение</th>'; $m1="1"; }

print"</tr><tr height=150><td class=$tblstyle valign=top><span class=name><BR><center>";

// Проверяем: это гость?
if (!isset($youwr)) {if (mb_strlen($dt[9])>5) print "$dt[8] "; else print"$dt[8] ";
$kuda=$fm-1; print" <a href='javascript:%20x()' onclick=\"DoSmilie('[b]$dt[8][/b], ');\" class=nav>&bull;</a><BR><br>
<form name='m$fm' method='post' action='tools.php?event=mailto' target='email' onclick=\"window.open('tools.php?event=mailto','email','width=600,height=350,left=100,top=100');return true;\"><input type=hidden name='email' value='$dt[9]'><input type=hidden name='name' value='$dt[8]'><input type=hidden name='id' value=''>
<input type=button value='ЛС'></form><BR><small>$guest_name</small>";}


else {
$codename=urlencode($dt[8]);
print "<a name='m$fm' href='admin.php?event=profile&pname=$codename' class=nav>$dt[8]</a> <a href='javascript:%20x()' onclick=\"DoSmilie('[b]$dt[8][/b], ');\" class=nav>&bull;</a><BR><BR><small>";
if (mb_strlen($status)>2 & $dt[6]==TRUE & isset($youwr)) print "$status"; else print"$user_name";
if (isset($reiting)) {if ($reiting>0) {echo'<BR>'; if (is_file("$forum_skin/star.gif")) {for ($ri=0;$ri<$reiting;$ri++) {print"<img src='$forum_skin/star.gif' border=0>";} } }}
}

if (isset($youwr) and is_file("$datadir/userstat.csv")) {
if (isset($ulines[$userpn])) {
if (mb_strlen($ulines[$userpn])>5) {
$ddu=explode("|",$ulines[$userpn]);

print"</small></span><br>
<noindex><fieldset STYLE='color:#646464'>
<legend STYLE='font-weight:bold;'>Статистика:</legend>
<div style='PADDING:2px;' align=left class=gensmall>Тем создано: $ddu[5]<br>Сообщений: $ddu[6]<br>Репутация: $ddu[7] <A href='#$fm' style='text-decoration:none' onclick=\"window.open('tools.php?event=repa&name=$ddu[2]&who=$userpn','repa','width=600,height=600,left=50,top=50,scrollbars=yes')\">&#177;</A><br>Предупреждения: $ddu[8]</div>
</fieldset>
"; }}}


print "
<br><br>IP: $dt[12] <a href='admin.php?badip&ip_get=$dt[12]'><B><font color=red>В БАН</font></B></a><br>
</span></td><td rowspan=2 class=$tblstyle width=100% height=28 valign=top>
<span class=postbody><UL>$msg</UL></span>";


if (date("d.m.Y",$dt[4])==$date)  $dt[4]="сегодня в ".csve("H:i",$dt[4]); else $dt[4]=date("d.m.y - H:i:s",$dt[4]);

print"</td></tr><tr>
<td class=row3 valign=middle align=center ><span class=postdetails>
<table><tr><td width=10 bgcolor=#22FF44><B><a href='admin.php?id=$id&topicrd=$fm&page=$page#m$lm' title='РЕДАКТИРОВАТЬ'>.P.</a></B></td><td width=10 bgcolor=#FF2244><B><a href='admin.php?id=$id&topicxd=$fm&page=$page' title='УДАЛИТЬ'>.X.</a></B></td></tr></table>
<I>Сообщение # <B>$fm.</B></I>
Отправлено: <b>$dt[4]</b></span></td>
</tr>
<tr><td class=spaceRow colspan=2 height=1><img src=\"$forum_skin/spacer.gif\" width=1 height=1></td>";

} // если строчка потерялась

} while($fm < $lm);

// Предыдущая и следующая тема - 2012 г.
if ($lasttema!=FALSE) {$lasttema--; $ldt=explode("|",$msglines[$lasttema]); $lasttema="<TD align=left>&#9668; <B><a href='admin.php?id=$ldt[2]$ldt[3]'>$ldt[5]</a></B> :Предыдущая тема</TD>";} else $lasttema="";
if ($nexttema!=FALSE) {$ndt=explode("|",$msglines[$nexttema]); $nexttema="<TD align=right>Следующая тема: <B><a href='admin.php?id=$ndt[2]$ndt[3]'>$ndt[5]</a></B> &#9658;";} else $nexttema="<TD>";

print"</tr></table> <table cellSpacing=0 cellPadding=0 width=100%><TR height=25>$lasttema$nexttema</TD></tr></table> $pageinfo<br>";

print"</span></td></tr></table>";


// Выбрана метка .P. - редактирование сообщения
if (isset($_GET['topicrd'])) { // выводим сообщение в форму
$topicrd=$_GET['topicrd']-1;
$lines=file("$datadir/$id.csv");
$dt=explode("|", $lines[$topicrd]);
$dt[4]=str_replace("<br>", "\r\n", $dt[14]);
$oldmsg=str_replace("'", ":kovichka:",$dt[14]); // шифруем символ '
print "
<form action=\"admin.php?event=add_msg&id=$id&topicrd=$topicrd\" method=post name=REPLIER>
<table cellpadding=3 cellspacing=1 width=100% class=forumline>
<tr><th class=thHead colspan=2 height=25><b>Сообщение</b></th></tr>
<tr><td class=row1 width=22% height=25><span class=gen><b>Имя
</b></span></td>
<td class=row2 width=78%> <span class=genmed>
<input type=hidden name=oldmsg value='$oldmsg'>
<input type=text value='$dt[8]' name=name size=20>&nbsp;
E-mail <input type=text value='$dt[9]' name=email size=26>&nbsp; 
<input type=hidden name=who value='$dt[6]'>Участник? <B>";
if ($dt[6]==TRUE) echo'ДА'; else echo'НЕТ';

} else {

print "</B><form action=\"admin.php?event=add_msg&id=$id\" method=post name=REPLIER>
<input type=hidden name=maxzd value=$maxzd>
<input type=hidden name=id value='$dt[2]$dt[3]'>
<input type=hidden name=page value=$page>
<input type=hidden name=zag value=\"$dt[5]\">

<table cellpadding=3 cellspacing=1 width=100% class=forumline>
<tr><th class=thHead colspan=2 height=25><b>Сообщение</b></th></tr>
<tr><td class=row1 width=22% height=25><span class=gen><b>Имя ";

if (!isset($wrfname)) echo'и E-mail<BR>';

echo'</b></span></td><td class=row2 width=78%> <span class=genmed>';

if (!isset($wrfname)) echo'<input type=text name=name size=28 class=post> <input type=text name=email size=30 class=post>';
else print "<b>$wrfname</b><input type=hidden name=name value='$wrfname'><input type=hidden name=who value='1'>";
}


echo'</span></td></tr><tr>
<td class=row1 valign=top><span class=genmed><b>Сообщение</b><br><br>Для вставки имени, кликните на точку рядом с ним.<br><br>Смайлики:<br>
<table align=center width=100 height=70><tr><td valign=top>';

if ($showsmiles==TRUE) {$i=count($smiles)-1;
for($k=0; $k<$i; $k=$k+2) {$j=$k+1; print"<A href='javascript:%20x()' onclick=\"DoSmilie(' $smiles[$j]');\"><img src='smile/$smiles[$k].gif' border=0></a> ";} }
print"<A href='javascript:%20x()' onclick=\"DoSmilie('[RB]  [/RB] ');\"><font color=red><B>RB</b></font></a>
<a name='add' href='#add' onclick=\"window.open('tools.php?event=moresmiles','smiles','width=250,height=300,left=50,top=150,toolbar=0,status=0,border=0,scrollbars=yes')\">Ещё смайлы</a>
</td></tr></table></span></td>
<td class=row2 valign=top><span class=gen><table width=450><tr valign=middle><td><span class=genmed>
<input type=button class=button value=' B ' style='font-weight:bold; width: 30px' onclick=\"DoSmilie(' [b]  [/b] ');\">&nbsp;
<input type=button class=button value=' RB ' style='font-weight:bold; color:red' onclick=\"DoSmilie('[RB] [/RB]');\">&nbsp;
<INPUT type=button class=button value='Цитировать выделенное' style='width: 170px' onclick='REPLIER.msg.value += \"[Quote]\"+(window.getSelection?window.getSelection():document.selection.createRange().text)+\"[/Quote]\"'>&nbsp;
<input type=button class=button value=' Код ' onclick=\"DoSmilie(' [Code]  [/Code] ');\">&nbsp;
<input type=button class=button value=' IMG ' style='font-weight:bold; color:navy' onclick=\"DoSmilie('[img][/img]');\">&nbsp;
</span></td></tr><tr>";

if (isset($_GET['topicrd']))
{
$dt[14]=str_replace("&lt;br&gt;","[br]",$dt[14]); // ЗАКОМЕНТИРОВАТЬ при ЧИСТОЙ установке скрипта или в 2017 году!
$dt[14]=str_replace("[br]","\r\n",$dt[14]); // c 08.2015 г.
print "
<td colspan=9><span class=gen><textarea name=msg cols=103 rows=10 class=post>$dt[14]</textarea></span></td>
<input type=hidden name=maxzd value=$maxzd>
<input type=hidden name=who value=$dt[6]>
<input type=hidden name=id value='$dt[2]$dt[3]'>
<input type=hidden name=zag value=\"$dt[5]\">
<input type=hidden name=fnomer value=$topicrd>
<input type=hidden name=timetk value=$dt[4]>
<input type=hidden name=page value=$page>
</tr></table></span></td></tr>
<tr><td class=catBottom colspan=2 align=center height=28><input type=submit tabindex=5 class=mainoption value='Изменить и сохранить'>&nbsp;&nbsp;&nbsp;<input type=reset tabindex=6 class=mainoption value=' Очистить '></td>
</tr></table></form>";

} else {

echo'<td colspan=9><span class=gen><textarea name=msg cols=103 rows=10 class=post>'.$qm.'</textarea></span></td>
</tr></table></span></td></tr><tr>
<td class=catBottom colspan=2 align=center height=28><input type=submit tabindex=5 class=mainoption value=" Отправить ">&nbsp;&nbsp;&nbsp;<input type=reset tabindex=6 class=mainoption value=" Очистить "></td>
</tr></table></form>';



}}

} // else if event !=""
}
} // if (isset($_GET['id'])) - если есть $id





if (isset($_GET['event'])) {


// КОНФИГУРИРОВАНИЕ форума - выбор настроек
if ($_GET['event']=="configure") {

if (!isset($specblok1)) $specblok1="0";// временно так как ввёл новые переменные в data/config.php
if (!isset($specblok2)) $specblok2="0";// --//--
if (!isset($nosssilki)) $nosssilki="0";// --//--

if ($ktotut!=1) {exit("$back! Модераторам запрещено изменять настройки форума! Если нужно сменить пароль - обращайтесь к админу!");}





//<tr><td class=row1>Скин форума</td><td class=row1><select class=input name=forum_skin>";

$skin=null; $path = '.'; // Путь до папки. '.' - текущая папка
if ($handle = opendir($path)) {
while (($file = readdir($handle)) !== false)
if (!is_dir($file)) { 
$stroka=stristr($file, "style-"); if (mb_strlen($stroka)>"6") { 
$tskin=str_replace("style-", "Скин ", $file);
if ($forum_skin==$file) {$marker="selected";} else {$marker="";}
$skin.="<option $marker value=\"$file\">$tskin</option>";}
}
closedir($handle); } else echo'Ошибка!';


$ok='checked="checked"'; // Новая система [изменён в 2016г.]
$sp1=""; if ($forum_lock==TRUE) $sp1=$ok;
$rn1=""; if ($random_name==TRUE) $rn1=$ok;
$sa1=""; if ($admin_send==TRUE) $sa1=$ok;
$s1=""; if ($sendmail==TRUE) $s1=$ok;
$am1=""; if ($antimat==TRUE) $am1=$ok;
$as1=""; if ($antispam==TRUE) $as1=$ok;
$asn1="";if ($antispam2k==TRUE) $asn1=$ok;
$ct1=""; if ($g_add_tema==TRUE) $ct1=$ok;
$cm1=""; if ($g_add_msg==TRUE) $cm1=$ok;
$u1=""; if ($activation==TRUE) $u1=$ok;
$lu1=""; if ($liteurl==TRUE) $lu1=$ok;
$ns1=""; if ($nosssilki==TRUE) $ns1=$ok;
$st1=""; if ($statistika==TRUE) $st1=$ok;
$cs1=""; if ($can_up_file==TRUE) $cs1=$ok;
$af1=""; if ($antiflud==TRUE) $af1=$ok;
$ip1=""; if ($ipblok==TRUE) $ip1=$ok;
$rk1=""; if ($reklama==TRUE) $rk1=$ok;
$sm1=""; if ($showsmiles==TRUE) $sm1=$ok;
$sb1=""; if ($specblok1==TRUE) $sb1=$ok;
$bs1=""; if ($specblok2==TRUE) $bs1=$ok;
$ob1=""; if ($onlineb==TRUE) $ob1=$ok;
$qt1=""; if ($quikchat==TRUE) $qt1=$ok;

print "<center><B>Конфигурирование</b></font>
<form action=admin.php?event=config method=post name=REPLIER>
<table width=80% cellpadding=4 cellspacing=1 align=center class=forumline><tr> 
<th class=thCornerL height=25 width=40% nowrap=nowrap>Параметр</th>
<th class=thTop nowrap=nowrap>Значение</th></tr>

<tr><td align=center colspan=2 class=row2><b>Общие настройки</b></td></tr>

<tr><td colspan=2 class=row1><div><input type='checkbox' id='sp1' $sp1 name='forum_lock' class='key' /><label for='sp1'>Блокировка форума: отключить работу форума на добавление тем/сообщений (да / нет)?</label></div></td></tr>
<tr><td class=row2>Название форума</td><td class=row2><input type=text value='$forum_name' name=forum_name class=post maxlength=50 size=50></td></tr>
<tr><td class=row1 valign=top>Описание.<BR><B><small>Использовать HTML-теги не рекомендуется!</small></td><td class=row1><textarea cols=55 rows=6 size=700 class=post name=forum_info>$forum_info</textarea></td></tr>
<tr><td class=row2>Е-майл администратора</td><td class=row2><input type=text value='$adminemail' class=post name=newadminemail maxlength=40 size=25></td></tr>
<tr><td class=row1>Логин и пароль администратора (данные входа в админ.панель со 100% набором прав)*</td><td class=row1>Логин: <input name=adminname class=post type=text value='$adminname'> Пароль: <input name=password type=hidden value='$password'><input class=post type=text value='скрыт' maxlength=10 name=newpassword size=15></td></tr>
<tr><td class=row2>Логин и пароль модератора (частичный набор прав)*</td><td class=row2>Логин: <input class=post name=modername type=text value='$modername'> Пароль: <input name=moderpass type=hidden value='$moderpass'><input class=post type=text value='скрыт' maxlength=10 name=newmoderpass size=15></td></tr>
<tr><td class=row1>Сколько давать очков репутации при добавлении:</td><td class=row1><B>сообщения</B>: <input type=text value='$repaaddmsg' class=post name=repaaddmsg maxlength=2 size=6> <B>темы</B>: <input type=text value='$repaaddtem' class=post name=repaaddtem maxlength=2 size=6> <B>файла</B>: <input type=text value='$repaaddfile' class=post name=repaaddfile maxlength=2 size=6></td></tr>
<tr><td colspan=2 class=row2><div><input type='checkbox' id='s1' $s1 name='sendmail' class='key' /><label for='s1'>Включить отправку сообщений?</label></div></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='rn1' $rn1 name='random_name' class='key' /><label for='rn1'>При загрузке файла генерировать ему имя случайным образом?</label></div></td></tr>
<tr><td colspan=2 class=row2><div><input type='checkbox' id='sa1' $sa1 name='admin_send' class='key' /><label for='sa1'>Мылить админу сообщения о вновь зарегистрированных пользователях?</label></div></td></tr>
<tr><td class=row1>Макс. длина имени / заголовка темы / сообщения</td><td class=row1><input type=text value='$maxname' class=post name=newmaxname maxlength=2 size=10> &nbsp;&nbsp; .:. &nbsp;&nbsp; <input type=text value='$maxzag' class=post name=maxzag maxlength=2 size=10> &nbsp;&nbsp; .:. &nbsp;&nbsp; <input type=text value='$maxmsg' class=post maxlength=4 name=newmaxmsg size=10></td></tr>
<tr><td class=row2>Тем / Cообщений / Участников на страницу</td><td class=row2><input type=text value='$tem_onpage' class=post maxlength=2 name=newtem_onpage size=11> &nbsp; .:. &nbsp; <input type=text value='$msg_onpage' class=post maxlength=2 name=newmsg_onpage size=11> &nbsp; .:. &nbsp; <input type=text value='$uq' maxlength=3 class=post name=uq size=11></td></tr>

<tr><td align=center colspan=2 class=row1><b>Защиты от взлома и накрутки</b></td></tr>
<tr><td class=row2><div><input type='checkbox' id='as1' $as1 name='antispam' class='key' /><label for='as1'>Задействовать цифровой АНТИСПАМ?</label></div></td><td class=row2>Длина кода: <input type=text class=post value='$max_key' name=max_key size=4 maxlength=1> (от 1 до 9) цифр</td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='asn1' $asn1 name='antispam2k' class='key' /><label for='asn1'>Задействовать дополнительно АНТИСПАМ типа вопрос/ответ?</label></div></td></tr>
<tr><td class=row2 align=right>вопрос:</td><td class=row1> <input type=text class=post value='$antispam2kv' name=antispam2kv size=25 maxlength=50>, ответ: <input type=text class=post value='$antispam2ko' name=antispam2ko size=12 maxlength=20></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='am1' $am1 name='antimat' class='key' /><label for='am1'>Задействовать АНТИМАТ?</label></div></td></tr>
<tr><td colspan=2 class=row2><div><input type='checkbox' id='u1' $u1 name='activation' class='key' /><label for='u1'>Требовать активации через емайл при регистрации?</label></div></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='ct1' $ct1 name='g_add_tema' class='key' /><label for='ct1'>Разрешить гостям создавать темы</label></div></td></tr>
<tr><td colspan=2 class=row2><div><input type='checkbox' id='cm1' $cm1 name='g_add_msg' class='key' /><label for='cm1'>Разрешить гостям публиковать сообщения</label></div></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='ns1' $ns1 name='nosssilki' class='key' /><label for='ns1'>Запретить гостям добавлять сообщения со ссылками?</label></div></td></tr>
<tr><td colspan=2 class=row2><div><input type='checkbox' id='lu1' $lu1 name='liteurl' class='key' /><label for='lu1'>Делать ссылки в тексте сообщений активными?</label></div></td></tr>

<tr><td align=center colspan=2 class=row1><b>Информационные и рекламные блоки</b></td></tr>

<tr><td colspan=2 class=row2><div><input type='checkbox' id='st1' $st1 name='statistika' class='key' /><label for='st1'>Показывать БЛОК <B>'Дни рождения, кол-во тем/сообщений, 10 новых сообщений'</B></label></div></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='qt1' $qt1 name='quikchat' class='key' /><label for='qt1'>Показывать БЛОК <B>'Мини-чат на главной'</B></label></div></td></tr>
<tr><td colspan=2 class=row2><div><input type='checkbox' id='ob1' $ob1 name='onlineb' class='key' /><label for='ob1'>Показывать БЛОК <B>'Кто сейчас находится на форуме'</B></label></div></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='sb1' $sb1 name='specblok1' class='key' /><label for='sb1'>Показывать БЛОК <B>'15-и самых больших по количеству сообщений тем'</B></label></div></td></tr>
<tr><td colspan=2 class=row2><div><input type='checkbox' id='bs1' $bs1 name='specblok2' class='key' /><label for='bs1'>Показывать БЛОК <B>'10 самых активных пользователей'</B></label></div></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='rk1' $rk1 name='reklama' class='key' /><label for='rk1'>Показывать БЛОК <B>'Реклама и/или объявление'<B></label></div></td></tr>
<tr><td class=row2>Заголовок БЛОКА 'Реклама и/или объявление'</td><td class=row2><input type=text value='$reklamatitle' name=reklamatitle class=post maxlength=50 size=50></td></tr>
<tr><td class=row1>Текст БЛОКА 'Реклама и/или объявление' показывается в верхней части всех страниц с темами.<br><br> <small>можно использовать теги, но двойные кавычки нельзя (будут заменены на одинарные). Не более 1000 символов.</small></td><td class=row1><textarea cols=50 rows=6 size=500 class=post name=\"reklamatext\">$reklamatext</textarea></td></tr>

<tr><td align=center colspan=2 class=row2><b>Настройки мини-чата</b></td></tr>
<tr><td class=row1>Максимум символов в сообщении</td><td class=row1><input type=text value='$chatmaxmsg' class=post maxlength=4 name='chatmaxmsg' size=7></td></tr>
<tr><td class=row2>Частота обновления чата, секунд </td><td class=row2><input type=text value='$chatrefresh' class=post maxlength=2 name='chatrefresh' size=7></td></tr>
<tr><td class=row1>Количество видимых сообщений</td><td class=row1><input type=text value='$chatmsg_onpage' class=post maxlength=2 name='chatmsg_onpage' size=7></td></tr>
<tr><td class=row2>Длина строки ввода сообщения, пунктов</td><td class=row2><input type=text value='$chatinput' class=post maxlength=3 name='chatinput' size=7></td></tr>
<tr><td class=row1>Длина фрейма чата, пикселей</td><td class=row1><input type=text value='$chatframesize' class=post maxlength=3 name='chatframesize' size=7></td></tr>

<tr><td align=center colspan=2 class=row2><b>Настройки голосования</b></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='ip1' $ip1 name='ipblok' class='key' /><label for='ip1'>Запретить голосовать повторно с одного IP</label></div></td></tr>
<tr><td class=row2><div><input type='checkbox' id='af1' $af1 name='antiflud' class='key' /><label for='af1'>Задействовать антифлуд</label></div></td><td class=row2>защитное время: <input type=text value='10' class=post maxlength=3 name='fludtime' size=7> секунд</td></tr>

<tr><td align=center colspan=2 class=row1><b>Настройки статусов (званий)</b></td></tr>
<tr><td class=row2>Как называть участников НЕ зареганых / зареганых</td><td class=row2><input type=text value='$guest_name' class=post maxlength=25 name=newguest_name size=22> &nbsp;/ &nbsp;<input type=text value='$user_name' class=post maxlength=25 name=newuser_name size=22></td></tr>";
for($k=0; $k<8; $k++) print"<tr><td align=right class=row1>Репутация от <input type=text value='$userrepa[$k]' class=post maxlength=4 name='repa[$k]' size=7>&nbsp;</td><td class=row1>статус <input type=text value='$userstatus[$k]' class=post maxlength=26 name='status[$k]' size=40></td></tr>";

print"<tr><td align=center colspan=2 class=row2><b>Прочие настройки</b></td></tr>
<tr><td class=row1>Скин форума</td><td class=row1><select class=input name=forum_skin>$skin</select></td></tr>
<tr><td class=row2>Папка с данными форума</td><td class=row2><input type=text value='$datadir' class=post maxlength=20 name='datadir' size=10> &nbsp;&nbsp; По умолчанию - <B>./data</B></td></tr>
<tr><td class=row1>Максимальный размер аватара в байтах</td><td class=row1><input type=text value='$max_f_size' class=post maxlength=6 name='max_f_size' size=10></td></tr>

<tr><td colspan=2 class=row2><div><input type='checkbox' id='cs1' $cs1 name='can_up_file' class='key' /><label for='cs1'>Разрешить загрузку файлов только зарегистрированным или никому?</label></div></td></tr>

<tr><td class=row1>Папка для загрузки файлов</td><td class=row1><input type=text value='$filedir' class=post maxlength=20 name='filedir' size=10> &nbsp;&nbsp; По умолчанию - <B>./load</B></td></tr>
<tr><td class=row2>Максимальный размер файла в байтах</td><td class=row2><input type=text value='$max_upfile_size' class=post maxlength=7 name='max_upfile_size' size=10></td></tr>

<tr><td class=row1>Смещение GMT относительно времени хостинга</td><td class=row1><input class=post type=text value='$delta_gmt' maxlength=2 name=delta_gmt size=15> (GMT + XX часов)</td></tr>

<tr><td align=center colspan=2 class=row2><b>Смайлы</b></td></tr>
<tr><td colspan=2 class=row1><div><input type='checkbox' id='sm1' $sm1 name='showsmiles' class='key' /><label for='sm1'>Включить / отключить графические смайлы?</label></div></td></tr>
<tr><td colspan=2 class=row2 align=center>";

if (isset($smiles)) {$i=count($smiles);
for($k=0; $k<$i; $k=$k+2) { $j=$k+1; if ($k!=($i-1) and is_file("smile/$smiles[$k].gif"))
print"<img src='smile/$smiles[$k].gif' border=0> <input type=hidden name=newsmiles[$k] value='$smiles[$k]' maxlenght=6><input type=text value='$smiles[$j]' maxlength=15 name=newsmiles[$j] size=4> "; } }


echo'</td></tr></table><BR><center><input type=submit class=mainoption value="Сохранить конфигурацию"></form>
</td></tr></table>
<br>* Если хотите изменить пароль - сотрите слово <B>"скрыт"</B> и введите новый пароль.<br> Рекомендую использовать только английские буквы и/или цифры. У некоторых хостеров есть проблемы<br> с регинальнальными настройками и пароль, набранный на другом языке может сохранится некорректно.<br>';
}






// ПРОСМОТР ВСЕХ УЧАСТНИКОВ форума
if ($_GET['event']=="userwho") {
$t1="row1"; $t2="row2"; $error=0;
$userlines=file("$datadir/user.php");
$ui=count($userlines)-1; $maxi=$ui; $first=0; $last=$ui+1;

$statlines=file("$datadir/userstat.csv"); $si=count($statlines)-1;
if ($si=="0") exit("<h1 align=center>НИОДНОГО участника не зарегистрировано!</h1>");

print"<center>Действия над участниками: &#9733; <B><a href='admin.php?newstatistik' class=mainmenu>Пересчитать статистику</a>
&#9733; <a href='admin.php?event=massmail' class=mainmenu>Автоматическая рассылка</a> ";
echo'&#9733; <a href="?delalluser=yes" title="УДАЛИТЬ" onclick="return confirm(\'Будут удалены ВСЕ НЕ АКТИВИРОВАННЫЕ УЧЁТНЫЕ ЗАПИСИ! Удалить? Уверены?\')">Удалить НЕ АКТИВИРОВАННЫХ</a> &#9733;</B><br></center>';

$bada="<center><font color=red><B>В файле статистики имеются ошибки! ПЕРЕСЧИТАЙТЕ статистику участников!!!</B></font></center><br>";

if ($si!=$ui) print"$bada";

if (isset($_GET['page'])) $page=$_GET['page']; else $page="1";
if (!ctype_digit($page)) $page=1; // защита
if ($page=="0") $page="1"; else $page=abs($page); 
$maxpage=ceil(($ui+1)/$uq); if ($page>$maxpage) $page=$maxpage;

// формируем переменную $pageinfo - со СПИСКОМ СТРАНИЦ
$pageinfo=""; $addpage=""; $maxpage=ceil(($maxi+1)/$uq); if ($page>$maxpage) $page=$maxpage;
$pageinfo.="<div style='padding:6px;' class=pgbutt>Страницы: &nbsp;";
if ($page>3 and $maxpage>5) $pageinfo.="<a href=admin.php?event=userwho>1</a> ... ";
$f1=$page+2; $f2=abs($page-2); if ($f2=="0") $f2=1; if ($page>=$maxpage-1) $f1=$maxpage;
if ($maxpage<=5) {$f1=$maxpage; $f2=1;}
for($i=$f2; $i<=$f1; $i++) { if ($page==$i) $pageinfo.="<B>$i</B> &nbsp;"; 
else {if ($i!=1) $addpage="&page=$i"; $pageinfo.="<a href=admin.php?event=userwho&page=$i>$i</a> &nbsp;";} }
if ($page<=$maxpage-3 and $maxpage>5) $pageinfo.="... <a href=admin.php?event=userwho&page=$maxpage>$maxpage</a>";
$pageinfo.='</div>';

$i=1+$uq*($page-1); if ($i>$ui) $i=$ui-$uq;
$lm=$i+$uq; if ($lm>$ui) $lm=$ui+1;

print"$pageinfo";
echo'<table width=100% valign=top cellpadding=0 cellspacing=0><TR><TD>

<table valign=top width=100% cellpadding=0 cellspacing=0 class=forumline><tr> 
<th class=thCornerL height=30 nowrap=nowrap>№</th>
<th class=thCornerL width=110>Имя</th>
<th class=thCornerR>Пол</th>
<th class=thTop>Дата рег-ии</th>
<th class=thTop>Емайл / Сменить пароль</th>
<th class=thTop>Тем</th>
<th class=thTop>Сообщ.</th>
<th class=thTop>Репутация</th>
<th class=thTop>Штрафы</th>
<th class=thTop>Статус / Изменить</th>
<th class=thTop>Звёзд</th></tr>';

$delblok="<FORM action='admin.php?usersdelete=$last&page=$page' method=POST name=delform>
<td colspan=8 class=$t1>
<table valign=top cellpadding=0 cellspacing=0 class=forumline width=25><th height=30 class=thCornerL>.X.</th>";

do {$tdt=explode("|",$userlines[$i]); $i++; $npp=$i-1;

if (isset($statlines[$i-1])) {$sdt=explode("|",$statlines[$i-1]);} else {$sdt[0]=""; $sdt[1]="-"; $sdt[2]="-"; $sdt[3]="-"; $sdt[4]="-";}
// Проверяем, если файл статистики повреждён - пишем сообщение о необходимости восстановить его
if ($sdt[0]!=$tdt[0]) {$error++; $sdt[1]="-"; $sdt[2]="-"; $sdt[3]="-"; $sdt[4]="-";}
if ($tdt[6]==TRUE) $tdt[6]="<font color=blue>М</font>"; else $tdt[6]="<font color=red>Ж</font>";
if ($tdt[16]==FALSE) $tdt[16]=$user_name;
$tdt[1]=date("d.m.y - H:i",$tdt[1]);

$delblok.="<TR height=35><td width=10 bgcolor=#FF6C6C><input type=checkbox name='del$npp' value=''"; if (isset($_GET['chekall'])) {$delblok.='CHECKED';} $delblok.="></td></TR>";
print"<tr height=35>
<td class=$t1>$npp</td>
<td class=$t1><B><a href=\"admin.php?event=profile&pname=$tdt[2]\">$tdt[2]</a></td>";
if ($tdt[16]==FALSE) {
print"<td class=$t1 colspan=9><B>[<a href='admin.php?event=activate&email=$tdt[5]&key=$tdt[16]&page=$page'>Активировать</a>]. Учётная запись не активирована с $tdt[1]. </B>
(емайл: <B>$tdt[5]</B> ключ: <B>$tdt[16]</B>)"; 
} else {

print"</td><td class=$t1 align=center><B>$tdt[6]</b></td><td class=$t1 align=center>$tdt[1]</td><td class=$t1>
<form action='admin.php?newuserpass&email=$tdt[5]' method=post><a href=\"mailto:$tdt[5]\">$tdt[5]</a> <input type=text class=post name=newpass value='' size=7 maxlength=20><input type=submit name=submit value='ок' class=mainoption></td></form>
<td class=$t1>$sdt[5]</TD>
<td class=$t1>$sdt[6]</TD>
<td class=$t1><form action='admin.php?newrepa&page=$page' method=post><input type=text class=post name=repa value='$sdt[7]' size=3 maxlength=4><input type=hidden name=usernum value='$i'><input type=submit name=submit value='OK' class=mainoption></td></form>
<td class=$t1 width=88 align=center><form action='admin.php?userstatus&page=$page' method=post><input type=hidden name=usernum value='$i'><input type=hidden name=status value='$sdt[8]'><input type=submit name=submit value='-1' style='width: 30px'>&nbsp; <B>$sdt[8]</B>&nbsp; <input type=submit name=submit value='+1' style='width: 30px'></TD></form>
<td class=$t1><form action='admin.php?newstatus=$i&page=$page' method=post><input type=text class=post name=status value='$sdt[9]' size=16 maxlength=20><input type=submit name=submit value='OK' class=mainoption></td></form>
<td class=$t1><form action='admin.php?newreiting=$i&page=$page' method=post><input type=text class=post name=reiting value='$tdt[4]' size=1 maxlength=1><input type=submit name=submit value='OK' class=mainoption></td></form>
</tr>";}

$t3=$t2; $t2=$t1; $t1=$t3;
} while ($i<$lm);

print"</table>
</TD><TD rowspan=20>


$delblok</table></TR></TABLE><br>
<div align=right><input type=hidden name=first value='$first'><input type=hidden name=last value='$last'><INPUT type=submit class=mainoption value='УДАЛИТЬ выбранных пользователей'></FORM>
&nbsp; <FORM action='admin.php?event=userwho&page=$page&chekall' method=POST name=delform><INPUT class=mainoption type=submit value='Пометить ВСЕХ'></FORM>
&nbsp; <FORM action='admin.php?event=userwho&page=$page' method=POST name=delform><INPUT class=mainoption type=submit value='СНЯТЬ пометку'></FORM></div>";

print "$pageinfo
<div align=right>Всего зарегистрировано участников - <B>$ui</B></div>
</TD></TR></TABLE><br>

<UL>Пересортировать участников по: 
<form action='admin.php?event=sortusers' method=post name=REPLIER>
<SELECT name=kaksort>
<OPTION selected value=5>Дате регистрации (новые - внизу)</OPTION>
<OPTION value=1>Имени</OPTION>
<OPTION value=2>Кол-ву сообщений (сколько участник оставил сообщений)</OPTION>
<OPTION value=3>Кол-ву звёзд</OPTION>
<OPTION value=4>Репутации</OPTION>
<OPTION value=6>Активности (кол-во сообщений в сутки / кол-во дней с даты регистрации)</OPTION></SELECT>
<input type=submit class=mainoption value=' Пересортировать '> &nbsp; (сортировать лучше когда с форумом никто из участников не работает)
<br><br>";


if ($error>0) print"$bada";

echo'* Репутация - "Авторитетность" пользователя. 0 - 9999 ед. Автоматически увеличивается при добавлении сообщения/темы;<br><br>
ШТРАФЫ (система штрафов ещё настраивается. Будет доступна в следующей версии):<br>
0 - юзер может всё;<br>
1 - юзеру антифлуд увеличиваем до 60 секунд;<br>
2 - юзер не имеет права менять репу другим;<br>
+3 РАБОТАЕТ - юзеру запрещаем создавать темы на 1 месяц;<br>
+4 РАБОТАЕТ - блокируем доступ к ответу в темах на 1 месяц - только просмотр;<br>
5 - БАН на 1 месяц!<br></UL>';
}
}




if (isset($_GET['event'])) { if ($_GET['event']=="blockip") { // - БЛОКИРОВКА по IP

$itogo=0;
if (is_file("$datadir/ipblock.csv")) { $lines=file("$datadir/ipblock.csv"); $i=count($lines)-1; $itogo=$i;
if ($i>0) { echo'<h1 align=center>Блокировка по IP-адресу</h1><table width=100% border=0 cellpadding=1 cellspacing=0><TR><TD>
<table border=0 width=100% cellpadding=2 cellspacing=1 class=forumline><tr> 
<th class=thCornerL width=50 height=25 nowrap=nowrap>.X.</th>
<th class=thCornerL >Дата блокировки</th><th class=thCornerL >Дата разблокировки</th>
<th class=thCornerL width=150>IP</th><th class=thCornerL >Тип блокировки</th><th class=thCornerL >Формулировка</th></tr>';

//FROM_TIME|TO_TIME|IP|LOCK|MSG|REZERVED|
do {$dt=explode("|", $lines[$i]);
$dt[0]=date("d.m.Y - H:i",$dt[0]); $dt[1]=date("d.m.Y - H:i",$dt[1]);
if ($dt[3]==FALSE) $dt[3]="<font color=green><B>Запись</B></font>"; else $dt[3]="<font color=red><B>Чтение и запись</B></font>";
print"<TR bgcolor=#F7F7F7 align=center><td width=10 align=center><table><tr><td width=10 bgcolor=#FF2244><B><a href='admin.php?delip=$i'>.X.</a></B></td></tr></table></td>
<td>$dt[0]</td><td>$dt[1]</td><td>$dt[2]</td><td>$dt[3]</td><td>$dt[4]</td></tr>";
$i--; } while($i>0);
} else echo'<br><br><H2 align=center>Заблокированные IP-адреса отсутствуют</H2><br>';
} else echo'<br><br><H2 align=center>Заблокированные IP-адреса отсутствуют</H2><br>';

print"</table><br><CENTER><form action='admin.php?badip' method=POST>
Добавь IP НЕдруга! &nbsp; <input type=text style='WIDTH: 110px' maxlength=15 name=ip> Формулировка: 
<input type=text style='WIDTH: 430px' maxlength=50 name=text 
value=' За добавление нежелательных сообщений на форум! ЗА СПАМ!'> 
на <input type=text style='WIDTH: 30px' maxlength=2 name=to_time value='1'> месяц.
<input type=submit value=' добавить '></form><br><br>*вводите IP аккуратно, не ставьте лишних ноликов и всяких пробелов.
<br><BR>Всего заБАНено пользователей - <B>$itogo</B><BR></td></tr></table>
<a href='admin.php?delfile=ipblock' title='УДАЛИТЬ ФАЙЛ БЛОКИРОВОК' onclick=\"return confirm('Будет удалён файл со всеми заБАНнеными IP! Удалить? Уверены?')\" >Очистить файл блокировки</a></B>
<br>* Модуль в процессе написания. Сейчас работает только блокировка на запись до указанной даты!"; exit;}}

















if (isset($_GET['event'])) {
if ($_GET['event']=="profile") { // РЕДАКТИРОВАНИЕ ПРОФИЛЯ юзера

// функция используется для отображения аватаров
function get_dir($path='./', $mask='*.php', $mode=GLOB_NOSORT) {
 if ( version_compare( phpversion(), '4.3.0', '>=' ) ) {if ( chdir($path) ) {$temp=glob($mask,$mode); return $temp;}}
return false;}

if (!isset($_GET['pname'])) exit("Попытка взлома.");
$pname=urldecode($_GET['pname']); // РАСКОДИРУЕМ имя пользователя, пришедшее из GET-запроса.
$lines=file("$datadir/user.php");
$i=count($lines); $use="0"; $userpn="0";
do {$i--; $rdt=explode("|",$lines[$i]);

if (isset($rdt[1])) { // Если нет потерянных строк в скрипте (пустая строка)
if ($rdt[16]==FALSE) $rdt[16]="<B><font color=red>ожидание активации</font></B>";
if ($pname===$rdt[2]) { $userpn=$i;

$jfile="$datadir/userstat.csv"; $jlines=file("$jfile"); $uj=count($jlines)-1; $msjitogo=0;
for ($j=0;$j<=$uj;$j++) {$udt=explode("|",$jlines[$j]); $msjitogo=$msjitogo+$udt[6]; if ($udt[2]==$rdt[2]) {$msguser=$udt[6]; $temaded=$udt[5]; $repa=$udt[7];}}
$msgaktiv=round(10000*$msguser/$msjitogo)/100;

if($rdt[6]==TRUE) $rdt[6]="Мужчина"; else $rdt[6]="Женщина";

$aktiv=$rdt[1]; $tekdt=time(); $aktiv=round(($tekdt-$aktiv)/86400);
if ($aktiv<=0) $aktiv=1; $aktiv=round(100*$msguser/$aktiv)/100;
$rdt[1]=date("d.m.y - H:i",$rdt[1]);

if (mb_strlen($rdt[13])<2) $rdt[13]=$user_name;

print "<center><br>
<table cellpadding=3 cellspacing=1 width=100% class=forumline>
<tr><th class=thHead colspan=2 height=25 valign=middle>Регистрационные данные ПОЛЬЗОВАТЕЛЯ $pname</th></tr>
<tr><td class=row2 colspan=2><span class=gensmall>Поля отмеченные * обязательны к заполнению, если не указано обратное</span></td></tr>
<tr><td class=row1 width=35%><span class=gen>Имя участника:</span></td><td class=row2><span class=nav>$rdt[2]</span></td></tr>
<tr><td class=row1><span class=gen>Репутация: </span><br></td><td class=row2><B>$repa</B> [<A href='#1' onclick=\"window.open('tools.php?event=repa&name=$rdt[2]&who=$userpn','repa','width=600,height=600,left=50,top=50,scrollbars=yes')\">Оценить &#177;</A>]</td></tr>
<tr><td class=row1><span class=gen>Дата регистрации:</span></td><td class=row2><span class=gen>$rdt[1]</td></tr>
<tr><td class=row1><span class=gen>Пол:</span><br></td><td class=row2><span class=gen>$rdt[6]</span><input type=hidden value='$rdt[6]' name=pol></td></tr>
<tr><td class=row1><span class=gen>Отправить личное сообщение на e-mail: </span><br></td><td class=row2><form method='post' action='tools.php?event=mailto' target='email' onclick=\"window.open('tools.php?event=mailto','email','width=600,height=350,left=100,top=100');return true;\"><input type=hidden name='email' value='$rdt[5]'><input type=hidden name='name' value='$rdt[2]'><input type=hidden name='id' value=''>ЛС</form></td></tr>
<tr><td class=row1><span class=gen>Написать персональное сообщение (сюда на форум):</span><br></td><td class=row2><form action='pm.php?id=$rdt[2]' method=POST name=citata><input type=image border=0 src='data-pm/pm.gif' alt='Отправить ПЕРСОНАЛЬНОЕ СООБЩЕНИЕ'></form></span></td></tr>
<tr><td class=row1><span class=gen>Активность:</span></td><td class=row2><span class=gen>Тем создано: <B>$temaded</B>, всего сообщений: <B>$msguser</B> [<B>$msgaktiv%</B> от общего числа / <B>$aktiv</B> сообщений в сутки]</span></td></tr>
<tr><td class=row1><span class=gen>Статус:</span></td><td class=row2><span class=gen>$rdt[13] (НУЖНО ИСПРАВИТЬ!)</span></td></tr>

<form action='tools.php?event=reregist' name=creator method=post enctype=multipart/form-data>
<tr><td class=row1><span class=gen>Сменить пароль: *</span></td><td class=row2><input class=inputmenu type=text value='скрыт' maxlength=10 name=newpassword size=15><input type=hidden class=inputmenu value='$rdt[3]' name=pass>(если хотите сменить, то введите новый пароль, иначе оставьте как есть!)</td></tr>
<tr><td class=row1><span class=gen>Адрес e-mail: *</span><br><span class=gensmall>Введите существующий электронный адрес! Форум защищён от роботов-спамеров.</span></td><td class=row2><input type=text class=post style='width: 200px' value='$rdt[5]' name=email size=25 maxlength=50></td></tr>
<tr><td class=row1><span class=gen>День варенья:</span><br><span class=gensmall>Введите день рождения в формате: ДД.ММ.ГГГГГ, если не секрет.</span></td><td class=row2><input type=text name=dayx value='$rdt[7]' class=post style='width: 100px' size=10 maxlength=18></td></tr>
<tr><td class=row1><span class=gen>Номер в ICQ:</span><br><span class=gensmall>Введите номер ICQ, если он у Вас есть.</span></td><td class=row2><input type=text value='$rdt[10]' name=icq class=post style='width: 100px' size=10 maxlength=10></td></tr>
<tr><td class=row1><span class=gen>Домашняя страничка:</span><br></td><td class=row2><input type=text value='$rdt[11]' class=post style='width: 200px' name=www size=25 maxlength=70 value='https://' /></td></tr>
<tr><td class=row1><span class=gen>Откуда:</span><br><span class=gensmall>Введите место жительства (Страна, Область, Город).</span></td><td class=row2><input type=text class=post style='width: 250px' value='$rdt[12]' name=about size=25 maxlength=70></td></tr>
<tr><td class=row1><span class=gen>Интересы:</span><br><span class=gensmall>Вы можете написать о ваших интересах</span></td><td class=row2><input type=text class=post style='width: 300px' value='$rdt[13]' name=work size=35 maxlength=70></td></tr>
<tr><td class=row1><span class=gen>Подпись:</span><br><span class=gensmall>Введите Вашу подпись, не используйте HTML</span></td><td class=row2><input type=text class=post style='width: 400px' value='$rdt[14]' name=write size=35 maxlength=70></td></tr>
<tr><td class=row1><span class=gen>Аватар:</span><br><span class=gensmall></span></td><td class=row2>";
if (!is_file("avatars/$rdt[15]")) print"<img src='./avatars/noavatar.gif'>"; else print"<img src='./avatars/$rdt[15]'>";
print "<input type=hidden name=name value='$rdt[2]'><input type=hidden name=oldpass value='$rdt[3]'>
<input type=hidden name=file value=''><input type=hidden name=avatar value='$rdt[15]'>
</td></tr><tr><td class=catBottom colspan=2 align=center height=28><input type=submit name=submit value='Сохранить изменения' class=mainoption /></td>
</tr></table></form>"; $use="1"; $i=1;
}
} // if
} while($i > "1");

if ($use!="1") { // в БД такого ЮЗЕРА НЕТ
echo'<center><table width=600 height=300 class=forumline><tr><th class=thHead height=25 valign=middle>Пользователь НЕ ЗАРЕГИСТРИРОВАН</th></tr>
<tr><td class=row1 align=center><B>Уважаемый администратор!</B><BR><BR>Извините, но участник с таким - <B>логином на форуме не зарегистрирован.</B><BR><BR>
Скорее всего, <B>он был уже удалён или Вы перешли по ошибочной ссылке.</B>.<BR><BR>
<B>Посмотреть других участников</B> можно <B><a href="admin.php?event=who">здесь</a>.</B><br><br></TD></TR></TABLE>'; }
}
} // if (isset($_GET['event'])) {













if (isset($_GET['event'])) { if ($_GET['event']=="seebasa") {

print"<html><body><form action='admin.php?event=seebasa&see' method=POST name=REPLIER>
<table align=center><tr><td class=row2>Просмотреть содержимое файла: </td><td class=row1><select class=input name=openfile>";


if ($handle=opendir($datadir)) {
while (($file=readdir($handle)) !== false)
if (!is_dir($file)) { 
$stroka=stristr($file, ".csv"); if (mb_strlen($stroka)>"1") 
{ $marker=""; if (isset($_POST['openfile'])) { if ($_POST['openfile']===$file) $marker="selected"; }
print"<option $marker value=\"$file\">$file</option>"; }
} closedir($handle); } else echo'Ошибка!';

echo'</select></td><td><center><input type=submit value="Просмотреть файл"></form></td></tr></table>';

if (isset($_POST['openfile'])) {

$openfile=$_POST['openfile'];
if (!stristr($openfile, ".csv")) exit("Разрешён просмотр только содержимого базы скрипта (всех файлов с раширением dat!");

$data=File("$datadir/$openfile");

echo "<b><i><h2><center>Содержимое файла \"$datadir/$openfile\"</b></i></h2>
* В первой строке указаны номера по порядку. Если Вам нужно считать в скрипте представленные данные, то здесь Вы можете быстро узнать их порядковый номер!
<table border=0><tr>";

$dat_arr=explode("|",$data[0]);

for ($p=0;$p<count($dat_arr);$p++) echo "<td bgcolor=#04A2FF><center><b>$p</td>";
echo "</tr>";

for ($i=0;$i<count($data);$i++) {
    $data_array=explode("|", $data[$i]);
    echo "<tr>";
    for ($f=0;$f<count($data_array);$f++) echo "<td bgcolor=#AEE1FF><center><b>$data_array[$f] &nbsp;</td>";
    echo "</tr>";
}

echo "</table></center></form>
</body>
</html>";

} // if isset $_GET['openfile']
}}










// МАССОВАЯ рассылка информации УЧАСТНИКам форума
if (isset($_GET['event'])) { if ($_GET['event']=="massmail") {
if (isset($_GET['user'])) $useremail=$_GET['user']; else $useremail="";
if (isset($_GET['autoscribe'])) $autoscribe=$_GET['autoscribe']; else $autoscribe="";
if (($autoscribe=="1") and ($useremail=="")) exit("<br><br><h2>Вернитесь назад! Вам необходимо отредактировать текст, выбрать 1-го пользователи и нажать кнопку 'Сохранить и отправить рассылку'!");

print"<center><TABLE class=forumline cellPadding=2 cellSpacing=1 width=775>
<br><br><FORM action='admin.php?event=rassilochka' method=post>
<TBODY><TR><TD class=thTop align=middle colSpan=2>Введите параметры текста, отправляемого пользователю</TD></TR>

<TR bgColor=#ffffff><TD>&nbsp; Имя отправителя:<FONT color=#ff0000>*</FONT> <INPUT name=name value='Администратор форума ' style='FONT-SIZE: 14px; WIDTH: 240px'>
и E-mail:<FONT color=#ff0000>*</FONT> <INPUT name=email value='$adminemail' style='FONT-SIZE: 14px; WIDTH: 320px'></TD></TR>

<TR bgColor=#ffffff><TD>Получатель: &nbsp; НИК:<FONT color=#ff0000>*</FONT> и E-mail:<FONT color=#ff0000>*</FONT>";

echo'<SELECT name=userdata class=maxiinput><option value="">Выберите участника</option>\r\n';

// Блок считывает всех пользователей из файла
if (is_file("$datadir/user.php")) $lines=file("$datadir/user.php");
if (!isset($lines)) $datasize=0; else $datasize=sizeof($lines)-1;
if ($datasize<=0) exit("$back. Проблемы с Базой пользователей, файл данных пуст.");
$imax=count($lines); $i="1";
do {$dt=explode("|", $lines[$i]);
print "<OPTION $selectnext value=\"$i|$dt[2]|$dt[5]|\">$i / $datasize - $dt[2] &lt;$dt[5]&gt;</OPTION>\r\n";
if ($useremail==$dt[5]) $selectnext="selected"; else $selectnext="";
$i++; } while($i < $imax);

if (is_file("$datadir/mailtext.csv")) $mailtext=file_get_contents("$datadir/mailtext.csv");
echo'</optgroup></SELECT></TD></TR>
<TR bgColor=#ffffff><TD>&nbsp; Сообщение:<FONT color=#ff0000>*</FONT><br>
<TEXTAREA name=msg style="FONT-SIZE: 14px; HEIGHT: 300px; WIDTH: 1000px">'.$mailtext.'
</TEXTAREA></TD></TR>
<TR><TD bgColor=#FFFFFF colspan=2><center><INPUT type=submit value="Сохранить и отправить в ручном режиме">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

if ($autoscribe=="1") { print"<script> 
var Time=2; // время в секундах между рассылкой сообщений участникам
function ButtonClick () {document.getElementById ('submit').click (); setTimeout (ButtonClick, Time * 60 * 1000);}
onload=function () {setTimeout (ButtonClick, Time * 1000)} </script>
<input type=hidden name=autoscribe value='$autoscribe'><input type=\"submit\" value=\"АВТОМАТИЧЕСКАЯ РАССЫЛКА АКТИВИРОВАНА\" id=\"submit\" onclick=\"this.disabled=1\"></FORM>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <FORM action='admin.php?event=massmail' method=post><input type='submit' value='Отменить авторассылку'>
"; } else print"

</form><FORM action='admin.php?event=massmail&user=$useremail&autoscribe=1' method=post>
<style>#autoscribe:checked + label button {color: green;}</style>
<input type='checkbox' id='autoscribe' name='autoscribe' value='1'><label for='autoscribe'><button>Активировать АВТОМАТИЧЕСКУЮ РАССЫЛКУ</button></label>
</FORM>";

echo'</TD></TR></TBODY></TABLE><br><br></center>
* Используйте макроподстановку:<br>
<LI><B>%name</B> - имя участника форума;</LI>
<LI><B>%forum_name</B> - название форума;</LI>
<LI><B>%forum_url</B> - URL-адрес форума;</LI>
<LI><B>%forum_urllogin</B> - URL-адрес страницы входа;</LI>
'; }} // МАССОВАЯ рассылка



?><br>
<center><font size=-2><small>Powered by <a href="https://www.wr-script.ru" title="Скрипт форума" class="copyright">WR-Forum Lite</a> &copy; 2.3 UTF-8<br></small></font></center>
</body>
</html>