<?php
require_once('utils.php');

$sortuj = 'modified';
$disp = 'grid';

if(isset($_GET) && count($_GET) > 0) {

	if (isset($_GET['sort'])) {
		$sortuj = $_GET['sort'];
		setcookie('sort', $_GET['sort']);
	} elseif (isset($_COOKIE['sort'])) {
		$sortuj = $_COOKIE['sort'];
	}

	if (isset($_GET['display'])) {
		$disp = $_GET['display'];
		setcookie('display', $_GET['display']);
	} elseif (isset($_COOKIE['display'])) {
		$disp = $_COOKIE['display'];
	}
}

?>
<html>
    <head>
        <title>.:: My eBook Library ::.</title>
        <link href='http://fonts.googleapis.com/css?family=Spicy+Rice' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="images/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="images/styles.css">
        <link href="images/favicon.png" rel="SHORTCUT ICON">
        <meta name="viewport" content="width=device-width, initial-scale=1"/> <!--320 / 600x800-->
        <link rel="stylesheet" type="text/css" href="images/styles.css">
    </head>
	  <body>
      <div id="library-menu">
        <ul>
          <li class="icon-link"><a href="index.php?display=grid"><i class="fa fa-th" aria-hidden="true"></i></a></li>
          <li class="icon-link"><a href="index.php?display=list"><i class="fa fa-list" aria-hidden="true"></i></a></li>
          <li><a href="?sort=modified&display=<?php echo($disp); ?>" class="menu-link">BY RECENT</a></li>
          <li><a href="?sort=rating&display=<?php echo($disp); ?>" class="menu-link">BY RATING</a></li>
          <li><a href="?sort=title&display=<?php echo($disp); ?>" class="menu-link">BY TITLE</a></li>
          <li><a href="?sort=author&display=<?php echo($disp); ?>" class="menu-link">BY AUTHOR</a></li>
        </ul>
      </div>
      <div class="books_container">
        <?php
            $utils = new Utils($sortuj);

            print('<p class="books_header"> <strong>Books: </strong>'. $utils->totalBooks);
            print('<strong style="padding-left: 20px">Google Queries: </strong>'.$utils->numberOfQueries.'</p>');

            print_r($utils->getBookListHTML($disp));
        ?>
      </div>
	  </div>
    <div class="footer">
      <a href="lista.php">Show file list</a>
    </div>
    <script type="text/javascript" src="color-thief.min.js"></script>
	</body>
</html>
