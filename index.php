<? // WR-forum Lite v 2.3 UTF-8 //  07.01.2023 г.  //  WR-Script.ru

//error_reporting (E_ALL); // ВРЕМЕННО - на время тестирования и отладки скрипта
error_reporting(0); // РАЗКОМЕНТИРУЙТЕ для постоянной работы
@ini_set('register_globals','off');// Все скрипты написаны для этой настройки php

include "data/config.php";

// Определяем URL форума, сайта с https
$site_url="https://".$_SERVER['HTTP_HOST']; $url1="$site_url".$_SERVER['REQUEST_URI'];
$url=str_replace('index.php','',"$url1"); $forum_url=preg_replace("/\?.+/","",$url); // удалить весь GET запрос: все символы после ?

if (isset($_GET['new-tema'])) { header("HTTP/1.1 404 Moved Permanently"); header("Location: $forum_url");} // 11-11-2018

$navigate=FALSE; // TRUE; // Включить/выключить показа блока НАВИГАЦИЯ по темам. Лучше выключить для мобильных
	
function replacer ($text) { // ФУНКЦИЯ очистки кода
$text=str_replace("&#032;",' ',$text);
$text=str_replace(">",'&gt;',$text);
$text=str_replace("<",'&lt;',$text);
$text=str_replace("\"",'&quot;',$text);
$text=preg_replace("/\n\n/",' [br] ',$text);
$text=preg_replace("/\n/",' [br] ',$text);
$text=preg_replace("/\\\$/",'&#036;',$text);
$text=preg_replace("/\r/",'',$text);
$text=preg_replace("/\\\/",'&#092;',$text);
$text=str_replace("\r\n",' [br] ',$text);
$text=str_replace("\n\n",' [br][br] ',$text);
$text=str_replace("\n",' [br] ',$text);
$text=str_replace("\t",'',$text);
$text=str_replace("\r",'',$text);
$text=str_replace('   ',' ',$text);
return $text; }


