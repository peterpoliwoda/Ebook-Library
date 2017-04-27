<?php
	require_once('utils.php');
?>
<html>
	<head>
		<title>.:: My eBook Library ::.</title>
		<link href='http://fonts.googleapis.com/css?family=Spicy+Rice' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="images/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="images/styles.css">
		<link href="images/favicon.png" rel="SHORTCUT ICON">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320-->
	</head>
	<body>
		<div id="library-menu">
			<ul>
				<li class="icon-link"><a href="index.php"><i class="fa fa-chevron-left" aria-hidden="true"></i></a></li>
			</ul>
		</div>
	<div id="books_container">
	<?php
  		$utils = new Utils();
			
			if (!isset($_GET['isbn'])) {
					print_r('No book found.');
			} else {
					$book = $utils->getBook($_GET['isbn']);
					$utils->showBookDetails($book);
					$utils->showGoodreadsComments($_GET['isbn']);
			}
	?>
	</div>
	</body>
</html>