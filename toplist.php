<?
require_once("bootstrap.inc.php");

class PouetBoxTopList extends PouetBox {
  function PouetBoxTopList() {
    parent::__construct();
    $this->uniqueID = "pouetbox_toplist";

    $this->formifier = new Formifier();

    $row = SQLLib::selectRow("DESC prods type");
    $m = enum2array($row->Type);

    $this->types = array();
    $this->types[""] = "- none - ";
    foreach($m as $v) $this->types[$v] = $v;
  }

  function LoadFromDB()
  {
    global $PLATFORMS;
    $plat = array();
    $plat[""] = "- none -";
	  foreach($PLATFORMS as $k=>$v) $plat[$k] = $v["name"];
	  uasort($plat,"strcasecmp");

    $this->fields = array(
      "type"=>array(
        "name"=>"type",
        "type"=>"select",
        //"multiple"=>true,
        "assoc"=>true,
        "fields"=>$this->types,
        "info"=>" ",
        //"required"=>true,
      ),
      "platform"=>array(
        "name"=>"platform",
        "type"=>"select",
        //"multiple"=>true,
        "assoc"=>true,
        "fields"=>$plat,
        "info"=>" ",
        //"required"=>true,
      ),
      "days"=>array(
        "name"=>"days to go back",
        "type"=>"number",
        "value"=>0,
        "info"=>"0 means alltime",
      ),
      "limit"=>array(
        "name"=>"number of prods",
        "type"=>"number",
        "value"=>10,
        "max"=>50,
      ),
    );


    if ($_GET)
    {
      foreach($_GET as $k=>$v)
        if ($this->fields[$k])
          $this->fields[$k]["value"] = $v;
    }

    $s = new BM_Query("prods");
    if ($_GET["days"])
    {
      $s->AddOrder("(prods.views/((NOW()-prods.addedDate)/100000)+prods.views)*prods.voteavg*prods.voteup DESC");
      $s->AddWhere(sprintf_esc("prods.addedDate > DATE_SUB(NOW(),INTERVAL %d DAY)",$_GET["days"]));
    }
    else if ($_GET["dateFrom"] || $_GET["dateTo"])
    {
      $s->AddOrder("(prods.views/((NOW()-prods.addedDate)/100000)+prods.views)*prods.voteavg*prods.voteup DESC");
      if ($_GET["dateFrom"])
        $s->AddWhere(sprintf_esc("prods.addedDate >= '%s'",$_GET["dateFrom"]));
      if ($_GET["dateTo"])
        $s->AddWhere(sprintf_esc("prods.addedDate <= '%s'",$_GET["dateTo"]));
    }
    else
    {
      $s->AddOrder("prods.rank");
      $s->AddWhere("prods.rank > 0");
    }
    if ($_GET["type"])
    {
      $s->AddWhere(sprintf_esc("FIND_IN_SET('%s',prods.type)>0",$_GET["type"]));
    }
    if ($_GET["platform"])
    {
      $s->AddJoin("","prods_platforms",sprintf_esc("prods_platforms.prod = prods.id AND prods_platforms.platform=%d",$_GET["platform"]));
    }
    $limit = (int)($_GET["limit"] ? $_GET["limit"] : 10);
    $limit = min($limit,50);
    $limit = max($limit,10);
    $s->SetLimit($limit);
    $this->prods = $s->perform();
    PouetCollectPlatforms($this->prods);
    PouetCollectAwards($this->prods);
  }
  function RenderTitle()
  {
    echo "<div class='selector'>";
    echo "<form action='toplist.php' method='get'>\n";
    $this->formifier->RenderForm( $this->fields );
    echo "  <input type='submit' value='Submit'/>\n";
    echo "</form>\n";
    echo "</div>";
  }
  function RenderBody()
  {
    echo "<ul class='boxlist boxlisttable'>\n";
    $n = 1;
    foreach($this->prods as $p)
    {
      printf("  <li>\n");
      printf("    <span>%d.</span>\n",$n++);
      printf("    <span>");
      echo $p->RenderTypeIcons();
      echo $p->RenderPlatformIcons();
      echo $p->RenderSingleRowShort();
      echo " ".$p->RenderAwards();
      printf("    </span>");
      printf("  </li>\n");
    }
    echo "</ul>\n";
  }
};

$TITLE = "top of the trumpets";

require_once("include_pouet/header.php");
require("include_pouet/menu.inc.php");

echo "<div id='content'>\n";

$box = new PouetBoxTopList();
$box->Load();
$box->Render();

echo "</div>\n";

require("include_pouet/menu.inc.php");
require_once("include_pouet/footer.php");

?>