// Функция содержит ПРОДОЛЖЕНИЕ ШАПКИ. Вызывается: addtop();
function addtop() { global $wrfname,$forum_skin,$date,$time;
// ищем КУКи и выводим ИМЯ
if (isset($_COOKIE['wrfcookies'])) {$wrfc=$_COOKIE['wrfcookies']; $wrfc=replacer($wrfc); $wrfc=explode("|", $wrfc); $wrfname=$wrfc[0];} else {unset($wrfname); unset($wrfpass); $wrfpass="";}
echo'<div align=right>';
if (isset($wrfname)) {print "<a href='tools.php?event=profile&pname=$wrfname' class=mainmenu>Ваш профиль</a>&nbsp; <a href='index.php?event=clearcooke' class=mainmenu>Выход [<B>$wrfname</B>]</a>";
$name=strtolower($wrfname);
}
if (!isset($_COOKIE['wrfcookies']) and !isset($_GET['id'])) {print "<span class=mainmenu>
<a href='tools.php?event=reg' class=mainmenu>Регистрация</a>&nbsp;&nbsp;
<a href='tools.php?event=login' class=mainmenu> Вход</a></div>";}
if (is_file("data/tiptop.html")) include("data/tiptop.html"); // подключаем дополнение к ВЕРХУШКе
print"Сегодня: $date - $time</div>";
return true;}


function nospam() { global $max_key,$rand_key,$antispam2k,$antispam2kv; // Функция АНТИСПАМ (2 в одном)
if (array_key_exists("image", $_REQUEST)) { $num=replacer($_REQUEST["image"]);
for ($i=0; $i<10; $i++) {if (md5("$i+$rand_key")==$num) {imgwr($st,$i); die();}} }
$xkey=""; mt_srand(time()+(double)microtime()*1000000);
$dopkod=mktime(0,0,0,date("m"),date("d"),date("Y")); // доп.код: меняется каждые 24 часа
$stime=md5("$dopkod+$rand_key");// доп.код
echo'<noindex><table cellspacing=0 cellpadding=0><tr height=30><TD>Защитный код:</TD>';
$nummax=0; for ($i=0; $i<=$max_key; $i++) {
$snum[$i]=mt_rand(0,9); $psnum=md5($snum[$i]+$rand_key+$dopkod);
$secret=mt_rand(0,1); $styles='bgcolor=#FFFFCE';
if ($nummax<3) { if ($secret==1 or $i==0) {$styles='bgcolor=#93C9FF'; $xkey=$xkey.$snum[$i]; $nummax++;}}
echo "<noindex><td width=20 $styles><img src=antispam.php?image=$psnum border=0 alt=''>\n<img src=antispam.php?image=$psnum height=1 width=1 border=0></td></noindex>\r\n";}
$xkey=md5("$xkey+$rand_key+$dopkod"); //число + ключ из data/config.php + код меняющийся кажые 24 часа
print"<td><input name='usernum' class=post type='text' maxlength=$nummax size=6> (введите цифры, которые на <font style='font-weight:bold'> синем фоне</font>)
<input name=xkey type=hidden value='$xkey'>
<input name=stime type=hidden value='$stime'>
</td></tr></table></noindex>";
if ($antispam2k==TRUE) print"Ответ на вопрос: <input name='antispam2ko' class=post type='text' maxlength=20 size=10>($antispam2kv)";
return; }


function addmsg($qm) { // ФУНКЦИЯ добавления темы/сообщения
global $wrfname,$maxname,$can_up_file,$antispam,$max_key,$rand_key,$max_upfile_size,$showsmiles,$smiles,$valid_types,$datadir;

//Проверка ЗАПРЕТА IP-пользователя на добавление объявлений (файл ipblock.csv)
$ip=$_SERVER['REMOTE_ADDR']; // определяем IP юзера
if (is_file("$datadir/ipblock.csv")) { $lines=file("$datadir/ipblock.csv"); $i=count($lines);
$tektime=time();
if ($i>0) {do {$i--; $idt=explode("|",$lines[$i]);

if ($idt[2]==$ip and $tektime<$idt[1]) { $date=date("d.m.Y");
$idt[0]=date("d.m.Y в H:i",$idt[0]);
$idt[1]=date("d.m.Y в H:i",$idt[1]);
exit("<center><br><br><B>Администратор $idt[0] заблокировал для Вашего IP: $ip<br> возможность добавлять что-либо по следующей 
причине:<br><br> <font color=red><B>$idt[4].</B></font><br><br>
До $idt[1] Вам разрешено просматривать сообщения,<br> а вот ДОБАВЛЯТЬ ТЕМЫ/СООБЩЕНИЯ категорически ЗАПРЕЩЕНО!</B><br><br>");}
} while($i>"1");} unset($lines);}

echo'<tr><td class=row1 width=14% height=25><span class=gen><b>Имя</b></span></td>
<td class=row2 width=76%><span class=genmed>';
if (!isset($wrfname)) print "<input type=text name=name class=post maxlength=$maxname size=28> E-mail <input type=text name=email class=post size=30>";
else {
if (isset($_COOKIE['wrfcookies'])) {$wrfc=$_COOKIE['wrfcookies']; $wrfc=htmlspecialchars($wrfc,ENT_COMPAT,"UTF-8"); $wrfc=stripslashes($wrfc); $wrfc=explode("|", $wrfc); $wrfpass=replacer($wrfc[1]);} else {unset($wrfpass); $wrfpass="";}
print "<b>$wrfname</b><input type=hidden name=name value='$wrfname'><input type=hidden name=who value='1'><input type=hidden name=userpass value=\"$wrfpass\">";}

echo'</span></td></tr>
<tr><td class=row1 valign=top><span class=genmed><b>Сообщение</b><br><br>Нажмите на точку возле имени, чтобы вставить обращение к участнику в сообщение<br><br>Смайлы:<br>
<table align=center width=100 height=70><tr><td valign=top>';

if ($showsmiles==TRUE) {$i=count($smiles)-1;
for($k=0; $k<$i; $k=$k+2) {$j=$k+1; print"<A href='javascript:%20x()' onclick=\"DoSmilie(' $smiles[$j]');\"><img src='smile/$smiles[$k].gif' border=0></a> ";} }
print"<a name='add' href='#add' onclick=\"window.open('tools.php?event=moresmiles','smiles','width=450,height=300,left=50,top=300,toolbar=0,status=0,border=0,scrollbars=1')\">Ещё смайлы</a></tr></td></table><br>* При вставке видео с Ютубе нужно указывать только 'хвостик', например: BoUUbs3CvHs</span></td>
<td class=row2 valign=top><span class=gen><table width=100%><tr valign=middle><td><span class=genmed>
<input type=button class=button value='B' style='font-weight:bold; width:30px' title='Выделить жирным' onclick=\"DoSmilie(' [b]  [/b] ');\">&nbsp;
<input type=button class=button value='RB' style='font-weight:bold; width:30px; color:red' title='Выделить красным и жирным' onclick=\"DoSmilie('[RB] [/RB]');\">&nbsp;
<INPUT type=button class=button value='Цитировать выделенное' style='width: 180px' title='Выделите часть сообщения, затем нажмите эту кнопку' onclick='REPLIER.msg.value += \"[Quote]\"+(window.getSelection?window.getSelection():document.selection.createRange().text)+\"[/Quote]\"'>&nbsp;
<input type=button class=button value=' Код ' title='Вставка php,htm,js и др. кода' onclick=\"DoSmilie(' [Code]  [/Code] ');\">&nbsp;
<input type=button class=button value=' Youtube 'Видео с YouTube' onclick=\"DoSmilie(' [Youtube][/Youtube] ');\">&nbsp;
<input type=button class=button value=' IMG ' title='Вставьте ссылку на картинку в интернете и картинка будет показываться' style='font-weight:bold; color:navy' onclick=\"DoSmilie('[img][/img]');\">&nbsp;</span></td></tr>
<tr><td colspan=9><span class=gen><textarea name=msg cols=70 rows=15 style='width:100%' class=post>$qm</textarea></span></td>
</tr></table></TD>";

if ($antispam==TRUE and !isset($wrfname)) nospam(); // АНТИСПАМ !

echo'
<center><input type=submit tabindex=5 class=mainoption value=" Отправить ">&nbsp;&nbsp;&nbsp;<input type=reset tabindex=6 class=mainoption value=" Очистить "></center>
</td></tr></table></form>';
return;} // КОНЕЦ функции-формы ДОБАВЛЕНИЯ ТЕМЫ/ОТВЕТА



// Выбран ВЫХОД из форума - очищаем куки
if(isset($_GET['event'])) {if ($_GET['event']=="clearcooke") {setcookie("wrfcookies","",time()); Header("Location: $forum_url"); exit;}}















// ДОБАВЛЕНИЕ ТЕМЫ или ОТВЕТА
if(isset($_GET['add_tema']) or isset($_GET['add_msg'])) {

if (isset($_GET['add_tema'])) {$add_tema=TRUE; $add_msg=FALSE;} // Если добавляем ТЕМУ
if (isset($_GET['add_msg'])) {$add_msg=TRUE; $add_tema=FALSE;} // Если добавляем СООБЩЕНИЕ

if ($forum_lock==TRUE) exit("Временно добавление тем и сообщений приостановлено!");

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
if (!ctype_digit($fid) or mb_strlen($fid)!=3 or !is_file("$datadir/$fid.csv")) exit("$back. Попытка взлома через номер рубрики. Рубрика отсутствует. Номер должен содержать только 3 цифры!");

//Проверка ЗАПРЕТА ПО IP на добавление тем/сообщений
$ip=$_SERVER['REMOTE_ADDR']; $tektime=time(); $pageadd="";
if (is_file("$datadir/ipblock.csv")) { $lines=file("$datadir/ipblock.csv"); $i=count($lines);
if ($i>0) {do {$i--; $idt=explode("|",$lines[$i]);
if ($idt[2]==$ip and $tektime<$idt[1]) exit("<noindex><script language='Javascript'>function reload() {location=\"$forum_url\"}; setTimeout('reload()', 10000);</script>
<center><br><br><B>Админитратор заблокировал для Вашего IP: $ip<br> возможность добавлять что-либо по следующей причине:<br><br> 
<font color=red><B>$idt[4].</B></font><br><br>Вам разрешено просматривать сообщения,<br> а вот ДОБАВЛЯТЬ ТЕМЫ/СООБЩЕНИЯ категорически ЗАПРЕЩЕНО!</B></noindex>");
} while($i>"1");} unset($lines);}

if ($nosssilki==TRUE and $add_msg==TRUE and preg_match("/href|http|https|www|\.ru|\.com|\.net|\.info|\.org/i",$msg) and preg_match("/\.jpg|\.gif|\.jpeg|\.png/is",$msg)<>TRUE and !isset($wrfname))
exit("<center>Уважаемый гость с IP $ip , если Вы хотите делиться ссылками с другими пользователями форума - пройдите регистрацию.<BR>Если Вы спаммер - выйдете вон! </center><BR><BR>");

// проходим по всем разделам и форумам и ищем запращиваемый. Если wrforum.csv пуст, то подключаем резервную копию.
$realbase=TRUE; if (is_file("$datadir/wrforum.csv")) $mainlines=file("$datadir/wrforum.csv");
if (!isset($mainlines)) $datasize=0; else $datasize=sizeof($mainlines);
if ($datasize<=0) {if (is_file("$datadir/wrf-copy.csv")) {$realbase=FALSE; $mainlines=file("$datadir/wrf-copy.csv"); $datasize=sizeof($mainlines);}}
if ($datasize<=0) exit("$back. Проблемы с Базой данных, файл данных пуст - обратитесь к администратору");

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
// ШАГ 1
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
   if (strtolower($wrfname)===$realname & $wrfpass===$rdt[3] & $userpass===$rdt[3])
   {$rn_user=$rdt[0]; $name=$wrfname; $who="1";} }
} while($i > "1");
if ($rn_user==FALSE) {setcookie("wrfcookies","",time()); exit("Ошибка при работе с КУКИ! <font color=red><B>
Вы не сможете оставить сообщение, попробуйте подать его как гость.</B></font> Ваш логин и пароль не найдены 
в базе данных, попробуйте зайти на форум вновь. Если ошибка повторяется - обратитесь к администратору форума.");}
}}

if ($antispam==TRUE and !isset($wrfname)) { //--А-Н-Т-И-С-П-А-М--проверка кода--
if (!isset($_POST['usernum']) or !isset($_POST['xkey']) or !isset($_POST['stime']) ) exit("данные из формы не поступили!");
$usernum=replacer($_POST['usernum']); $xkey=replacer($_POST['xkey']); $stime=replacer($_POST['stime']);
$dopkod=mktime(0,0,0,date("m"),date("d"),date("Y")); // доп.код. Меняется каждые 24 часа
$usertime=md5("$dopkod+$rand_key"); $userkey=md5("$usernum+$rand_key+$dopkod");
if (($usertime!=$stime) or ($userkey!=$xkey) or mb_strlen($usernum)<1) exit("введён ОШИБОЧНЫЙ код!");}

// АНТИСПАМ 2!
if ($antispam2k==TRUE and !isset($wrfname)) { $ao=replacer($_POST['antispam2ko']);
if (strtolower($antispam2ko)!=strtolower($ao) or mb_strlen($ao)<1) exit("введён ошибочный ответ на вопрос!");}

if ($add_tema==TRUE and $g_add_tema==FALSE and !isset($wrfname)) exit("$back Администратор запретил гостям создавать темы!</center>");
if ($add_msg==TRUE and $g_add_msg==FALSE and !isset($wrfname)) exit("$back Администратор запретил гостям отвечать в темах!</center>");

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


if ($add_msg==TRUE) { // Если добавляем СООБЩЕНИЕ
$temka=file("$datadir/$fid$id.csv"); $kmax=count($temka); $k=$kmax;
do {$k--; $dtt=explode("|",$temka[$k]);
if ($dtt[3]==$id) { $zag=$dtt[5]; 
if ($dtt[11]==FALSE) exit("$back тема закрыта и добавление сообщений запрещено!");
if ($msg==$dtt[14]) exit("$back. Такое сообщение уже размещено последним в данной теме. Спамить на форуме запрещено!");
}
} while($j>0);
} // $add_msg==TRUE


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

if (!isset($name) || mb_strlen($name) > $maxname || mb_strlen($name) <1) exit("$back Ваше <B>Имя пустое, или превышает $maxname</B> символов!</B></center>");
if (preg_match("/[^(\\w)|(\\x7F-\\xFF)|(\\-)]/",$name)) exit("$back Ваше имя содержит запрещённые символы. Разрешены русские и английские буквы, цифры, подчёркивание и тире.");
if (mb_strlen(ltrim($zag))<3 || mb_strlen($zag) > $maxzag) exit("$back Слишком короткое название темы или <B>название превышает $maxzag</B> символов!</B></center>");
if (mb_strlen(ltrim($msg))<2 || mb_strlen($msg) > $maxmsg) exit("$back Ваше <B>сообщение короткое или превышает $maxmsg</B> символов.</B></center>");
if (!preg_match('/^([0-9a-zA-Z]([-.w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-w]*[0-9a-zA-Z].)+[a-zA-Z]{2,9})$/si',$email) and mb_strlen($email)>30 and $email!="") exit("$back и введите корректный E-mail адрес!</B></center>");


print"<html><head><link rel='stylesheet' href='$forum_skin/style.css' type='text/css'></head><body>";

$text_msg=$text_msg."|$msg||"; // Добавляем в переменную $text_msg кусочек о загруженном файле!


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
<B><a href='index.php?id=$fid$id&$pageadd#m$in'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
exit;}
} // $add_msg==TRUE

$razdelname="";
if ($realbase==TRUE) { // Если подключена рабочая база, а не копия
$lines=file("$datadir/wrforum.csv"); $max=sizeof($lines)-1;
$dt=explode("|",$lines[$realfid]); $dt[7]++; $main_id="$fid$id";
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






if ($add_tema==TRUE) { // Добавление ТЕМЫ - запись данных
// Пишем В ТОПИК
$fp=fopen("$datadir/$fid.csv","a+");
flock ($fp,LOCK_EX);
fputs($fp,"$text_tema\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

// Пишем В ТЕМУ
$fp=fopen("$datadir/$fid$id.csv","a+");
flock ($fp,LOCK_EX);
fputs($fp,"$text_msg\r\n");
fflush ($fp);
flock ($fp,LOCK_UN);
fclose($fp);

print "<script language='Javascript'>function reload() {location=\"index.php?id=$fid$id\"}; setTimeout('reload()', 1500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
Спасибо, <B>$name</B>, за добавление темы!<BR><BR>Через несколько секунд Вы будете автоматически перемещены в созданную тему.<BR><BR>
<B><a href='index.php?id=$fid$id'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
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
if ($timer<0) {$viptime=strtotime("+2 year"); touch("$datadir/$fid$id.csv",$viptime);}

print "<script language='Javascript'>function reload() {location=\"index.php?id=$fid$id$pageadd#m$in\"}; setTimeout('reload()', 1500);</script>
<table width=100% height=80%><tr><td><table border=1 cellpadding=10 cellspacing=0 bordercolor=#224488 align=center valign=center width=60%><tr><td><center>
Спасибо, <B>$name</B>, Ваш ответ успешно добавлен.<BR><BR>Через несколько секунд Вы будете автоматически перемещены в текущую тему <BR><B>$zag</B>.<BR><BR>
<B><a href='index.php?id=$fid$id$pageadd#m$in'>ДАЛЬШЕ >>></a></B></td></tr></table></td></tr></table></center></body></html>";
exit; }
}
























//--------------- ШАПКА для всех страниц форума ------------//
// Определяем дату последнего визита. +5 минут погрешности
if (isset($_COOKIE['wrfcookies'])) {
$wrfc=$_COOKIE['wrfcookies']; $wrfc=explode("|",replacer($wrfc));
$wrfname=$wrfc[0];$wrfpass=$wrfc[1];$wrftime1=$wrfc[2];$wrftime2=$wrfc[3];
if (time()>($wrftime1+240)) { $tektime=time(); $wrfcookies="$wrfc[0]|$wrfc[1]|$tektime|$wrftime1|";
setcookie("wrfcookies", $wrfcookies, time()+1728000);
$wrfc=$_COOKIE['wrfcookies']; $wrfc=explode("|",replacer($wrfc));
$wrfname=$wrfc[0];$wrfpass=$wrfc[1];$wrftime1=$wrfc[2];$wrftime2=$wrfc[3];}}
// ------------




// БЛОК подключает копию главного файла при повреждении
$error=FALSE; $frname=null; $frtname=""; $rfid="0";

if (isset($_GET['razdel']) or isset($_GET['id'])) {

if (is_file("$datadir/wrforum.csv")) $mainlines=file("$datadir/wrforum.csv"); $imax=count($mainlines); $i=$imax;
if (!isset($mainlines)) $datasize=0; else $datasize=sizeof($mainlines);
if ($datasize<=0) {if (is_file("$datadir/wrf-copy.csv")) {$mainlines=file("$datadir/wrf-copy.csv"); $datasize=sizeof($mainlines);}}
if ($datasize<=0) exit("<center><b>Файл РУБРИК несуществует! Зайдите в <a href='admin.php'>админку</a> и создайте рубрики!</b>");

// ДЛЯ ссылки типа razdel=
if (isset($_GET['razdel'])) { $razdel=replacer($_GET['razdel']);
do {$i--; $dt=explode("|",$mainlines[$i]); if ($dt[2]==$razdel) {$rfid=$i; $frname="$dt[4] »"; $i=0;} } while($i>0); } $i=$imax;

if (isset($_GET['id'])) { // Блок выводит в статусной строке: ТЕМА » РАЗДЕЛ » ФОРУМ
$id=$_GET['id'];
if (isset($_GET['quotemsg'])) $error=TRUE;
if (mb_strlen($id)<=3 and !is_file("$datadir/$id.csv")) $error="ый Вами раздел";
if (mb_strlen($id)> 3 and !is_file("$datadir/$id.csv")) $error="ая Вами тема";
if (!ctype_digit($id)) $error="ая Вами тема или раздел";

if(mb_strlen($id)>3) {$fid=mb_substr($id,0,3); $id=mb_substr($id,3,4);} else $fid=$id;

// проходим по всем разделам и топикам - ищем запрашиваемый
do {$i--; $dt=explode("|",$mainlines[$i]);
if ($dt[2]==$fid) { $frname="$dt[4] »"; $i=0; if($dt[8]>0) $maxtem=$dt[8]; else $maxtem="999";}
} while($i>0);

// Блок считывает название темы для отображения в шапке форума
if (mb_strlen($id)>3 and is_file("$datadir/$fid.csv")) {
$lines=file("$datadir/$fid.csv"); $imax=count($lines); $i=$imax;
do {$i--; $dt=explode("|",$lines[$i]);
if("$dt[2]$dt[3]"=="$fid$id") $frtname="$dt[5] »";
} while ($i>0); }

} //если isset razdel или id

if ($error!=FALSE) { // ЗАПРЕЩАЕМ ИНДЕКСАЦИЮ страниц с цитированием / УДАЛЁННЫЕ РАЗДЕЛЫ / ТЕМЫ!
$topurl="data/top.html";
ob_start(); include $topurl; $topurl=ob_get_contents(); ob_end_clean();
$topurl=str_replace("<meta name=\"Robots\" content=\"index,follow\">",'<meta name="Robots" content="noindex,follow">',$topurl);
print"$topurl";
if (mb_strlen($error)>1) exit("</td></tr></table><div align=center><br>Извините, но запрашиваем$error отсутствует.<br>
Рекомендую перейти на главную страницу форума по <a href='$forum_url'>этой ссылке</a>,<br>
и найти интересующую Вас тему.<br></div></td></tr></table></td></tr></table></td></tr></table></body></html>"); }


// здесь проверяем СУЩЕСТВУЕТ ЛИ СТРАНИЦА СПИСКА ТЕМ ИЛИ СТРАНИЦА ТЕКУЩЕЙ ТЕМЫ, на которую пришёл юзер
if (isset($_GET['id'])) { $id=replacer($_GET['id']);
if (mb_strlen($id)==3 or mb_strlen($id)==7) { 

if (is_file("$datadir/$id.csv")) {$lines=file("$datadir/$id.csv"); $imax=count($lines)+1;}
if (isset($_GET['page'])) $page=replacer($_GET['page']); else $page=1;

if (mb_strlen($id)==3) $maxikpage=ceil($imax/$tem_onpage); else $maxikpage=ceil($imax/$msg_onpage);
if ($page>$maxikpage and $imax!=0) {
$topurl="data/top.html";
ob_start(); include $topurl; $topurl=ob_get_contents(); ob_end_clean();
$topurl=str_replace("<meta name=\"Robots\" content=\"index,follow\">",'<meta name="Robots" content="noindex,follow">',$topurl);
print"$topurl";

// 2018 г. исключаем вызов страницы с page=1
if ($maxikpage==1) $lasturl="index.php?id=$id"; else $lasturl="index.php?id=$id&page=$maxikpage";

exit("<noindex><br><br><br><br><center><table class=forumline width=700><tr><th class=thHead colspan=4 height=25>Страница отсутствует!</th></tr>
<tr class=row2><td class=row1><center><BR><BR><B><span style='FONT-SIZE: 14px'>Такой страницы нет. В этой рубрике всего $maxikpage страниц.<br> Вы можете перейти 
<a href='index.php?id=$id'>на первую</a> или <a href='$lasturl'>последнюю страницу</a>.<br>
Через несколько секунд Вы будете перемещены на последнюю страницу.<br><br></td></tr></table>
<script language='Javascript'>function reload() {location=\"$lasturl\"}; setTimeout('reload()', 10000);</script></noindex>
</td></tr></table></td></tr></table></body></html>");
$error=TRUE; }}}

} // if (isset($_GET['id']))

if ($error==FALSE) include("data/top.html"); addtop(); // подключаем ШАПКУ форума










// выводим ГЛАВНУЮ СТРАНИЦУ ФОРУМА

if (is_file("$datadir/user.php")) { // считываем имя последнего зарегистрировавшегося
$userlines=file("$datadir/user.php"); $dayx="";
$usercount=count($userlines); $ui=$usercount-1; $uu=$ui;
$tdt=explode("|", $userlines[$ui]);} else { $fp=fopen("$datadir/user.php","a+"); fputs($fp,"<?die;?>\r\n"); fflush ($fp); fclose($fp); $ui=""; $tdt[0]="";}
$today=time();

if (!isset($_GET['id'])) {
if (mb_strlen($frname)>1) {$frname=str_replace('»','',$frname); $frname="» <B>$frname</B>";}
print"<table><tr><td><span class=nav>&nbsp;&nbsp;&nbsp;<a href='$forum_url' class=nav>$forum_name</a> $frname</span></td></tr></table>
<table width=100% cellpadding=2 cellspacing=1 class=forumline>
<tr>
<th width=62% colspan=2 class=thCornerL height=25>Форумы</th>
<th width=7% class=thTop>Тем</th>
<th width=7% class=thCornerR>Ответов</th>
<th width=28% class=thCornerR nowrap=nowrap>Обновление</th>
</tr>";


// Выводим все РУБРИКИ НА ГЛАВНОЙ
$adminmsg=""; if (is_file("$datadir/wrforum.csv")) $mainlines=file("$datadir/wrforum.csv");
if (!isset($mainlines)) $datasize=0; else $datasize=sizeof($mainlines);
if ($datasize<=0) {if (is_file("$datadir/wrf-copy.csv")) {$mainlines=file("$datadir/wrf-copy.csv"); $datasize=sizeof($mainlines);} $adminmsg="<font color=red><B>Администратор, внимание!!!</B> Файл БД с рубриками повреждён. Восстановите его из резервной копии в админке!</font><br>";}
if ($datasize<=0) exit("Проблемы с Базой данных - обратитесь к администратору");
$i=count($mainlines); 
$n="0"; $u=$i-1; $a1=$rfid-1; $fid="0"; $itogotem="0"; $itogomsg="0"; $alt=""; $konec="";


do {$a1++; $dt=explode("|", $mainlines[$a1]);

if (isset($dt[6])) { // Если строчка потерялась в скрипте (пустая строка) - то просто её НЕ выводим

if ($dt[3]==TRUE and isset($_GET['razdel'])) $konec++; else {

// определяем тип: топик или заголовок
if ($dt[3]==TRUE) print "<tr height=28><td class=catLeft colspan=2 align=center><a href='index.php?razdel=$dt[2]'>$dt[4]</a></td><td class=rowpic colspan=4 align=right>&nbsp;</td></tr>";
 else {
// $dt[12] - дата размещения сообщения; $wrftime2 - последнее посещение
// Если $dt[12] раньше (т.е. больше) $wrftime2 значит раздел форума - новый
$fid=replacer($dt[2]);

$alt="";

$newtema=""; $page=1; $msgsize=""; $pageadd="";
if (is_file("$datadir/$dt[11].csv")) { $msgsize=sizeof(file("$datadir/$dt[11].csv")); // считаем кол-во страниц в файле
if (mb_strlen($dt[13])>20) {$dt[13]=mb_substr($dt[13],0,20); $dt[13].="..";}
if (mb_strlen($dt[14])>28) {$dt[14]=mb_substr($dt[14],0,28); $dt[14].="..";}
if ($msgsize>$msg_onpage) $page=ceil($msgsize/$msg_onpage); else $page=1;
if ($page!=1) $pageadd="&page=$page";
if (mb_strlen($dt[12])>3) {if (date("d.m.Y",$dt[12])==$date)  $dt[12]="<B>сегодня</B> в ".csve("H:i",$dt[12]); else $dt[12]=date("d.m.y - H:i",$dt[12]);}
$newtema="<span class=gensmall>тема: <a href=\"index.php?id=$dt[11]$pageadd#m$msgsize\" title='$dt[14]'>$dt[14]</a> <BR>автор: <B>$dt[13]</B><BR>дата: $dt[12]</span>";
} // is_file...$dt[11]

$dt[8]=mb_substr($dt[8],0,-3);
$dt[10]=replacer($dt[10]);
$itogotem=$itogotem+$dt[6];
$itogomsg=$itogomsg+$dt[7];

$maxzvezd=null;

print "<tr align=center valign=middle height=50>
<td width=3% class=row1></td>
<td width=60% class=row1 align=left><span class=forumlink><a href=\"index.php?id=$fid\">$dt[4]</a> $maxzvezd<BR></span><small>$dt[5]</small></td>
<td class=row2><small>$dt[6]</small></td>
<td class=row2><small>$dt[7]</small></td>
<td class=row2 align=left>$newtema</td></tr>\r\r\n";

 }} 
if ($konec==2) $a1=$u;
} // if isset($dt[6]
} while($a1 < $u);
echo('</table><BR>');



if ($navigate==TRUE) {  // БЫСТРЫЙ ПЕРЕХОД к теме, если разрешено. 2018
echo '<table width=100% cellpadding=3 cellspacing=1 class=forumline><TR><TD class=catHead><span class=cattitle>Навигация</span></td></tr>
<tr><td class=row1 align=right><span class=gensmall>
Быстрый переход по рубрикам &nbsp; <select onchange="window.location=(\'index.php?id=\'+this.options[this.selectedIndex].value)">
<option>Выберите рубрику</option>';
$ii=count($mainlines); $cn=0; $i=0;
do {$dt=explode("|",$mainlines[$i]);
if ($dt[3]==TRUE) {if ($cn!=0) {echo'</optgroup>'; $cn=0;} $cn++; print"<optgroup label='$dt[4]'>";} else print" <option value='$dt[2]'>|-$dt[4]</option>";
$i++;} while($i<$ii);
echo'</optgroup></select></TD></TR></TABLE><br>'; } // $navigate=TRUE
















// изменения в блоке 24.02.2018 г.

if ($statistika==TRUE and !isset($_GET['razdel'])) { // СТАТИСТИКА ИТОГО ТЕМ/СООБЩЕНИЙ/ПРАВА ЮЗЕРОВ

if ($g_add_tema==TRUE) $c1="разрешено"; else $c1="запрещено";
if ($g_add_msg ==TRUE) $c2="разрешено"; else $c2="запрещено";
$codename=urlencode($tdt[2]);

print"<table width=100% cellpadding=3 cellspacing=1 class=forumline><tr><th class=catHead colspan=2 height=28><span class=cattitle>Статистика</span></th></tr><tr>
<td class=row1 align=center valign=middle rowspan=2></td>
<td class=row1 align=left width=95%><span class=gensmall>
Создано: <b>$itogotem</b> тем<BR>Написано: <b>$itogomsg</b> сообщений<br>Зарегистрированных: <b>";
if (!isset($wrfname)) print"$ui"; else print"<a href=\"tools.php?event=who\">$ui</a>";
echo'</b> пользователей<br>Последним зарегистрировался: <B>';
if (!isset($wrfname)) print"$tdt[2]"; else print"<a href=\"tools.php?event=profile&pname=$codename\">$tdt[2]</a>";
print"</B><BR> Гостям <B>$c1</B> создавать темы и <B>$c2</B> отвечать в темах<BR>";

print"$adminmsg</span></td></tr></table>"; 




// СТАТИСТИКА -= Последние сообщения с форума =-
if (is_file("$datadir/news.csv")) { $newmessfile="$datadir/news.csv";
$lines=file($newmessfile); $i=count($lines); //if ($i>10) $i=10; (РАСКОМЕНТИРУЙ - ВОТ ГДЕ СИЛА!!! ;-))
if ($i>0) {
echo('<br><table width=100% cellpadding=3 cellspacing=1 class=forumline>
<tr><th class=catHead colspan=3 height=28><span class=cattitle>Последние сообщения</span></th></tr><tr>
<td class=row1 align=left valign=top width=75%><span class=gensmall><UL>');

$mmax=count($mainlines);
$a1=$i-1;$u="-1"; // выводим данные по возрастанию или убыванию
do {$dt=explode("|",$lines[$a1]); $a1--;

if (isset($dt[1])) { // Если строчка потерялась в скрипте (пустая строка) - то просто её НЕ выводим
//print"$dt[14]<br><br>"; 
$msg=htmlspecialchars($dt[14],ENT_COMPAT,"UTF-8"); // с 2018г.
$msg=replacer($dt[14]);
$msg=str_replace('[b]'," ",$msg); $msg=str_replace('[/b]'," ",$msg);
$msg=str_replace('[RB]'," ",$msg); $msg=str_replace('[/RB]'," ",$msg);
$msg=str_replace('[Code]'," ",$msg); $msg=str_replace('[/Code]'," ",$msg);
$msg=str_replace('[Quote]'," ",$msg); $msg=str_replace('[/Quote]'," ",$msg);
$msg=str_replace('[img]'," картинка: ",$msg); $msg=str_replace('[/img]'," ",$msg);
$msg=str_replace("[br]","\r\n", $msg);
$msg=str_replace("<br>","\r\n", $msg);
$msg=str_replace("'","`",$msg);
$msg=str_replace('&amp;lt;br&amp;gt;'," \r\r\n", $msg);
$msg=str_replace('&lt;br&gt;'," \r\r\n", $msg);

$k=$mmax; $mainr=""; // Ищем название рубрики, как находим - присваимваем значение и выходим из цикла!
do {$k--; $mdt=explode("|",$mainlines[$k]);
if ($mdt[2]==$dt[2]) {$mainr="<a href='".$forum_url."index.php?id=$mdt[2]' class=nav>$mdt[4]</a>"; $k=0;}
} while($k>0);

if (date("d.m.Y",$dt[4])==$date)  $dt[4]="сегодня в ".csve("H:i",$dt[4]); else $dt[4]=date("d.m.y - H:i",$dt[4]);

if ($dt[17]>$msg_onpage) $page=ceil($dt[17]/$msg_onpage); else $page=1; // Считаем страницу
if ($page!=1) $pageadd="&page=$page"; else $pageadd="";

if ($dt[6]==TRUE) {$codename=urlencode($dt[8]); if (!isset($wrfname)) $name="$dt[8]"; else $name="<B><a href='tools.php?event=profile&pname=$codename'>$dt[8]</a></B>";} else $name="гость $dt[8]";
print"$dt[4]: 
<strong>$mainr</strong> » <B><a href='index.php?id=$dt[2]$dt[3]$pageadd#m$dt[17]' title='$msg \r\n\r\n Отправлено $dt[4]'>$dt[5]</a></B> - $name.<br>";
} // если строчка потерялась
$a11=$u; $u11=$a1;
} while($a11 < $u11);

echo'</UL></span></td></tr>';

echo'</tr></table>';

}

} // Конец блока последних сообщений

} // конец if (statistika==TRUE)

} // if !isset($id) // конец главной страницы

















if (isset($_GET['id'])) {  // страница С ТЕМАМИ выбранной РУБРИКИ

$id=$_GET['id']; if (mb_strlen($id)==3) { $fid=$id;

// Защиты
if (!ctype_digit($fid) or mb_strlen($fid)>3) exit("$back. Номер рубрики должен быть цифровым и содержать 3 символа!");
$imax=count($mainlines); if (($fid>999) or (mb_strlen($fid)==0)) exit("<b>Данный раздел удалён или не существует.</b>");

// Исключаем ошибку вызова несуществующей страницы
if (!isset($_GET['page'])) $page=1; else {$page=$_GET['page']; if (!ctype_digit($page)) $page=1; if ($page<1) $page=1;}

// Уточняем статус по кол-ву ЗВЁЗД юзера. Если меньше допустимых N в этой рубрике - то досвиданья!
$maxzd=null;
$addbutton="<table width=100%><tr><td align=left valign=middle>";

if ($forum_lock!=TRUE) $addbutton.="<span class='pgbutt'><a name='add' href=\"index.php?newtema=add&id=$fid#add\">Создать новую тему</a>&nbsp;</span></td>";
else $addbutton.="Извините за неудобство, но администратор временно приостановил добавление тем и сообщений!";



// определяем есть ли информация в файле с данными
if (is_file("$datadir/$fid.csv")) {
$msglines=file("$datadir/$fid.csv"); $maxi=count($msglines); $i=$maxi;

if (isset($_POST['findme']) or isset($_GET['findme'])) {
// ЕСЛИ есть фильтр по названию темы, то:
// - Считываем файл с темами и отбираем в отдельный массив только те, которые содаржат в названии искомую фразу
// - в $maxi записываем кол-во тем
// - в $msglines[$i] записываем данные
setlocale(LC_ALL,'Russian_Russia.65001'); // 11.2018! РАЗРЕШАЕМ РАБОТУ ФУНКЦИЙ, работающих с регистром и с РУССКИМИ БУКВАМИ
if (isset($_POST['findme'])) $findme=replacer($_POST['findme']);
if (isset($_GET['findme'])) { $findme=replacer($_GET['findme']); $findme=urldecode($findme);}
$stroka=mb_strlen($findme,"UTF-8"); // - изменение 01-2019
if($stroka<3 or $stroka>30) exit("разрешается поиск в количестве от 3-х до 30-и символов!");
$tmplines=$msglines; $msglines=null; $i=0;
foreach($tmplines as $v) {$dt=explode("|", $v); if (stristr($dt[5],$findme,false,'utf-8')) {$msglines[$i]=$v; $i=$i+1;}}
$maxi=$i;} else $findme=""; //  $maxi=$i; - изменение 01-2019

$frname=str_replace(' »','',$frname); //вырезаем лишние символы
print"<table width=100%><TR>
<td><span class=nav>&nbsp;&nbsp;&nbsp;<a href='$forum_url' class=nav>$forum_name</a> » <a href='index.php?id=$id'><B>$frname</B></a> »</span></td>

<TD align=right><form action='index.php?id=$fid&find' method=POST name=finder>Фильтр по названию темы: 
<input name='findme' value='$findme' class=post type='text' maxlength=30 size=20>
<input type=submit class=mainoption value='Искать'></form></td></tr></table>

<table width=100% cellpadding=2 cellspacing=1 class=forumline><tr>
<th width=60% colspan=2 class=thCornerL height=25>Тема</th>
<th width=10% class=thTop nowrap=nowrap>Cообщений</th>
<th width=12% class=thCornerR nowrap=nowrap>Автор</th>
<th width=18% class=thCornerR>Обновления</th>
</tr>";

if ($maxi>0) {

if ($maxi>$maxtem-1) $addbutton="<table width=100%><TR><TD>Количество допустимых тем в рубрике исчерпано.";


// БЛОК СОРТИРОВКИ: последние ответы ВВЕРХУ (по времени создания файла с темой)!
do {$i--; $dt=explode("|", $msglines[$i]);
   $filename="$dt[2]$dt[3].csv"; if (is_file("$datadir/$filename")) $ftime=filemtime("$datadir/$filename"); else $ftime="";
   $newlines[$i]="$dt[10]|$ftime|$dt[2]$dt[3]|$i|";
} while($i > 0);
sort($newlines);
//print"<PRE>"; print_r($newlines); exit;
// $newlines - массив с данными: ДАТА | ИМЯ_ФАЙЛА_С_ТЕМОЙ | № п/п |
// $msglines - массив со всеми темами выбранной рубрики
$i=$maxi;
do {$i--; $dtn=explode("|", $newlines[$i]);
  $numtp="$dtn[3]"; $lines[$i]="$msglines[$numtp]";
} while($i > 0);
// КОНЕЦ блока сортировки

// Показываем tem_onpage ТЕМ
$fm=$maxi-$tem_onpage*($page-1);
if ($fm<"0") $fm=$tem_onpage; $lm=$fm-$tem_onpage; if ($lm<"0") $lm="0";

do {$fm--; $num=$fm+2;
$dt=explode("|", $lines[$fm]);

// нужно для определения темы на VIP-статус
$dtn=explode("|", $newlines[$fm]);
$timer=time()-$dtn[0]; // узнаем сколько прошло времени (в секундах) 


$filename="$dt[2]$dt[3]"; 
if (is_file("$datadir/$filename.csv")) { // если файл с темой существует - то показать тему в списке!
$msgsize=sizeof(file("$datadir/$filename.csv"));

$linetmp=file("$datadir/$filename.csv"); if (sizeof($linetmp)!=0) {
$pos=$msgsize-1; $dtt=explode("|", $linetmp[$pos]);
}

print "<tr height=50 align=center valign=middle>
<td width=3% class=row1></td>
<td class=row1 align=left><span class=forumlink><b>";

if ($dt[10]==TRUE) echo'<font color=red>VIP </font>';

$dt[5]=replacer($dt[5]);
print"<a href=\"index.php?id=$dt[2]$dt[3]\" title='$dt[5]'>$dt[5]</a></B>";

if ($msgsize>$msg_onpage) { // ВЫВОДИМ СПИСОК ДОСТУПНЫХ СТРАНИЦ ТЕМЫ
$maxpaget=ceil($msgsize/$msg_onpage); $addpage="";
echo'</b></span><small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div style="padding:6px;" class=pgbutt>Страницы: ';
if ($maxpaget<=5) $f1=$maxpaget; else $f1=5;
for($i=1; $i<=$f1; $i++) {if ($i!=1) $addpage="&page=$i"; print"<a href=index.php?id=$dt[2]$dt[3]$addpage>$i</a> &nbsp;";}
if ($maxpaget>5) print "... <a href=index.php?id=$dt[2]$dt[3]&page=$maxpaget>$maxpaget</a>"; }


print"</div></td><td class=row2>$msgsize</td><td class=row2><span class=gensmall>";

$codename=urlencode($dt[8]);
if ($dt[6]==TRUE) {
if (!isset($wrfname)) print "$dt[8]"; else print "<a href='tools.php?event=profile&pname=$codename'>$dt[8]</a>";
print"<BR><small>$user_name</small>"; } else print"$dt[8]<BR><small>$guest_name</small>";

// защита if (strlen...) только если файл есть и имеет верный формат - выводим
if ($msgsize>=2) {$linesdat=file("$datadir/$filename.csv"); $dtdat=explode("|", $linesdat[$msgsize-1]);
if (mb_strlen($linesdat[$msgsize-1])>10) {$dt[0]=$dtdat[0]; $dt[1]=$dtdat[1]; $dt[2]=$dtdat[2]; $dt[5]=$dtdat[5]; $dt[6]=$dtdat[6];}}

if (date("d.m.Y",$dtt[4])==$date)  $dtt[4]="<B>сегодня</B> в ".csve("H:i:s",$dtt[4]); else $dtt[4]=date("d.m.y - H:i",$dtt[4]);
print "</span></td><td class=row2 align=left><span class=gensmall>Автор: <B>$dtt[8]</B><BR>дата/время: $dtt[4]</span></td></tr>\r\r\n";

} //if is_file

} while($lm < $fm);

// формируем переменную $pageinfo - со СПИСКОМ СТРАНИЦ
if (mb_strlen($findme)>1) $findadd="&findme=$findme"; else $findadd="";
$pageinfo=""; $addpage=""; $maxpage=ceil(($maxi+1)/$tem_onpage); if ($page>$maxpage) $page=$maxpage;
$pageinfo.="<div style='padding:6px;' class=pgbutt>Страницы: &nbsp;";
if ($page>3 and $maxpage>5) $pageinfo.="<a href=index.php?id=$fid$findadd>1</a> ... ";
$f1=$page+2; $f2=abs($page-2); if ($f2=="0") $f2=1; if ($page>=$maxpage-1) $f1=$maxpage;
if ($maxpage<=5) {$f1=$maxpage; $f2=1;}
for($i=$f2; $i<=$f1; $i++) { if ($page==$i) $pageinfo.="<B>$i</B> &nbsp;"; 
else {if ($i!=1) $addpage="&page=$i"; $pageinfo.="<a href=index.php?id=$fid$addpage$findadd>$i</a> &nbsp;";} }
if ($page<=$maxpage-3 and $maxpage>5) $pageinfo.="... <a href=index.php?id=$fid&page=$maxpage$findadd>$maxpage</a>";
$pageinfo.='</div>';

print"
$addbutton<TD><table width=100%><tr><td align=right colspan=3>
$pageinfo</b></span></td></tr></table>";

} else print"$addbutton";

} else print"$addbutton";

echo'</tr></table><BR>';


if (isset($_GET['newtema'])) { if ($g_add_tema==FALSE and !isset($wrfname)) print"<center><h5>Администратор запретил создавать гостям темы! Для регистрации пройдите по ссылке: <B><a href='tools.php?event=reg'>зарегистрироваться</a></B></h5></center><BR><BR>"; else {
$maxzag=$maxzag-10; // так нужно!!!
print"<form action=\"index.php?add_tema&id=$fid\" method=post enctype=\"multipart/form-data\" name=REPLIER><table width=100% class=forumline><tr><td class=catHead colspan=2 height=28><span class=cattitle>Добавление темы</span></td></tr>
<tr><td class=row1 align=right valign=top>Заголовок темы</TD><TD class=row2>
<input type=hidden name=maxzd value='$maxzd'><input type=text class=post name=zag maxlength=$maxzag size=70>
</TD></TR>";
addmsg("");
} }


if ($maxi>0) { // БЫСТРЫЙ ПЕРЕХОД к теме
if ($navigate==TRUE) {  // БЫСТРЫЙ ПЕРЕХОД к теме, если разрешено. 2018
echo '<br><table width=100% cellpadding=3 cellspacing=1 class=forumline><TR><TD class=catHead><span class=cattitle>Навигация</span></td></tr>
<tr><td class=row1 align=right><span class=gensmall>
Быстрый переход по темам &nbsp; <select onchange="window.location=(\'index.php?id=\'+this.options[this.selectedIndex].value)">
<option>Выберите тему</option>';
$ii=$maxi; $cn=0; $i=0;
do {$dt=explode("|", $lines[$i]); print" <option value='$dt[2]$dt[3]'>$dt[5]</option>"; $i++;} while($i<$ii);
echo'</optgroup></select></TD></TR></TABLE>'; }} // if($maxi>0)
}








// показываем СООБЩЕНИЯ выбранной темы
if (mb_strlen($id)>6) { $fid=mb_substr($id,0,3);

// определяем есть ли информация в файле с данными
if (!is_file("$datadir/$id.csv")) exit("$back. Извините, но такой темы на форуме не существует.<BR> Скорее всего её удалил администратор.");
$lines=file("$datadir/$id.csv"); $mitogo=count($lines); $i=$mitogo; $maxi=$i-1;

if ($mitogo>0) { $tblstyle="row1"; $printvote=null;

// Считываем СТАТИСТИКУ ВСЕХ УЧАСТНИКОВ
if (is_file("$datadir/userstat.csv")) {$ufile="$datadir/userstat.csv"; $ulines=file("$ufile"); $ui=count($ulines)-1;}

// Ищем тему в списке тем ХХХ.csv - проверяем не закрыта ли тема? и сразу же ищем есть ли в топике
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


$i=count($mainlines); $imax=$i; $maxzd=null;




// Исключаем ошибку вызова несуществующей страницы
if (!isset($_GET['page'])) $page=1; else {$page=$_GET['page']; if (!ctype_digit($page)) $page=1; if ($page<1) $page=1;}

$fm=$msg_onpage*($page-1); if ($fm>$maxi) $fm=$maxi-$msg_onpage;
$lm=$fm+$msg_onpage; if ($lm>$maxi) $lm=$maxi+1; if ($maxi==1) {$fm=0; $lm=2;}

// формируем переменную $pageinfo - со СПИСКОМ СТРАНИЦ
$pageinfo=""; $addpage=""; $maxpage=ceil(($maxi+1)/$msg_onpage); if ($page>$maxpage) $page=$maxpage;
$pageinfo.="<div align=center style='padding:6px;' class=pgbutt>Страницы: &nbsp;";
if ($page>3 and $maxpage>5) $pageinfo.="<a href=index.php?id=$id>1</a> ... ";
$f1=$page+2; $f2=abs($page-2); if ($f2=="0") $f2=1; if ($page>=$maxpage-1) $f1=$maxpage;
if ($maxpage<=5) {$f1=$maxpage; $f2=1;}
for($i=$f2; $i<=$f1; $i++) { if ($page==$i) $pageinfo.="<B>$i</B> &nbsp;"; 
else {if ($i!=1) $addpage="&page=$i"; $pageinfo.="<a href=index.php?id=$id$addpage>$i</a> &nbsp;";} }
if ($page<=$maxpage-3 and $maxpage>5) $pageinfo.="... <a href=index.php?id=$id&page=$maxpage>$maxpage</a>";
$pageinfo.='</div>';

$qm=null; $flag=0;
do {$dt=explode("|",replacer($lines[$fm]));

$youwr=null; $fm++; $num=$maxi-$fm+2; $status="";

if (mb_strlen($lines[$fm-1])>5) { // Если строчка потерялась в скрипте (пустая строка) - то просто её НЕ выводим

if (isset($_GET['quotemsg'])) {
$quottime=date("d.m.y в H:i",$dt[4]);
$quotemsg=replacer($_GET['quotemsg']); if(ctype_digit($quotemsg) and $quotemsg==$fm) $qm="[Quote][b]$dt[8] $quottime пишет:[/b]\r\n".$dt[14]."[/Quote]";}

$msg=str_replace("[b]","<b>",$dt[14]);
$msg=str_replace("[/b]","</b>", $msg);
$msg=str_replace("[RB]","<font color=red><B>", $msg);
$msg=str_replace("[/RB]","</B></font>", $msg);
$msg=str_replace("&lt;br&gt;","<br>",$msg); // ЗАКОМЕНТИРОВАТЬ при ЧИСТОЙ установке скрипта или в 2017 году!
$msg=str_replace("[br]","<br>",$msg); // c 2015 г.
$msg=str_replace("[Quote]","<br><UL><B><U><small>Цитата:</small></U></B><table width=95% cellpadding=5 cellspacing=1 style=\"margin-left:18px;padding:5px;\"><tr><td class=quote>",$msg); $msg=str_replace("[/Quote]","</td></tr></table></UL>",$msg);
$msg=str_replace("[Code]","<br><UL><B><U>Код:</U></B><table width=95% cellpadding=10 cellspacing=1 style=\"margin-left:18px;padding:5px;\"><tr><td class=code>",$msg); $msg=str_replace("[/Code]","</td></tr></table></UL>",$msg);

// запускать новую конструкцию в цикле. Протестировать! Пока что работает криво! 2016 г.
//$msg=preg_replace("/(\[Quote\])(.+?)(\[\/Quote\])/is","<br><UL><B><U><small>Цитата:</small></U></B><table width=95% cellpadding=5 cellspacing=1 style=\"margin-left:18px;padding:5px;\"><tr><td class=quote>$2</td></tr></table></UL>",$msg);
//$msg=preg_replace("/(\[Code\])(.+?)(\[\/Code\])/is","<br><UL><B><U>Код:</U></B><table width=95% cellpadding=10 cellspacing=1 style=\"margin-left:18px;padding:5px;\"><tr><td class=code>$2</td></tr></table></UL>",$msg);

if ($showsmiles==TRUE) { // СМАЙЛИКИ
$i=count($smiles)-1; for($k=0; $k<$i; $k=$k+2)
{$j=$k+1; $msg=str_replace("$smiles[$j]","<img src='smile/$smiles[$k].gif' border=0>",$msg);}}

// Если разрешена публикация УРЛов
if ($liteurl==TRUE) {$msg=' '.$msg; $msg=preg_replace ("/([^\[img\]])((https|http|ftp)+(s)?:(\/\/)([\w]+(.[\w]+))([\w\-\.,@?^=%&:;\/~\+#]*[\w\-\@?^=%&:;\/~\+#])?)/i", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $msg); $msg=ltrim($msg);}

// После замены АДРЕСА URL преобразуем код [img]
$msg=preg_replace('#\[img\](.+?)(jpg|gif|jpeg|png|bmp)\[/img\]#','<img src="$1$2" border="0">',$msg);

// Вставляем видео с ЮТУБ
$msg=preg_replace("/(\[Youtube\])(.+?)(\[\/Youtube\])/is","<center><object width=480px height=360px><param name=movie value=\"https://www.youtube.com/v/$2\"></param>
<param name=allowFullScreen value=true></param><param name=allowscriptaccess value=always></param>
<embed src=\"https://www.youtube.com/v/$2\" type=\"application/x-shockwave-flash\" allowscriptaccess=always allowfullscreen=true width=480px height=360px></embed></object></center>",$msg);

// считываем в память данные по пользователю
if ($dt[6]==TRUE) { $iu=$usercount; $predup="0";
do {$iu--; $du=explode("|", $userlines[$iu]); if ($du[0]==$dt[7]) { 
$reiting=$du[4]; $youavatar=$du[15]; $email=$du[5]; $icq=$du[10]; $site=$du[11]; $userpn=$iu;

if (isset($_COOKIE['wrfcookies'])) { $youwr=' '.$du[14];
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

if ($flag==FALSE) { // БЛОК ПЕЧАТАЕМ ОДИН РАЗ
$frname=str_replace(' »','',$frname); $frtname=str_replace(' »','',$frtname); //вырезаем лишние символы
$linkus="<table><tr><td><span class=nav><a href=\"$forum_url\" class=nav>$forum_name</a> » <a href=\"index.php?id=$fid\" class=nav>$frname</a> » <a href=\"index.php?id=$id\" class=nav><strong>$frtname</strong></a></span></td></tr></table>";
$flag=TRUE; print"<br>$pageinfo $linkus";

// РЕКЛАМНЫЙ БЛОК
if ($reklama==1) print"<table class=forumline align=center width=100% cellspacing=1 cellpadding=2><tr><th class=thHead height=20>$reklamatitle</th></tr><tr><td class=row1><br>$reklamatext<br></td></table>";

print"<table class=forumline width=100% cellspacing=1 cellpadding=3><tr><th class=thLeft width=160 height=26 nowrap=nowrap>Автор</th><th class=thRight>Сообщение</th>";
} // if $flag==FALSE)

print"</tr><tr height=150><td class=$tblstyle valign=top><span class=name><BR><center>";

// Проверяем: это гость?

if (!isset($youwr)) {if (mb_strlen($dt[9])>5) print"$dt[8] "; else print"$dt[8] "; $kuda=$fm-1; print" <a href='javascript:%20x()' name='m$fm' onclick=\"DoSmilie('[b]$dt[8][/b], ');\" class=nav>&bull;</a><BR><br>";

// 04-01-2019 если нет аватара добавляем чудо через css. если border-radius 100% - это круг
$pb=mb_substr("$dt[8]",0,1,"UTF-8");
$pb=mb_convert_case($pb, MB_CASE_UPPER, "UTF-8");
print"<style>
.content{width:120px;height:120px;text-align:center;background: #FFDEAD;color: #4E5754;font-size: 50px;
		display: table-cell;border-radius: 20%;position: relative;vertical-align: middle;}
</style>
<div class=content>$pb</div>";	


// если емайл указан, печатаем форму для отправки ЛС
print"<small>$guest_name</small><BR>";
if (mb_strlen($dt[9])>5) print"
<form method='post' action='tools.php?event=mailto' target='email' onclick=\"window.open('tools.php?event=mailto','email','width=700,height=400,left=100,top=100');return true;\">
<input type=hidden name='email' value='$dt[9]'><input type=hidden name='name' value='$dt[8]'><input type=hidden name='id' value='$id'>
<input type=button value='ЛС'></form>";
}


else {

// новый блок с 2016 г.
if (isset($youwr) and is_file("$datadir/userstat.csv")) { // ТОЛЬКО участники видят всю репутацию! ;-)
if (isset($ulines[$userpn])) {
if (mb_strlen($ulines[$userpn])>5) {
$ddu=explode("|",replacer($ulines[$userpn]));
$winop="window.open('tools.php?event=repa&name=$dt[8]&who=$userpn','repa','width=600,height=600,left=50,top=50,scrollbars=yes')";

// БЛОК надо бы ПОТЕСТИРОВАТЬ! с 2012 г.
if (mb_strlen($ddu[9])>1) $status=$ddu[9];
$si=0; $repuser=$ddu[7]; // Репутация пользователя
for ($si=0;$si<7;$si++) if ($repuser>=$userrepa[$si]) $stp=$si;
$si=$stp+1;

if ($repuser>$userrepa[$si]) {$title="Набрано масксимальное количество рейтинга. Репутация более ".$userrepa[7]." единиц! Ваш статус: ".$userstatus[7]."! Поздравляем!!!"; $statpro=100;
} else {

$statd=$userrepa[$si]-$userrepa[$si-1];
$statt=$repuser-$userrepa[$si-1];
$statpro=intval(($statt*100)/$statd); if ($statpro=="0") $statpro=1;
$tekstatus=$userstatus[$si-1]; if (mb_strlen($status)<1) $status=$tekstatus;
$nextstatus=$userstatus[$si];

if (!stristr($status,"Администратор") and !stristr($status,"модератор")) $title="В текущем статусе &quot;$tekstatus&quot; набрано $statpro% рейтинга. Смена статуса на &quot;$nextstatus&quot; произойдет при репутации $userrepa[$si]";
else $title="$dt[8], статус администратор не зависит от репутации. В текущем диапазоне набрано $statpro% рейтинга";
} // if ($repuser>$userrepa[$si])
// КОНЕЦ НОВОГО БЛОКА


$codename=urlencode($dt[8]);
if (!isset($wrfname)) print"$dt[8]"; else print"<a name='m$fm' href='tools.php?event=profile&pname=$codename' class=nav>$dt[8]</a>";
print" <a href='javascript:%20x()' name='m$fm' onclick=\"DoSmilie('[b]$dt[8][/b], ');\" class=nav>&bull;</a><BR><BR><small>";
if (mb_strlen($status)>2 & $dt[6]==TRUE & isset($youwr)) print"$status"; else print"$user_name";
if (isset($reiting)) {if ($reiting>0) {echo'<BR>'; if (is_file("$forum_skin/star.gif")) {for ($ri=0;$ri<$reiting;$ri++) {print"<img src='$forum_skin/star.gif' border=0> ";} } }}

}





// Статистика пользователя
print"</small></span>
<noindex><br><br><fieldset STYLE='color:#646464'>
<legend STYLE='font-weight:bold;'>Статистика:</legend>
<div style='PADDING:3px;' align=left class=gensmall>Тем создано: $ddu[5]<br>Сообщений: $ddu[6]<br>Репутация: $ddu[7]<br>
<table align=left width=80% cellpadding=0 cellspacing=1 style='border-width:1;border-style:solid;border-color:#bbbbbb;cursor:help' title='$title'>
<tr><td width=\"$statpro%\" height=5 style='background-color:green;'></td><td></td></tr></table>
</div></fieldset>
</noindex>"; }}}
// Конец блока "статистика пользователя"







print "</td>
<td class=$tblstyle width=100% rowspan=2 valign=top><span class=postbody><UL>$msg</UL></span>";


print"";






// Если имя юзер участник и имя юзера=имени в сообщении и сообщение последнее, то вывести кнопку удаления
$codename=urlencode($dt[8]);

if (date("d.m.Y",$dt[4])==$date)  $dt[4]="сегодня в ".csve("H:i",$dt[4]); else $dt[4]=date("d.m.y - H:i",$dt[4]);

$addpage=""; if ($page>1) $addpage="&page=$page"; // нужно для цитирования
print"</td></tr><tr>
<td class=row3 valign=middle align=center><span class=postdetails><I>Сообщение # <B>$fm</B></I>
$dt[4]</span></td>
<td class=row3 width=100% height=28></td></tr>";

print"<tr><td class=spaceRow colspan=2 height=1></td>";

} // если строчка потерялась
} while($fm < $lm);


// Предыдущая и следующая тема
if ($lasttema!=FALSE) {$lasttema--; $ldt=explode("|",$msglines[$lasttema]); $lasttema="<TD align=left>&#9668; <B><a href='index.php?id=$ldt[2]$ldt[3]'>$ldt[5]</a></B> :Предыдущая тема</TD>";} else $lasttema="";
if ($nexttema!=FALSE) {$ndt=explode("|",$msglines[$nexttema]); $nexttema="<TD align=right>Следующая тема: <B><a href='index.php?id=$ndt[2]$ndt[3]'>$ndt[5]</a></B> &#9658;";} else $nexttema="<TD>";

print"</tr></table> $linkus <table cellSpacing=0 cellPadding=0 width=100%><TR height=25>$lasttema$nexttema</TD></tr></table> $pageinfo<br>";

if ($g_add_msg==FALSE and !isset($wrfname)) print"<center>Администратор запретил отвечать гостям на сообщения! Для регистрации пройдите по ссылке: <B><a href='tools.php?event=reg'>зарегистрироваться</a></B></center><BR><BR>"; else {
if ($closed==FALSE) {

if (isset($_COOKIE['wrfcookies'])) {$wrfc=$_COOKIE['wrfcookies']; $wrfc=htmlspecialchars($wrfc,ENT_COMPAT,"UTF-8"); $wrfc=stripslashes($wrfc); $wrfc=explode("|", $wrfc); $wrfpass=replacer($wrfc[1]);} else {unset($wrfpass); $wrfpass="";}

if ($forum_lock==FALSE) {
print "
<form action=\"index.php?add_msg&id=$id\" method=post name=REPLIER enctype=\"multipart/form-data\">
<input type=hidden name=userpass value=\"$wrfpass\">
<input type=hidden name=page value='$page'>
<input type=hidden name=maxzd value='$maxzd'>
<table cellpadding=3 cellspacing=1 width=100% class=forumline>
<tr><th class=thHead colspan=2 height=25><b>Сообщение</b></th></tr>";

addmsg($qm);
} else echo'<center>Извините за неудобство, но администратор временно приостановил добавление тем и сообщений!';
} else echo'<center><font style="font-size: 16px;font-weight:bold;"><BR>Тема закрыта для обсуждения!<BR><BR>';
}}
}
} // if isset($id)

if (is_file("data/bottom.html")) include("data/bottom.html"); // подключаем НИЖНИЙ БЛОК форума

?>
</td></tr></table>
<center><small>Powered by <a href="https://mamon.host" title="скрипт php форума" class="copyright">MAMON DEV</a> &copy; RM CLoud Data ver 1.1</small></center>
</body>
</html>