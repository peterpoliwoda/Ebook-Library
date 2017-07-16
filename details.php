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
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script type="text/javascript" src="images/color-thief.min.js"></script>
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
	<script type="text/javascript">
		document.querySelector('img').addEventListener('load', function() {
	
			var colorThief = new ColorThief();
			var bookThumbnail = document.getElementById('thumbnail');
			bookThumbnail.setAttribute('crossOrigin', '');
			console.log('image source', bookThumbnail);
			var bookColor = colorThief.getColor(bookThumbnail);
			console.log('BookColor', bookColor);

			$('#books_container').css('background', 'rgb(' + bookColor[0] + 
			','+ bookColor[0] + ',' + bookColor[0] + ')');

		});
	</script>
	</body>
</html>