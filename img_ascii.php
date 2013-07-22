<?
include_once("include_generic/credentials.inc.php");
include_once("include_generic/functions.inc.php");

$ts=microtime();

/*
if(!isset($s)) $s=4;
if(!isset($r1)) $r1=0xff;
if(!isset($g1)) $g1=0xff;
if(!isset($b1)) $b1=0xff;
if(!isset($r2)) $r2=0x44;
if(!isset($g2)) $g2=0x66;
if(!isset($b2)) $b2=0x88;
*/
$s=4;
$r1=0xff;
$g1=0xff;
$b1=0xff;
$r2=0x44;
$g2=0x66;
$b2=0x88;

$r3=$r2-32;
$g3=$g2-32;
$b3=$b2-32;
if($r3<0) $r3=0;
if($g3<0) $g3=0;
if($b3<0) $b3=0;

$path = "";
if ($_GET["nfo"])
{
  $path = get_local_nfo_path($_GET["nfo"]);
}
elseif ($_GET["results"])
{
  $path = get_local_partyresult_path($_GET["results"],$_GET["year"]);
}
elseif ($_GET["boardnfo"])
{
  $path = get_local_bbsnfo_path($_GET["boardnfo"]);
}

if(!$path || !file_exists($path))
{
  $txt=array(
    "���������Ŀ  ",
    "�WRONG URL�  ",
    "�����������  ");
  $s=0;
  $f=2;
}
else
{
  $wrap = 100;
  $txtLong = file($path);
  $txt = array();
  foreach($txtLong as $k=>$v)
  {
    if (strlen($v) > $wrap)
    {
      $txt = array_merge($txt, preg_split("/\n/",chunk_split($v, $wrap)));
    }
    else
    {
      $txt[] = $v;
    }
  }
}
//foreach($txt as &$v) $v = wordwrap($v,100,"\n",1);

$f = (int)$_GET["font"];
if(!file_exists(POUET_CONTENT_LOCAL."gfx/fnt".$f.".png")) $f=1;

$txt[count($txt)-1].=chr(13).chr(10);
$fnt=imagecreatefrompng(POUET_CONTENT_LOCAL."gfx/fnt".(int)$f.".png");
$c_w=imagesx($fnt)/16;
$c_h=imagesy($fnt)/16;
$fc1=imagecolorexact($fnt,0,0,0);
$fc2=imagecolorexact($fnt,160,160,160);
imagecolortransparent($fnt,$fc1);

$nbr=count($txt);
$max=0;

for($i=0;$i<$nbr;$i++)
{
  $len=strlen(rtrim($txt[$i]));
  if($len>$max) $max=$len;
}
$i_w=$max*$c_w+$s;
$i_h=$nbr*$c_h+$s+10;

$im=imagecreate($i_w,$i_h);
$c1=imagecolorallocate($im,$r2,$g2,$b2);
$c2=imagecolorallocate($im,$r3,$g3,$b3);
imagefill($im,0,0,$c1);
//imagecolortransparent($im,$c1);
imagefilledrectangle($im,0,$i_h-9,$i_w,$i_h,$c2);

for($i=0;$i<$nbr;$i++)
{
  $len=strlen($txt[$i]);
  for($j=0;$j<$len+1;$j++)
  { 
    $chr=ord($txt[$i]{$j});
    if($chr==9) $chr=32;
    $x=$chr%16*$c_w;
    $y=($chr-$chr%16)/16*$c_h;
    imagecolorset($fnt,$fc2,$r3,$g3,$b3);
    imagecopymerge($im,$fnt,$j*$c_w+$s,$i*$c_h+$s,$x,$y,$c_w,$c_h,100);
    imagecolorset($fnt,$fc2,$r1,$g1,$b1);
    imagecopymerge($im,$fnt,$j*$c_w,$i*$c_h,$x,$y,$c_w,$c_h,100);
  }
}

function write($txt,$a,$b,$br,$bg,$bb)
{
  global $im;
  $fnt=imagecreatefrompng(POUET_CONTENT_LOCAL."gfx/font.png");
  $c_w=imagesx($fnt)/16;
  $c_h=imagesy($fnt)/16;
  $f1=imagecolorexact($fnt,0,0,0);
  $f2=imagecolorexact($fnt,80,80,80);
  $f3=imagecolorexact($fnt,160,160,160);
  imagecolortransparent($fnt,$f1);
  imagecolorset($fnt,$f2,($br+255)/2,($bg+255)/2,($bb+255)/2);
  imagecolorset($fnt,$f3,255,255,255);
  for($i=0;$i<strlen($txt);$i++)
  { 
    $chr=ord($txt{$i});
    $x=$chr%16*$c_w;
    $y=($chr-$chr%16)/16*$c_h;
    imagecopy($im,$fnt,$a+$c,$b,$x,$y,$c_w,$c_h);
    $c+=$c_w; if($c>imagesx($im)-$a) { $b+=$c_h; $c=0; }
  }
}

$te=microtime()-$ts;// list($s,$ms)=explode(".",$te); $t=$s.".".substr($ms,0,3);
write("generated by pouet lobstergod REZ! (".($te)."s)",1,$i_h-7,$r3,$g3,$b3);

header("Content-type: image/png");
imagepng($im);
?>