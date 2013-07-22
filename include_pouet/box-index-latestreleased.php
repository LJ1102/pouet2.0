<?
class PouetBoxLatestReleased extends PouetBoxCachable {
  var $data;
  var $prods;
  function PouetBoxLatestReleased() {
    parent::__construct();
    $this->uniqueID = "pouetbox_latestreleased";
    $this->title = "latest released prods";
  }

  function LoadFromCachedData($data) {
    $this->data = unserialize($data);
  }

  function GetCacheableData() {
    return serialize($this->data);
  }

  function LoadFromDB() {
    $s = new BM_Query("prods");
    $s->AddOrder("prods.date DESC,prods.quand DESC");
    $s->attach("added",array("users as user"=>"id"));
    $s->SetLimit(POUET_CACHE_MAX);
    $this->data = $s->perform();
    PouetCollectPlatforms($this->data);
  }

  function RenderBody() {
    echo "<ul class='boxlist boxlisttable'>\n";
    $n = 0;
    foreach($this->data as $p) {
      echo "<li>\n";
      echo "<span class='rowprod'>\n";
      echo $p->RenderAsEntry();
      echo "</span>\n";
      if (get_setting("indexwhoaddedprods"))
      {
        echo "<span class='rowuser'>\n";
        echo $p->user->PrintLinkedAvatar();
        echo "</span>\n";
      }
      echo "</li>\n";
      if (++$n == get_setting("indexlatestreleased")) break;
    }
    echo "</ul>\n";
  }
  function RenderFooter() {
    echo "  <div class='foot'><a href='prodlist.php?order=added'>more</a>...</div>\n";
    echo "</div>\n";
  }
};

?>