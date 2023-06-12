<? // WR-forum Lite v 2.3 UTF-8 //  07.01.2023 г.  //  WR-Script.ru

//error_reporting (E_ALL);
error_reporting(0); // РАЗКОМЕНТИРУЙТЕ для постоянной работы
ini_set('register_globals','off');// Все скрипты написаны для этой настройки php

include "data/config.php";


// оставить только поиск здесь
// простая регистрация и авторизация (без мыл)
// 



// Определяем URL форума 11-11-2018 поддержка http / https
$url="http".(($_SERVER['SERVER_PORT']==443)?"s":"")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; $fu=explode('tools.php', $url);$forum_url=$fu[0];

// Функция содержит ПРОДОЛЖЕНИЕ ШАПКИ. Вызывается: addtop();
function addtop() { global $wrfname,$forum_skin,$date,$time;

// ищем В КУКАХ wrfcookies чтобы вывести ИМЯ
if (isset($_COOKIE['wrfcookies'])) {$wrfc=$_COOKIE['wrfcookies']; $wrfc=htmlspecialchars($wrfc,ENT_COMPAT,"UTF-8"); $wrfc=stripslashes($wrfc); $wrfc=explode("|", $wrfc); $wrfname=$wrfc[0];} else {$wrfname=null; $wrfpass=null;}

echo'<TD align=right>';

if ($wrfname!=null) {
$codename=urlencode($wrfname); // Кодируем имя в СПЕЦФОРМАТ, для поддержки корректной передачи имени через GET-запрос.
print "<a href='tools.php?event=profile&pname=$codename' class=mainmenu>Ваш профиль</a>&nbsp;&nbsp;<a href='index.php?event=clearcooke' class=mainmenu>Выход [<B>$wrfname</B>]</a>";}

else {print "<span class=mainmenu>
<a href='tools.php?event=reg' class=mainmenu>Регистрация</a>&nbsp;&nbsp;
<a href='tools.php?event=login' class=mainmenu> Вход</a></td>";}

if (is_file("data/tiptop.html")) include("data/tiptop.html"); // подключаем дополнение к ВЕРХУШКе

print"</span></td></tr></table></td></tr></table><span class=gensmall>Сегодня: $date - $time";
return true;}


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


function unreplacer($text) { // ФУНКЦИЯ замены спецсимволов конца строки на обычные
$text=str_replace("&lt;br&gt;","<br>",$text); return $text;}


function nospam() { global $max_key,$rand_key,$antispam2k,$antispam2kv; // Функция АНТИСПАМ 2011+2012
if (array_key_exists("image", $_REQUEST)) { $num=replacer($_REQUEST["image"]);
for ($i=0; $i<10; $i++) {if (md5("$i+$rand_key")==$num) {imgwr($st,$i); die();}} }
$xkey=""; mt_srand(time()+(double)microtime()*1000000);
$dopkod=mktime(0,0,0,date("m"),date("d"),date("Y")); // доп.код: меняется каждые 24 часа
$stime=md5("$dopkod+$rand_key");// доп.код
echo'<noindex><table cellspacing=0 cellpadding=0><tr height=30><TD>Защитный код:</TD>';
$nummax=0; for ($i=0; $i<=$max_key; $i++) {
$snum[$i]=mt_rand(0,9); $psnum=md5($snum[$i]+$rand_key+$dopkod);
$secret=mt_rand(0,1); $styles='bgcolor=#FFFF00';
if ($nummax<3) { if ($secret==1 or $i==0) {$styles='bgcolor=#77C9FF'; $xkey=$xkey.$snum[$i]; $nummax++;}}
echo "<td width=20 $styles><img src=antispam.php?image=$psnum border=0 alt=''>\n<img src=antispam.php?image=$psnum height=1 width=1 border=0></td>\r\n";}
$xkey=md5("$xkey+$rand_key+$dopkod"); //число + ключ из data/config.php + код меняющийся кажые 24 часа
print"<td><input name='usernum' class=post type='text' maxlength=$nummax size=6> (введите цифры, которые на <font style='font-weight:bold'> голубом фоне</font>)
<input name=xkey type=hidden value='$xkey'>
<input name=stime type=hidden value='$stime'>
</td></tr></table></noindex>";
if ($antispam2k==TRUE) print"Ответ на вопрос: <input name='antispam2ko' class=post type='text' maxlength=20 size=10>($antispam2kv)";
return; }









// ВСЁ, что делается при наличии переменной $_GET['event']
if(isset($_GET['event'])) {



if ($_GET['event']=="login") { // ВХОД на форум УЧАСТНИКОМ
$frname="Вход на форум .:. "; $frtname="";
include("data/top.html"); addtop(); // подключаем ШАПКУ форума

echo '<BR><BR><BR><BR><center>
<table bgcolor=navy cellSpacing=1><TR><TD class=row2>
<TABLE class=bakfon cellPadding=4 cellSpacing=1>

<FORM action="tools.php?event=regenter" method=post>
<TR class=toptable><TD align=middle colSpan=2><B>Вход на форум</B></TD></TR>
<TR class=row1><TD>Имя:</TD><TD><INPUT name=name class=post></TD></TR>
<TR class=row2><TD>Пароль:</TD><TD><INPUT type=password name=pass class=post></TD></TR>
<TR class=row1><TD colspan=2><center><INPUT type=submit class=button value=Войти></TD></TR></TABLE></FORM> </TD></TR></TABLE>
<BR><BR><BR>
<table bgcolor=navy cellSpacing=1><TR><TD class=row2>
<TABLE class=bakfon cellPadding=3 cellSpacing=1>
<FORM action="tools.php?event=givmepassword" method=post>
<TR class=toptable><TD align=middle colSpan=3><B>Забыли пароль? Введите на выбор:</B></TD></TR>
<TR class=row1><TD><B>Ваш Емайл:</B> <font color=red>*</font></TD><TD><INPUT name=myemail class=post style="width: 170px"></TD>
<TR class=row1><TD><B>Имя (Ник):</B></TD><TD><INPUT name=myname class=post style="width: 170px"></TD></TR>
<TR><TD colspan=2 align=center><INPUT type=submit class=button style="width:150" value="Сделать запрос"></TD></TR>
<TR><TD colspan=3><small><font color=red>*</font> На Ваш электронный адрес будет выслана<br> информация для восстановления учётной записи.</TD></TR></TABLE>
</FORM></TD></TR></TABLE><BR><BR><BR><BR><BR>
</TD></TR></TABLE>
</TD></TR></TABLE>'; exit;}






// ОТПРАВКА СООБЩЕНИЯ юзеру
if ($_GET['event']=="mailto") {

if ($sendmail!=TRUE) exit("$back. <center><B>Извините, но функция отправки писем ЗАБЛОКИРОВАНА администратором!<BR><BR><BR><a href='' onClick='self.close()'>Закрыть окно</b></a></center>");

if (!isset($_POST['email'])) exit("Нет данных переменной email.");
if (!isset($_POST['name'])) exit("Нет данных переменной name.");
$uemail=replacer($_POST['email']); $uname=replacer($_POST['name']);
$id=""; $fid=""; if (isset($_POST['id'])) {$id=replacer($_POST['id']); if (strlen($id)>0) $fid=substr($id,0,3);}

print "<html><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8'><meta http-equiv='Content-Language' content='ru'>
<meta name=\"Robots\" content=\"noindex,nofollow\">
<title>Отправление сообщения автору объявления</title></head><body topMargin=5>
<center><TABLE bgColor=#aaaaaa cellPadding=2 cellSpacing=1 width=98%>
<FORM action='tools.php?event=mailtogo' method=post>
<TBODY><TR><TD align=middle bgColor=#cccccc colSpan=2>Получатель сообщения: <B>$uname</B></TD></TR>

<TR bgColor=#ffffff><TD>&nbsp; Ваше Имя:<FONT color=#ff0000>*</FONT> <INPUT name=name style='FONT-SIZE: 14px; WIDTH: 150px'>

и E-mail:<FONT color=#ff0000>*</FONT> <INPUT name=email style='FONT-SIZE: 14px; WIDTH: 180px'></TD></TR>

<TR bgColor=#ffffff><TD>&nbsp; Сообщение:<FONT color=#ff0000>*</FONT><br>
<TEXTAREA name=msg style='FONT-SIZE: 14px; HEIGHT: 150px; WIDTH: 494px'></TEXTAREA></TD></TR>
<INPUT type=hidden name=uemail value=$uemail><INPUT type=hidden name=uname value=$uname>
<TR bgColor=#ffffff><TD>";

if ($antispam==TRUE and !isset($wrfname)) nospam(); // АНТИСПАМ !

if ($id!="") print"<INPUT type=hidden name=id value=$id><INPUT type=hidden name=fid value=$fid>";

echo'<TR><TD bgColor=#FFFFFF colspan=2><center><INPUT type=submit value=Отправить></TD></TR></TBODY></TABLE></FORM>'; 
exit; }


// ШАГ 2 отправки сообщения пользователю
if ($_GET['event']=="mailtogo") {
$name=replacer($_POST['name']);
$email=replacer($_POST['email']);
$msg=replacer($_POST['msg']);
if (isset($_POST['fid'])) $fid=replacer($_POST['fid']);
if (isset($_POST['id'])) $id=replacer($_POST['id']);
$uname=replacer($_POST['uname']);
$uemail=replacer($_POST['uemail']);

//--А-Н-Т-И-С-П-А-М--проверка кода--
if ($antispam==TRUE) {
if (!isset($_POST['usernum']) or !isset($_POST['xkey']) or !isset($_POST['stime']) ) exit("данные из формы не поступили!");
$usernum=replacer($_POST['usernum']); $xkey=replacer($_POST['xkey']); $stime=replacer($_POST['stime']);
$dopkod=mktime(0,0,0,date("m"),date("d"),date("Y")); // доп.код. Меняется каждые 24 часа
$usertime=md5("$dopkod+$rand_key");// доп.код
$userkey=md5("$usernum+$rand_key+$dopkod");
if (($usertime!=$stime) or ($userkey!=$xkey)) exit("введён ОШИБОЧНЫЙ код!");}

if (!preg_match('/^([0-9a-zA-Z]([-.w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-w]*[0-9a-zA-Z].)+[a-zA-Z]{2,9})$/si',$email) and strlen($email)>30 and $email!="") exit("$back и введите корректный E-mail адрес!</B></center>");
if (!preg_match('/^([0-9a-zA-Z]([-.w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-w]*[0-9a-zA-Z].)+[a-zA-Z]{2,9})$/si',$uemail) and strlen($uemail)>30 and $uemail!="") exit("$back у пользователя задан несуществующий адрес!</B></center>");
if ($name=="") exit("$back Вы не ввели своё имя!</B></center>");
if ($msg=="") exit("$back Вы не ввели сообщение!</B></center>");

$text="$name|$msg|$uname|$email|";
$text=str_replace("\r\n","<br>",$text);
$exd=explode("|",$text); $name=$exd[0]; $msg=$exd[1]; $uname=$exd[2]; $email=$exd[3];

$headers=null; // Настройки для отправки писем
$headers.="From: $name $email\n";
$headers.="X-Mailer: PHP/".phpversion()."\n";
$headers.="Content-Type: text/html; charset=UTF-8";

// Собираем всю информацию в теле письма

$allmsg="<html><head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'><meta http-equiv='Content-Language' content='ru'>
</head><body>
<BR><BR><center>$uname, это сообщение отправлено вам от посетителя форума <BR><B>$forum_name</B><BR><BR>
<table cellspacing=0 width=700 bgcolor=navy><tr><td><table cellpadding=6 cellspacing=1 width='100%'>
<tr bgcolor=#F7F7F7><td width=130 height=24>Имя</td><td>$name</td></tr>
<tr bgcolor=#F7F7F7><td>E-mail:</td><td><font size='-1'>$email</td></tr>
<tr bgcolor=#F7F7F7><td> Сообщение:</td><td><BR>$msg<BR></td></tr>
<tr bgcolor=#F7F7F7><td>Дата отправки сообщения:</td><td>$time - <B>$date г.</B></td></tr>
<tr bgcolor=#F7F7F7><td>Перейти на главную страницу:</td><td><a href='$forum_url'>$forum_url</a></td></tr>
</table></td></tr></table></center><BR><BR>* Данное письмо сгенерировано и отправлено роботом, отвечать на него не нужно.
</body></html>";

mail("$uemail", "Сообщение от посетителя форума ($forum_name) от $name ", $allmsg, $headers);
exit('<div align=center><BR><BR><BR>Ваше сообщение <B>успешно</B> отправлено.<BR><BR><BR><a href="#" onClick="self.close()"><b>Закрыть окно</b></a></div>'); }




// проверка имени/пароля и вход на форум
if ($_GET['event']=="regenter") {
if (!isset($_POST['name']) & !isset($_POST['pass'])) exit("$back введите имя и пароль!");
$name=str_replace("|","I",$_POST['name']); $pass=replacer($_POST['pass']);
$name=replacer($name); $name=strtolower($name);
if (strlen($name)<1 or strlen($pass)<1) exit("$back Вы не ввели имя или пароль!");

// проходим по всем пользователям и сверяем данные
$lines=file("$datadir/user.php"); $i=count($lines); $regenter=FALSE;
$pass=md5("$pass");
do {$i--; $rdt=explode("|",$lines[$i]);
if (isset($rdt[1])) { // Если строчка НЕ ПУСТА
if ($name===strtolower($rdt[2]) & $pass===$rdt[3]) {
if ($rdt[16]==FALSE) exit("$back. Ваша учётная запись не <a href='tools.php?event=reg3'>активирована</a>. Для активации Вам необходимо перейти по ссылке, которая должна прийти Вам на емайл.");
$regenter=TRUE;
$tektime=time();
$wrfcookies="$rdt[2]|$rdt[3]|$tektime|$tektime|";
setcookie("wrfcookies", $wrfcookies, time()+1728000);
}} // if-ы

} while($i > "1");

if ($regenter==FALSE) exit("$back Ваш данные <B>НЕ верны</B>!</center>");
Header("Location: index.php");
}








// Регистрация НОВЫЙ ШАГ 2!! отправка на мыл подтверждения и сохранение в БД
if ($_GET['event']=="regnxt") {

if (!isset($_POST['name']) & !isset($_POST['pass'])) exit("$back введите имя и пароль!");
$name=str_replace("|","I",$_POST['name']); $pass=str_replace("|","I",$_POST['pass']); $dayreg=$date;
$name=trim($name); // Вырезает ПРОБЕЛьные символы 

if (isset($_POST['email'])) $email=$_POST['email']; else $email="";
$email=strtolower($email);

//--А-Н-Т-И-С-П-А-М--проверка кода--
if ($antispam==TRUE) {
if (!isset($_POST['usernum']) or !isset($_POST['xkey']) or !isset($_POST['stime']) ) exit("данные из формы не поступили!");
$usernum=replacer($_POST['usernum']); $xkey=replacer($_POST['xkey']); $stime=replacer($_POST['stime']);
$dopkod=mktime(0,0,0,date("m"),date("d"),date("Y")); // доп.код. Меняется каждые 24 часа
$usertime=md5("$dopkod+$rand_key");// доп.код
$userkey=md5("$usernum+$rand_key+$dopkod");
if (($usertime!=$stime) or ($userkey!=$xkey)) exit("введён ОШИБОЧНЫЙ код!");

// АНТИСПАМ 2012!
if ($antispam2k==TRUE) { $ao=replacer($_POST['antispam2ko']);
if (strtolower($antispam2ko)!=strtolower($ao) or strlen($ao)<1) exit("введён ошибочный ответ на вопрос!");}
}

if (preg_match("/[^(\\w)|(\\x7F-\\xFF)|(\\-)]/",$name)) exit("$back Ваше имя содержит запрещённые символы. Разрешены русские и английские буквы, цифры и подчёркивание!!.");
if ($name=="" or strlen($name)>$maxname) exit("$back ваше имя пустое, или превышает $maxname символов!</B></center>");
if ($pass=="" or strlen($pass)<1 or strlen($pass)>$maxname) exit("$back Вы не ввели пароль. Пароль не должен быть пустым.</B></center>");
if(!preg_match("/^[a-z0-9\.\-_]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is", $email) or $email=="" or strlen($email)>40) exit("$back и введите корректный E-mail адрес!</B></center>");
if (isset($_POST['pol'])) $pol=$_POST['pol']; else $pol=""; if ($pol!="1") $pol="0";

$email=str_replace("|","I",$email);
$activ="0";
$key=mt_rand(100000,999999); if ($activation==FALSE) { $key=""; $activ="1";} // КОЛДУЕМ рандомный КОД активации? если не требуется - обнуляем

$rn=mt_rand(10000,99999); $tektime=time();
$pass=replacer($pass); $ps=md5("$pass");
$text="$rn|$tektime|$name|$ps|0|$email|$pol||0|||||||$key|$activ|";
$text=replacer($text); $exd=explode("|",$text); $name=$exd[2]; $email=$exd[5];
$ip=$_SERVER['REMOTE_ADDR']; // определяем IP юзера

if ($name===$pass) exit("$back. В целях Вашей безопасности, <B>запрещено равенство имени и пароля!</B>");

// Ищем юзера с таким логином или емайлом
$loginsm=strtolower($name);
$lines=file("$datadir/user.php"); $i=count($lines);
if ($i>"1") { do {$i--; $rdt=explode("|",$lines[$i]); 
$rdt[2]=strtolower($rdt[2]);
if ($rdt[2]===$loginsm) {$bad="1"; $er="логином";}
if ($rdt[5]===$email) {$bad="1"; $er="емайлом";}
} while($i > 1);
if (isset($bad)) exit("$back. Участник с таким <B>$er уже зарегистрирован на форуме</B>!"); }

// отправка пользователю КОДА АКТИВАЦИИ
$headers=null; // Настройки для отправки писем
$headers.="From: robot форума <$adminemail>\n";
$headers.="X-Mailer: PHP/".phpversion()."\n";
$headers.="Content-type: text/plain; charset=UTF-8";

// Собираем всю информацию в теле письма
if ($activation==TRUE) {
$allmsg=$forum_name.' (подтверждение регистрации)'.chr(13).chr(10).
 'Подтвердите регистрациию на форуме, для этого перейдите по ссылке: '.$forum_url.'tools.php?event=reg3&email='.$email.'&key='.$key.chr(13).chr(10).
 'Ваше Имя: '.$name.chr(13).chr(10).
 'Ваш пароль: '.$pass.chr(13).chr(10).
 'Ваш E-mail: '.$email.chr(13).chr(10).
 'Активационный ключ: '.$key.chr(13).chr(10).chr(13).chr(10).
 'Сохраните письмо с паролем или запомните его.'.chr(13).chr(10).
 'Пароли на форуме хранятся в зашифрованном виде, увидеть пароль невозможно.'.chr(13).chr(10).
 'Для восстановления доступа к форуму Вам придётся воспользоваться системой восстановления пароля.'.chr(13).chr(10);
 
} else { $allmsg=$forum_name.' (данные регистрации)'.chr(13).chr(10). 'Вы успешно зарегистрированы на форуме: '.$forum_url.chr(13).chr(10). 'Ваше Имя: '.$name.chr(13).chr(10). 'Ваш пароль: '.$pass.chr(13).chr(10). 'Ваш E-mail: '.$email.chr(13).chr(10); }

// Отправляем письмо майлеру на съедение ;-)
mail("$email", "=?UTF-8?B?" . base64_encode("$forum_name (подтверждение регистрации)") . "?=", $allmsg, $headers);
sleep(1); // пауза 1 секунду, чтобы прошли оба письма
if ($admin_send==TRUE) {mail("$adminemail", "=?UTF-8?B?" . base64_encode("$forum_name (Новый участник)") . "?=", $allmsg, $headers);}

$file=file("$datadir/user.php");
$fp=fopen("$datadir/user.php","a+");
flock ($fp,LOCK_EX);
fputs($fp,"$text\r\n");
fflush ($fp);//очищение файлового буфера
flock ($fp,LOCK_UN);
fclose($fp);

// Записываем строчку с именем в файл со статистикой
$file=file("$datadir/userstat.csv");
$fp=fopen("$datadir/userstat.csv","a+");
flock ($fp,LOCK_EX);
fputs($fp,"$rn||$name|0||0|0|0|0||$ip||\r\n");
fflush ($fp);//очищение файлового буфера
flock ($fp,LOCK_UN);
fclose($fp);


// ЕСЛИ АКТИВАЦИИ НЕ ТРЕБУЕТСЯ, то устанавливаем КУКИ
if ($activation!=TRUE) { $tektime=time(); $wrfcookies="$name|$ps|$tektime|$tektime|"; setcookie("wrfcookies", $wrfcookies, time()+1728000);
print"<html><head><link rel='stylesheet' href='$forum_skin/style.css' type='text/css'></head><body>
<script language='Javascript'>function reload() {location=\"index.php\"}; setTimeout('reload()', 2500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
<B>$name, Вы успешно зарегистрированы</B>.<BR><BR>Через несколько секунд Вы будете автоматически перемещены на главную страницу форума.<BR><BR>
<B><a href='index.php'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>"; exit;}

print"<html><head><link rel='stylesheet' href='$forum_skin/style.css' type='text/css'></head><body>
<script language='Javascript'>function reload() {location=\"tools.php?event=reg3\"}; setTimeout('reload()', 2500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
<B>$name, на указанный Вами емайл был выслан код подтверждения.
Для того чтобы зарегистрироваться - введите его на странице, либо перейдите по ссылке - указанной в письме</B>.<BR><BR>Через несколько секунд Вы будете автоматически перемещены на страницу подтверждения регистрации.<BR><BR>
<B><a href='tools.php?event=reg3'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
exit;
}






// Регистрация ШАГ 3 - ввод ключа либо подтверждение по емайлу
if ($_GET['event']=="reg3") {

if (isset($_GET['email']) and isset($_GET['key'])) {$key=$_GET['key']; $email=$_GET['email'];} else {
$frname=""; $frtname=""; include("data/top.html"); addtop(); // подключаем ШАПКУ форума
exit('<center><span class=maintitle>Подтверждение регистрации*</span><br>
<br><form action="tools.php" method=GET>
<input type=hidden name=event value="reg3">
<table cellpadding=3 cellspacing=1 width=100% class=forumline><tr>
<th class=thHead colspan=2 height=25 valign=middle>Ввод емайла и активационного ключа</th>
</tr><tr><td class=row1><span class=gen>Адрес e-mail:</span><br><span class=gensmall></span></td><td class=row2><input type=text class=post style="width: 200px" name=email size=25 maxlength=50></td>
</tr><tr><td class=row1><span class=gen>Активационный ключ:</span><br><span class=gensmall></span></td><td class=row2><input type=text class=post style="width: 200px" name=key size=25 maxlength=6></td></tr><tr>
<td class=catBottom colspan=2 align=center height=28><input type=submit value="Подтвердить регистрацию" class=mainoption></td>
</tr></table>
* Вы можете либо ввести емайл и ключ, который пришёл по почте, либо перейти по активационной ссылке в письме.
</form>');}

// защиты от взлома по ключу и емайлу
if (strlen($key)<6 or strlen($key)>6 or !ctype_digit($key)) exit("$back. Вы ошиблись при вводе ключа. Ключ может содержать только 6 цифр.");
$email=replacer($email); $email=str_replace("|","I",$email); $email=str_replace("\r\n","<br>",$email);
if (strlen($email)>35) exit("Ошибка при вводе емайла");

// Ищем юзера с таким емайлом и ключом. Если есть - меняем статус на пустое поле.
$fnomer=null; $email=strtolower($email); unset($fnomer); unset($ok);
$lines=file("$datadir/user.php"); $ui=count($lines); $i=$ui;
do {$i--; $rdt=explode("|",$lines[$i]); 
$rdt[5]=strtolower($rdt[5]);
if ($rdt[5]===$email and $rdt[15]===$key) {$name=$rdt[2]; $pass=$rdt[3]; $fnomer=$i;}
if ($rdt[5]===$email and $rdt[16]==TRUE) $ok="1";
} while($i > 1);

if (isset($fnomer)) {
// обновление строки юзера в БД
$i=$ui; $dt=explode("|", $lines[$fnomer]);
$txtdat="$dt[0]|$dt[1]|$dt[2]|$dt[3]|$dt[4]|$dt[5]|$dt[6]|$dt[7]|$dt[8]|$dt[9]|$dt[10]|$dt[11]|$dt[12]|$dt[13]|$dt[14]|noavatar.gif|1|";
$fp=fopen("$datadir/user.php","a+");
flock ($fp,LOCK_EX); 
ftruncate ($fp,0);//УДАЛЯЕМ СОДЕРЖИМОЕ ФАЙЛА
for ($i=0;$i<=(sizeof($lines)-1);$i++) { if ($i==$fnomer) fputs($fp,"$txtdat\r\n"); else fputs($fp,$lines[$i]); }
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);
// устанавливаем КУКИ
$tektime=time(); $wrfcookies="$name|$pass|$tektime|0|";
setcookie("wrfcookies", $wrfcookies, time()+1728000);
}
if (!isset($fnomer) and !isset($ok)) exit("$back. Вы ошиблись в воде активационного ключа или емайла.</center>");
if (isset($ok)) $add="Ваша запись уже активирована"; else $add="$name, Вы успешно зарегистрированы";

print"<html><head><link rel='stylesheet' href='$forum_skin/style.css' type='text/css'></head><body>
<script language='Javascript'>function reload() {location=\"index.php\"}; setTimeout('reload()', 2500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
Спасибо, <B>$add</B>.<BR><BR>Через несколько секунд Вы будете автоматически перемещены на главную страницу форума.<BR><BR>
<B><a href='index.php'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
exit; }


















if ($_GET['event'] =="givmepassword") { // отсылает утеряные данные на мыло

// защита от злостного хакера
if (!isset($_POST['myemail']) or !isset($_POST['myname'])) exit("Из формы НЕ поступили данные!");
$myemail=strtolower($_POST['myemail']); $myemail=replacer($myemail);
$myname =strtolower($_POST['myname']); $myname =replacer($myname);
if (strlen($myemail)>40 or strlen($myname)>40) exit("Длина имени или емайл должна быть менее 40 символов!");

// ГЕНЕРИРУЕМ новый пароль юзера
$len=8; // количество символов в новом пароле
$base='ABCDEFGHKLMNPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
$max=mb_strlen($base)-1; $pass=''; mt_srand((double)microtime()*1000000);
while (mb_strlen($pass)<$len) {$zz=mt_rand(0,$max); $pass.=$base($zz);}

$lines=file("$datadir/user.php"); $record="<?die;?>\r\n"; $itogo=count($lines); $i=1; $regenter=FALSE;

do {$rdt=explode("|",$lines[$i]); // проходим по всем пользователям и сверяем данные
if (isset($rdt[1])) { // Если строчка потерялась в скрипте (пустая строка) - то просто её НЕ выводим
$rdt[5]=strtolower($rdt[5]); $rdt[2]=strtolower($rdt[2]);
if ($myemail===$rdt[5] or $myname===$rdt[2]) {$regenter=TRUE; $myemail=$rdt[5]; $myname=$rdt[2]; $passmd5=md5("$pass"); $lines[$i]=str_replace("$rdt[3]","$passmd5",$lines[$i]);}
} //if isset
$record.=$lines[$i];
$i++; } while($i < $itogo);

// узнаём IP-запрашивающего пароль
$ip=""; $ip=(isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:0;

// переписываем файл участников - вставляем туда новый пароль
$fp=fopen("$datadir/user.php","a+");
flock ($fp,LOCK_EX);
ftruncate ($fp,0);
fputs($fp,"$record");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// отправка пользователю его имени и пароля на мыло
if ($regenter==TRUE) {
$headers=null; // Настройки для отправки писем
$headers.="From: администратор <$adminemail>\n";
$headers.="X-Mailer: PHP/".phpversion()."\n";
$headers.="Content-Type: text/plain; charset=UTF-8";

// Собираем всю информацию в теле письма
$allmsg=$forum_name.' (данные для восстановления доступа к форуму)'.chr(13).chr(10).
        'Вы, либо кто-то другой с IP-адреса '.$ip.' запросили данные для восстановления доступа к форуму по адресу: '.$forum_url.chr(13).chr(10).chr(13).chr(10).
        'Ваше Имя: '.$myname.chr(13).chr(10).
        'Ваш новый пароль: '.$pass.chr(13).chr(10).chr(13).chr(10).
        'Для входа на форум перейдите по ссылке и введите логин и НОВЫЙ ПАРОЛЬ: '.$forum_url.'?event=login'.chr(13).chr(10).chr(13).chr(10).
        'Изменить Ваш пароль (только после того как войдёте) всегда можно на странице: '.$forum_url.'?event=profile&pname='.$myname.chr(13).chr(10).chr(13).chr(10).
        '* Это письмо сгенерировано роботом, отвечать на него не нужно.'.chr(13).chr(10);
// Отправляем письмо майлеру на съедение ;-)
mail("$myemail", "=?UTF-8?B?" . base64_encode("$forum_name (Данные для восстановления доступа к форуму)") . "?=", $allmsg, $headers);
// если есть участник с введённым емайлом
$msgtoopr="<B>$myname</B>, на Ваш электронный адрес выслано сообщение с именем и паролем доступа к форуму.";
}
// Если нет такого емайла в БД
else $msgtoopr="<B>Участника с таким емайлом или логином</B><BR> на форуме <B>не зарегистрировано!</B>";
print "<html><body><script language='Javascript'>function reload() {location=\"index.php\"}; setTimeout('reload()', 2000);</script>
<BR><BR><BR><center><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 width=300><tr><td align=center>
<font style='font-size: 15px'>$msgtoopr Через несколько секунд Вы будете автоматически перемещены на главную страницу.
Если этого не происходит, нажмите <B><a href='index.php'>здесь</a></B></font>.</td></tr></table></center><BR><BR><BR></body></html>";
exit; }








// ----- Шапка для всех страниц форума

if (isset($_COOKIE['wrfcookies'])) {
$wrfc=$_COOKIE['wrfcookies']; $wrfc=htmlspecialchars($wrfc,ENT_COMPAT,"UTF-8"); $wrfc=stripslashes($wrfc);
$wrfc=explode("|", $wrfc);
$wrfname=$wrfc[0];$wrfpass=$wrfc[1];$wrftime1=$wrfc[2];$wrftime2=$wrfc[3];
if (time()>($wrftime1+50)) {$tektime=time();$wrfcookies="$wrfc[0]|$wrfc[1]|$tektime|$wrftime1";setcookie("wrfcookies", $wrfcookies, time()+1728000);}}
 else {unset($wrfname); unset($wrfpass);}

// -----

$frname=""; $frtname=""; include("data/top.html"); addtop(); // подключаем ШАПКУ форума




















if ($_GET['event'] =="profile") { // РЕДАКТИРОВАНИЕ ПРОФИЛЯ

if (!isset($_GET['pname'])) exit("Попытка взлома.");
$pname=urldecode($_GET['pname']); // РАСКОДИРУЕМ имя пользователif (!ctype_digit($userpn) or strlen($userpn)>4) exit("<B>$back. Попытка взлома. Хакерам здесь не место!");я, пришедшее из GET-запроса.
$lines=file("$datadir/user.php"); $i=count($lines); $use="0"; $userpn="0";
do {$i--; $rdt=explode("|", $lines[$i]);

if (isset($rdt[1])) { // Если строчка потерялась в скрипте (пустая строка) - то просто её НЕ выводим

if (strlen($rdt[16])=="6" and ctype_digit($rdt[16])) $rdt[16]="<B><font color=red>ожидание активации</font></B>";

if ($pname===$rdt[2]) { $userpn=$i;

// Считываем статистику сообщений/репы юзера
$jfile="$datadir/userstat.csv"; $jlines=file("$jfile"); $uj=count($jlines)-1; $msjitogo=0;
for ($j=1;$j<=$uj;$j++) {$udt=explode("|",$jlines[$j]); $msjitogo=$msjitogo+$udt[6];
 if ($udt[2]==$rdt[2]) {$msguser=$udt[6]; $temaded=$udt[5]; $repa=$udt[7];}}
if ($msjitogo==0) $msgaktiv=0; else $msgaktiv=round(10000*$msguser/$msjitogo)/100;

$aktiv=$rdt[1]; $tekdt=time(); $aktiv=round(($tekdt-$aktiv)/86400);
if ($aktiv<=0) $aktiv=1; $aktiv=round(100*$msguser/$aktiv)/100;
$rdt[1]=date("d.m.Y г.",$rdt[1]);

if (isset($wrfname) & isset($wrfpass)) { $wrfname=replacer($wrfname); $wrfpass=replacer($wrfpass);
if ($rdt[6]==TRUE) $pol="мужчина"; else $pol="женщина";

if ($wrfname===$rdt[2] & $wrfpass===$rdt[3]) {
print "<center><span class=maintitle>Регистрационные данные</span><br>

<br><form action='tools.php?event=reregist' name=creator method=post enctype=multipart/form-data>

<table cellpadding=3 cellspacing=1 width=100% class=forumline>
<tr><th class=thHead colspan=2 height=25 valign=middle>Регистрационная информация</th></tr>
<tr><td class=row2 colspan=2><span class=gensmall>Поля отмеченные * обязательны к заполнению, если не указано обратное</span></td></tr>
<tr><td class=row1 width=35%><span class=gen>Имя участника:</span><span class=gensmall><br>Русские ники РАЗРЕШЕНЫ</span></td><td class=row2><span class=nav>$rdt[2]</span></td></tr>
<tr><td class=row1><span class=gen>Ваш пол:</span><br></td><td class=row2><span class=gen>$pol</span><input type=hidden value='$rdt[6]' name=pol></td></tr>
<tr><td class=row1><span class=gen>Ваш пароль: *</span></td><td class=row2><input class=inputmenu type=text value='скрыт' maxlength=10 name=newpassword size=15><input type=hidden class=inputmenu value='$rdt[3]' name=pass>
(если хотите сменить, то введите новый пароль, иначе оставьте как есть!)</td></tr>

<tr><td class=row1><span class=gen>Адрес e-mail: *</span><br><span class=gensmall>Введите существующий электронный адрес! Форум защищён от роботов-спамеров.</span></td>
<td class=row2> <input type=text class=post style='width: 200px' value='$rdt[5]' name=email size=25 maxlength=50></td></tr>

<tr><td class=row1><span class=gen>Дата регистрации:</span></td><td class=row2><span class=gen>$rdt[1]</td></tr>
<tr><td class=row1><span class=gen>Репутация: </span><br></td><td class=row2><B>$repa</B> [<A href='#1' onclick=\"window.open('tools.php?event=repa&name=$wrfname&who=$userpn','repa','width=600,height=600,left=50,top=50,scrollbars=yes')\">Посмотреть статистику изменения</A>]</td></tr>
<tr><td class=row1><span class=gen>Активность:</span></td><td class=row2><span class=gen>Тем создано: <B>$temaded</B>, всего сообщений: <B>$msguser</B> [<B>$msgaktiv%</B> от общего числа / <B>$aktiv</B> сообщений в сутки]</span></td></tr>

<td class=row1><span class=gen>Персональные сообщения</span><br><span class=gensmall><td class=row2>";
$wrfname=strtolower($wrfname);
if (is_file("data-pm/$wrfname.csv")) {$linespm=file("data-pm/$wrfname.csv"); $pmi=count($linespm); print" <img src=\"$forum_skin/icon_mini_profile.gif\" border=0 hspace=3 />[<a href='pm.php?readpm&id=$wrfname'><font color=red><B>$pmi сообщения в ПМ</b></font></a>]";} else echo'сообщений нет';
print"</span></td>
</tr><tr>
<td class=catSides colspan=2 height=28>&nbsp;</td>
</tr><tr>
<th class=thSides colspan=2 height=25 valign=middle>Немного о себе</th>
</tr><tr>
<td class=row1><span class=gen>День варенья:</span><br><span class=gensmall>Введите день рождения в формате: ДД.ММ.ГГГГГ, если не секрет.</span></td>
<td class=row2><input type=text name=dayx value='$rdt[7]' class=post style='width: 100px' size=10 maxlength=18></td>
</tr><tr>
<td class=row1><span class=gen>Номер в ICQ:</span><br><span class=gensmall>Введите номер ICQ, если он у Вас есть.</span></td>
<td class=row2><input type=text value='$rdt[10]' name=icq class=post style='width: 100px' size=10 maxlength=10></td>
</tr><tr>
<td class=row1><span class=gen>Домашняя страничка:</span><br><span class=gensmall>Если у Вас есть домашняя или любимая страничка в Интернете, введите URL этой странички.</span></td>
<td class=row2><input type=text value='$rdt[11]' class=post style='width: 500px' name=www size=25 maxlength=70 value='https://' /></td>
</tr><tr>
<td class=row1><span class=gen>Откуда:</span><br><span class=gensmall>Введите место жительства (Страна, Область, Город).</span></td>
<td class=row2><input type=text class=post style='width: 500px' value='$rdt[12]' name=about size=25 maxlength=70></td>
</tr><tr>
<td class=row1><span class=gen>Интересы:</span><br><span class=gensmall>Вы можете написать о ваших интересах</span></td>
<td class=row2><input type=text class=post style='width: 500px' value='$rdt[13]' name=work size=35 maxlength=70></td>
</tr><tr>
<td colspan=2>
<input type=hidden name=name value='$rdt[2]'>
<input type=hidden name=oldpass value='$rdt[3]'>
</td></tr><tr>
<td class=catBottom colspan=2 align=center height=28><input type=submit name=submit value='Сохранить изменения' class=mainoption /></td>
</tr></table></form>"; $use="1"; }


if ($use!="1") {

////////////// Передалать  строки со статусом!!!! и $rdt[1] - дата регистрации!!!!!!!!!
//if (strlen($rdt[13])<2) $rdt[13]=$user_name;
if (is_file("avatars/$rdt[15]")) $avpr="$rdt[15]"; else $avpr="noavatar.gif";
if ($rdt[6]==TRUE) $pol="мужчина"; else $pol="женщина";
print "<center><span class=maintitle>Профиль участника</span><br><br><table cellpadding=5 cellspacing=1 width=100% class=forumline>
<tr><th class=thHead colspan=2 height=25 valign=middle>Регистрационная информация</th></tr>
<tr><td class=row1 width=30%><span class=gen>Имя участника:</span></td><td class=row2><span class=nav>$rdt[2]</span></td></tr>
<tr><td class=row1><span class=gen>Репутация: </span><br></td><td class=row2><B>$repa</B> [<A href='#1' onclick=\"window.open('tools.php?event=repa&name=$rdt[2]&who=$userpn','repa','width=600,height=600,left=50,top=50,scrollbars=yes')\">Оценить &#177;</A>]</td></tr>
<tr><td class=row1><span class=gen>Активность:</span></td><td class=row2><span class=gen>Тем создано: <B>$temaded</B>, всего сообщений: <B>$msguser</B> [<B>$msgaktiv%</B> от общего числа / <B>$aktiv</B> сообщений в сутки]</span></td></tr>
<tr><td class=row1><span class=gen>Отправить личное сообщение на e-mail: </span><br></td><td class=row2><form method='post' action='tools.php?event=mailto' target='email' onclick=\"window.open('tools.php?event=mailto','email','width=600,height=350,left=100,top=100');return true;\"><input type=hidden name='email' value='$rdt[5]'><input type=hidden name='name' value='$rdt[2]'><input type=hidden name='id' value=''><input type=image src='$forum_skin/ico_pm.gif' alt='личное сообщение'></form></td></tr>
<tr><td class=row1><span class=gen>Дата регистрации:</span></td><td class=row2><span class=gen>$rdt[1]</span></td></tr>
<tr><td class=row1><span class=gen>Статус:</span></td><td class=row2><span class=gen>$rdt[13]</span></td></tr>
<tr><td class=row1><span class=gen>Пол:</span></td><td class=row2><span class=gen>$pol</span></td></tr>
<tr><td class=row1><span class=gen>День Варенья:</span><br></td><td class=row2><span class=gen>$rdt[7]</span></td></tr>
<tr><td class=row1><span class=gen>Номер в ICQ:</span><br></td><td class=row2><span class=gen>$rdt[10]</td></tr>
<tr><td class=row1><span class=gen>Домашняя страничка:</span></td><td class=row2><span class=gen><a href='$rdt[11]' target='_blank'>$rdt[11]</a></td></tr>
<tr><td class=row1><span class=gen>Откуда</span> (<span class=gensmall>Место жительства, город, страна.):</span></td><td class=row2><span class=gen>$rdt[12]</td></tr>
<tr><td class=row1><span class=gen>Интересы:</span></td><td class=row2><span class=gen>$rdt[13]</td></tr>
<tr><td class=row1><span class=gen>Подпись:</span></td><td class=row2><span class=gen>$rdt[14]</td></tr>
</td></tr></table><BR>"; $use="1";}

}
}
} // if
} while($i > "1");

if (!isset($wrfname)) exit("<BR><BR><font size=+1><center>Только зарегистрированные участники форума могут просматривать данные профиля!");

if ($use!="1") { // в БД такого ЮЗЕРА НЕТ - его админ удалил
print"<center><table width=600 height=300 class=forumline>
<tr><th class=thHead height=25 valign=middle>Пользователь НЕ ЗАРЕГИСТРИРОВАН</th></tr>
<tr><td class=row1 align=center><B>Уважаемый посетитель!</B><BR><BR> 
Извините, но участник с таким - <B>логином на форуме не зарегистрирован.</B><BR><BR>
Скорее всего, <B>его удалил администратор</B>.<BR><BR>
<B>Посмотреть других участников</B> можно <B><a href='tools.php?event=who'>здесь</a>.</B><br><br>
<B>Перейти на главную</B> страницу форума можно по <B><a href='$forum_url'>этой ссылке</a></B>
</TD></TR></TABLE>"; }
}






if ($_GET['event']=="reg") {
if (!isset($_POST['rulesplus'])) {
echo'
<form action="tools.php?event=reg" method=post>
<center><span class=maintitle>Правила и условия регистрации</span><br><br>
<table cellpadding=8 cellspacing=1 width=100% class=forumline><tr><th class=thHead height=25 valign=middle>ЧИТАЙФ</th></tr><tr>
<td class=row1><span class=gen>';
if (is_file("$datadir/pravila.html")) include"$datadir/pravila.html";
echo'</td></tr><tr><td class=row2><INPUT type=checkbox name=rulesplus><B>Я ознакомился с правилами и условиями, и принимаю их.</B></td></tr><tr>
<td class=catBottom align=center height=28><input type=submit value="Продолжить регистрацию" class=mainoption></td>
</tr></table>
</form>'; 
} else {

print"<center><span class=maintitle>Регистрация на форуме</span><br>
<br><form action='tools.php?event=regnxt' method=post>

<table cellpadding=3 cellspacing=1 width=100% class=forumline><tr>
<th class=thHead colspan=2 height=25 valign=middle>Регистрационная информация</th>
</tr><tr>
<td class=row1 width=35%><span class=gen>Имя участника:</span><span class=gensmall><br>Разрешено использовать только русские, латинские буквы, цифры и знак подчёркивания</span></td>
<td class=row2><input type=text class=post style='width:200px' name=name size=25 maxlength=$maxname></td>
</tr><tr>
<td class=row1><span class=gen>Ваш пароль:</span></td>
<td class=row2><input type=password class=post style='width:200px' name=pass size=25 maxlength=25></td>
</tr><tr>
<td class=row1><span class=gen>Адрес e-mail:</span><br><span class=gensmall>Введите существующий электронный адрес! На Ваш емайл будет отправлено сообщение с кодом активации.</span></td>
<td class=row2><input type=text class=post style='width: 200px' name=email size=25 maxlength=50></td>
</tr><tr>
<td class=row1><span class=gen>Ваш пол:</span><br></td>
<td class=row2><input type=radio name=pol value='1'checked> мужчина&nbsp;&nbsp; <input type=radio name=pol value='0'> женщина</td>
</tr>";

if ($antispam==TRUE) {echo'<tr><TD class=row2>Защитный код</TD><TD class=row2>'; nospam();} // АНТИСПАМ !

echo'</td></tr><tr>
<td class=row2 colspan=2><span class=gensmall>* Все поля обязательны к заполнению<BR>
** Ваш пароль будет также отправлен на адрес электронной почты, который Вы определите</span></td>
</tr><tr>
<td class=catBottom colspan=2 align=center height=28><input type=submit value="Продолжить" class=mainoption></td>
</tr></table></form>';
}
}



if ($_GET['event']=="find") { // ПОИСК
$minfindme="3"; //минимальное кол-во символов в слове для поиска
echo'<BR><form action="tools.php?event=go&find" method=POST>
<center><table class=forumline align=center width=700>
<tr><th class=thHead colspan=4 height=25>Поиск</th></tr>
<tr class=row2>
<td class=row1>Запрос: <input type="text" style="width: 250px" class=post name=findme size=30></TD>
<TD class=row1>Тип: <select style="FONT-SIZE: 12px; WIDTH: 120px" name=ftype>
<option value="0">&quotИ&quot
<option value="1" selected>&quotИЛИ&quot
<option value="2">Вся фраза целиком
</select></td>
<td class=row1><INPUT type=checkbox name=withregistr><B>С учётом РЕГИСТРА</B></TD>
<input type=hidden name=gdefinder value="1">
</tr>';

print"<TR><TD class=row1 colspan=4>ИЛИ найти все сообщения зарегистрированного пользователя: 
<SELECT name=user style='FONT-SIZE: 14px; WIDTH: 250px'><OPTION value='0' selected> - - Выбрать пользователя - -</OPTION>";
$slines=file("data/user.php"); $smax=count($slines); $i="1"; do {
$slines[$i]=replacer($slines[$i]); $dts=explode("|",$slines[$i]);
print "<OPTION value=\"$dts[2]\">$dts[2]</OPTION>\r\n"; $i++; } while($i < $smax);
echo'</SELECT></TD>


<tr class=row1>
<td class=row1 colspan=4 width="100%">
Язык запросов:<br><UL>
<LI><B>&quotИ&quot</B> - должны присутствовать оба слова;</LI><br>
<LI><B>&quotИЛИ&quot</B> - есть ХОТЯ БЫ одно из слов;</LI><br>
<LI><B>&quotВся фраза целиком&quot</B> - в искомом документе ищите фразу на 100% соответствующую вашему запросу;</LI><BR><BR>
<LI><B>&quotС учётом РЕГИСТРА&quot</B> - поиск ведётся с учётом введённого ВАМИ РЕГИСТРА;</LI><BR><BR>
</UL>Скрипт ищет все данные, которые начинаются с введенной вами строки. Например, при запросе &quotфорум&quot будут найдены слова &quotфорум&quot, &quotфорумы&quot, &quotфорумом&quot и многие другие.
</td>
</tr><tr><td class=row1 colspan=4 align=center height=28><input type=submit class=post value="  Поиск  "></td></form>
</tr></table><BR><BR>';

print "Ограничение на поиск: <BR> - минимальное кол-во символов: <B>$minfindme</B>";
}





if (isset($_GET['find'])) {

//exit("Поиск временно не работает!");
$minfindme="2"; //минимальное кол-во символов в слове для поиска
$time=explode(' ', microtime()); $start_time=$time[1]+$time[0]; // считываем начальное время запуска поиска

$gdefinder="1"; $ftype=$_POST['ftype']; 
if (!ctype_digit($ftype) or strlen($ftype)>2) exit("<B>$back. Попытка взлома. Хакерам здесь не место.</B>");
if (!isset($_POST['withregistr'])) $withregistr="0"; else $withregistr="1";

if ($_POST['user']!="0") {$findme=$_POST['user']; $gdefinder="3"; $ftype="2"; $withregistr="1";} //  Если выбран поиск по имени юзера
else $findme=$_POST['findme']; 

$findme=replacer($findme); // Защита от взлома
$findmeword=explode(" ",$findme); // Разбиваем $findme на слова
$wordsitogo=count($findmeword);
$findme=trim($findme); // Вырезает ПРОБЕЛьные символы 
if ($findme == "" || strlen($findme) < $minfindme) exit("$back Ваш запрос пуст, или менее $minfindme символов!</B>");

// Открываем файл с темами формума и запоминаем имена файлов с сообщениями

setlocale(LC_ALL,'Russian_Russia.65001'); // 11.2018! РАЗРЕШАЕМ РАБОТУ ФУНКЦИЙ, работающих с регистром и с РУССКИМИ БУКВАМИ
//setlocale(LC_ALL,'ru_RU.CP1251'); // ! РАЗРЕШАЕМ РАБОТУ ФУНКЦИЙ, работающих с регистором и с РУССКИМИ БУКВАМИ


// ПЕРВЫЙ цикл - считаем кол-во форумов (записываем в переменную $itogofid)
$mainlines=file("$datadir/wrforum.csv");$i=count($mainlines); $itogofid="0";$number="0"; $oldid="0"; $nump="0";
do {$i--; $dt=explode("|",$mainlines[$i]);
if ($dt[3]==FALSE) { $maxzd=$dt[9];
if (!ctype_digit($maxzd)) $maxzd=0;  // считываем ЗВЁЗДы раздела из файла
if ($maxzd<1) {$itogofid++; $fids[$itogofid]=$dt[2]; }} // $itogofid - общее кол-во форумов
} while($i > "0");


// ВТОРОЙ цикл - открываем файл с топиком (если он существует) и сохраняем в переменную $topicsid все имена тем
do { $fid=$fids[$itogofid];
if (is_file("$datadir/$fid.csv")) {
$msglines=file("$datadir/$fid.csv");

unset($topicsid); if (count($msglines)>0) { $lines=file("$datadir/$fid.csv"); $i=count($lines);
do {$i--; $dt=explode("|",$lines[$i]); $topicsid[$i]="$dt[2]$dt[3]";} while($i > "0"); }


// ТРЕТИЙ цикл - последовательно открываем каждую тему
if (isset($topicsid)) {
$ii=count($topicsid);
do {$ii--;
$id=str_replace("\r\n","",$topicsid[$ii]);

if (is_file("$datadir/$id.csv")) { // Если файл есть? Бывает, что файлы с сообщениями бьются, тогда при пересчёте они удаляются.
$file=file("$datadir/$id.csv"); $iii=count($file);

// ЧЕТВЁРТЫЙ цикл - последовательно ищем в каждой теме искомое сообщение
if ($iii>0) { // если файл с сообщениями НЕ ПУСТОЙ
do {$iii--; 
$lines=file("$datadir/$id.csv");
$dt=explode("|", $lines[$iii]); if (!isset($dt[4])) $dt[4]=" ";

if ($gdefinder=="0") {$msgmass=array($dt[2],$dt[3],$dt[4]); $gi="3"; $add="ях <B>Автор, Текст, Заголовок</B> ";}
if ($gdefinder=="1") {$msgmass=array($dt[14]); $gi="1"; $add="е <strong>Текст</strong> ";}
if ($gdefinder=="2") {$msgmass=array($dt[3],$dt[4]); $gi="2"; $add="ях <B>Текст и Заголовок</B> ";}
if ($gdefinder=="3") {$msgmass=array($dt[8]); $gi="1"; $add="е <B>Автор</B> ";}
if ($gdefinder=="4") {$msgmass=array($dt[3]); $gi="1"; $add="е <B>Заголовок</B> ";}

// Цикл по местам поиска (0,1,2,3,4)
do {$gi--;

$msg=$dt[14];
$msdat=$msgmass[$gi];
$stroka="0"; $wi=$wordsitogo;

// ЦИКЛ по КАЖДОМУ слову запроса !
do {$wi--;


// БЛОК УСЛОВИЙ ПОИСКА
if ($withregistr!="1") // регистронезависимый поиск - cимвол "i" после закрывающего ограничителя шаблона - /
   {
    if ($ftype=="2") 
        { if (stristr($msdat,$findme)) // ПОИСК по "ВСЕЙ ФРАЗЕ ЦЕЛИКОМ" БЕЗ учёта регистра
          { $stroka++; $msg=str_replace($findme," <b><u>$findme</u></b> ",$msg); }
        } else {
           $str1=strtolower($msdat);  
           $str2=strtolower($findmeword[$wi]); 
           if ($str2!="" and strlen($str2) >= $minfindme)
              { if (stristr($str1,$str2)) // ПОИСК БЕЗ учёта регистра при равных прочих условиях
                { $stroka++; $msg=str_replace($findmeword[$wi]," <b><u>$findmeword[$wi]</u></b> ",$msg); }
              }
          }
        }

else  // if ($withregistr!="1")
   {
    if ($ftype=="2")
       {
        if (strstr($msdat,$findme)) // ПОИСК по "ВСЕЙ ФРАЗЕ ЦЕЛИКОМ" C учёта РЕГИСТРА
           {
            $stroka++;
            $msg=str_replace($findme," <b><u>$findme</u></b> ",$msg);
           }
       }
     else {
           if ($msdat!="" and strlen($findmeword[$wi]) >= $minfindme)
              {
               if (strstr($msdat,$findmeword[$wi])) // ПОИСК С учётом РЕГИСТРА при равных прочих условиях
                  {
                   $stroka++;
                   $msg=str_replace($findmeword[$wi]," <b><u>$findmeword[$wi]</u></b> ",$msg);
                  }
              }
          }

   }   // if ($withregistr!="1")

} while($wi > "0"); // конец ЦИКЛа по КАЖДОМУ слову запроса


// Подготавливаем результирующее сообщение, и если результат соответствует условиям - выводим его
if ($ftype=="0") { if ($stroka==$wordsitogo) $printflag="1"; }
if ($ftype=="1") { if ($stroka>"0") $printflag="1"; }
if ($ftype=="2") { if ($stroka==$wordsitogo) $printflag="1"; }

if (!isset($printflag)) $printflag="0";
    if ($printflag=="1")
       { $msg=str_replace("<br>", " &nbsp;&nbsp;", $msg); // заменяем в сообщении <br> на пару пробелов

if (strlen($msg)>150)
{
 $ma=strpos($msg,"<b>"); if ($ma > 50) $ma=$ma-50; else $ma=0;
 $mb=strrpos($msg,">b/<"); if (($mb+50) > strlen($msg)) $mb=strlen($msg); else $mb=$mb+50;
 $msgtowrite="..."; $msgtowrite.=substr($msg,$ma,$mb); $msgtowrite.="...";
 $msgtowrite=substr($msg,0,400);
}
else $msgtowrite=$msg;




if (!isset($m)) {
print"
<small><BR>По запросу '<U><B>$findme</B></U>' в пол$add найдено: <HR size=+2 width=99% color=navy>
<BR><form action='tools.php?event=go&find' method=POST>
<table class=forumline align=center width=700>
<tr><th class=thHead colspan=4 height=25>Повторить поиск по сообщению</th></tr>
<tr class=row2>
<td class=row1>Запрос: <input type='text' value='$findme' style='width: 250px' class=post name=findme size=30>
<INPUT type=hidden value='1' name=ftype>
<INPUT type=hidden value='0' name=user>
<input type=hidden name=gdefinder value='1'>
<input type=submit class=post value='  Поиск  '></td></table></form><br>
<table width=100% class=forumline><TR align=center class=small><TH class=thCornerL><B>№</B></TH><TH class=thCornerL width=35%><B>Заголовок</B></TH><TH class=thCornerL width=70%><B>часть сообщения</B></TH><TH class=thCornerL><B>Совпадений<BR> в теме</B></TH></TR>"; $m="1"; }

$in=$iii+1; if ($in>$msg_onpage) {$page=ceil($in/$msg_onpage);} else $page="1"; // расчитываем верную страницу и номер сообщения

if ($oldid!=$id and $number<100) { $number++; $msgnumber=$iii;

if ($nump>1) $anp="$nump"; else $anp="1";
if ($number>1) print"<TD class=row1 align=center>$anp</TD></TR><TR height=25>";

$msg=$msgtowrite; // Убираем спец код из строки поиска
$msg=str_replace("&","&amp;",$msg); $msg=str_replace('\"','"',$msg);
$msg=str_replace("[b]","<p>",$msg); $msg=str_replace("[/b]","</p>",$msg);
$msg=str_replace("[RB]","<p>",$msg); $msg=str_replace("[/RB]","</p>",$msg);
$msg=str_replace("[Code]","<p>",$msg); $msg=str_replace("[/Code]","</p>",$msg);
$msg=str_replace("[Quote]","<p>",$msg); $msg=str_replace("[/Quote]","</p>",$msg);
$msg=str_replace("<br>","\r\n", $msg);
$msg=str_replace("&amp;lt;br&amp;gt;","<p></p>", $msg);

print "<TD class=row1 align=center><B>$number</B></TD>
<TD class=row1><A class=listlink href='index.php?id=$id&page=$page#m$iii' target=_blank>$dt[5]</A></TD>
<TD class=row1>$msg</TD>";
$printflag="0"; $nump="0";

} else $nump++;

if ($number>=100) { print"</TR></TABLE> * поиск останавливается, при нахождении более 100 вхождений!"; $gi=0; $iii=0; $ii=0; $itogofid=0;}

$oldid=$id;
} // if $printflag==1

} while($gi > "0"); // конец ЦИКЛа по МЕСТУ поиска

} while($iii > "0");
} // если файл с сообщениями НЕПУСТОЙ

} // if is_file("$datadir/$id.csv")
} while($ii > "0");

} // if isset($topicsid)

} // if файл $fid.csv НЕ пуст

$itogofid--;
} while($itogofid > "0");

if (!isset($m)) echo'<table width=80% align=center><TR><TD>По вашему запросу ничего не найдено.</TD></TR></table>';

$time=explode(' ',microtime());
$seconds=($time[1]+$time[0]-$start_time);
echo "</TR></table><HR size=+2 width=99% color=navy><BR><p align=center><small>".str_replace("%1", sprintf("%01.3f", $seconds), "Время поиска: <b>%1</b> секунд.")."</small></p>";

}

} // if isset($_GET['event']) - всё, что делается при наличии переменной $event

?>

</td></tr></table>
<center><small>Powered by <a href="https://mamon.host" title="Скрипт форума" class="copyright">MAMON DEV</a> RM Cloud Data &copy; ver 1.1<br></small></center>
</body>
</html>