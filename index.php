<?php
require_once('utils.php');

$sortuj = 'recent';
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
<link rel="stylesheet" type="text/css" href="abc/styles.css">
<link
href="abc/favicon.png"
rel="SHORTCUT ICON">

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320 / 600x800-->
<link rel="stylesheet" type="text/css" href="abc/styles.css">

</head>
<body>
 <div id="library-menu">
 	<div id="view_container">
	  <ul class="sorting">
	    <li class="sorting"><a href="index.php?display=grid" class="view"><img src="abc/grid.png" alt="grid" /></a></li>
	    <li class="sorting"><a href="index.php?display=list" class="view"><img src="abc/list.png" alt="list" /></a></li>
	  </ul>
    </div>
   <div id="sorting_container">
        <ul class="sorting">
          <li class="sorting"><a href="?sort=recent&display=<?php echo($disp); ?>" class="sorting">by Recent</a></li>
          <li class="sorting"><a href="?sort=rating&display=<?php echo($disp); ?>" class="sorting">by Rating</a></li>
          <li class="sorting"><a href="?sort=title&display=<?php echo($disp); ?>" class="sorting">by Title</a></li>
          <li class="sorting"><a href="?sort=author&display=<?php echo($disp); ?>" class="sorting">by Author</a></li>
        </ul>
 	</div>
 </div>
<div id="books_container">

  <?php

		$utils = new Utils();
		$allBooks = $utils->getBooks();
		$booksFromGoogle = $utils->getBooksFromGoogle($allBooks);

		print_r(json_encode($booksFromGoogle));

		print("<p style=\"font-size: 10px;\"> <strong>Books: </strong>". $utils->totalBooks."&nbsp; &nbsp; &nbsp;");
		print("<strong>Google Queries: </strong>".$utils->numberOfQueries."</p>");

		function sort_title($a, $b) {
			if ($a['Title'] < $b['Title']) {
				return -1;
			} else if ($a['Title'] > $b['Title']) {
				return 1;
			} else {
				return 0;
			}
		}

		function sort_author($a, $b) {
			if ($a['Author'] < $b['Author']) {
				return -1;
			} else if ($a['Author'] > $b['Author']) {
				return 1;
			} else {
				return 0;
				}
			}

		function sort_recent($a, $b) {
			if (strtotime($a['LastModified']) > strtotime($b['LastModified'])) {
				return -1;
			} else if (strtotime($a['LastModified']) < strtotime($b['LastModified'])) {
				return 1;
			} else {
				return 0;
				}
		}
			
		function sort_rating($a, $b) {
			if(isset($a['Rating']))
				$rate_a = $a['Rating'];
			else
				$rate_a = 0;
			
			if(isset($a['Rating']))
				$rate_b = $b['Rating'];
			else
				$rate_b = 0;
			
				if ($rate_a > $rate_b) {
					return -1;
				} else if ($rate_a < $rate_b) {
					return 1;
				} else {
					return 0;
					}	
		}
			
		function sort_pages($a, $b) {
			if ($a['Pages'] < $b['Pages']) {
				return -1;
			} else if ($a['Pages'] > $b['Pages']) {
				return 1;
			} else {
				return 0;
				}
		}
			
		function sort_language($a, $b) {
			if ($a['LANG'] < $b['LANG']) {
				return -1;
			} else if ($a['LANG'] > $b['LANG']) {
				return 1;
			} else {
				return 0;
			}
		}
  
  	$ISBNs = array();
	$LANGs = array();
	$MODIFIED = array();
  	$jsonArray = array();

	$BOOKS = array();
	$book_entry = array();
	/*	
	//Book Entry Headings:
	- ISBN
	- LANG
	- Thumbnail
	- Author
	- Title
	- Rating
	- Year
	- Pages
	- Description
	- Last Modified
	*/
	
    if(isset($_GET['path'])) {
		$path = $_GET['path'];
		print_r('$path is set to:');
		print_r($path);
	}
	
    if(!isset($path))
    {
        $path = ".";
    }
  
    if ($handle = opendir($path))
    {		
        while (false !== ($file = readdir($handle)))
        {
            if ($file != "." && $file != "..")
            {
                $fName = $file;								
                $file = $path.'/'.$file;
				$fExt = substr($fName,-4);
				
				$MODIFIED[] = date("d-m-Y H:i:s.", filectime($file));
				
                if(is_file($file) && $fExt == "mobi")
                {
				$isbn = explode("_",$fName);
				$LANGs[] = $isbn[0];
				$ISBNs[] = $isbn[1];
				
                }
        	}
		}
        closedir($handle);
	}
		
		//Total of ebooks to handle
		$nrs = count($ISBNs);
		$intNrs = intval($nrs/10);		
		
		if (($nrs/10) > $intNrs){
			$res = $nrs%10;
			$GoogleQueries = $intNrs + 1;
			
		} else {
			$res = $nrs%10;
			$GoogleQueries = $intNrs;
		}

		$booksHandled = 0;
		$GQ = 1;
		$BooksInThisQuery = 0;
		$urls = array();
		
		
		while($GQ <= $GoogleQueries){
			$url = 'https://www.googleapis.com/books/v1/volumes?country=US&q=isbn:';

			if(($nrs/10) > $intNrs){
				$BooksInThisQuery = 0;
			}
			
			for($number = 0; $number < 10; $number++){
				if($number == 0)
					$url = $url. $ISBNs[$booksHandled];
				elseif($booksHandled < $nrs && $number > 0)
					$url = $url . "+OR+isbn:".$ISBNs[$booksHandled];
				
				$booksHandled++;
				
				if($booksHandled > $nrs)
					break;
				}	
				
				$urls[] = $url;
				//print($url."<br/>");
				//print("Books handled: ".$booksHandled."<br/>");
				$GQ++;
			}
		
		$bookInArray = 0;
		foreach($urls as $GLink) {
			$content = $utils->makeUrlRequest($GLink);
			$json = json_decode($content, true);
			
			if(isset($json['items'])){
				foreach($json['items'] as $item) {
						/*	
						//Book Entry Headings:
						- ISBN
						- LANG
						- Thumbnail
						- Author
						- Title
						- Rating
						- Year
						- Pages
						- Description
						- Last Modified
						*/
						
						if(isset($item['volumeInfo']['averageRating']))
							$rating =  $item['volumeInfo']['averageRating'];
						else
							$rating = 0;
						
						if(isset($item['volumeInfo']['imageLinks']['thumbnail']))
							$thumb =  $item['volumeInfo']['imageLinks']['thumbnail'];
						else
							$thumb = "http://books.google.ie/googlebooks/images/no_cover_thumb.gif";
						
						if(isset($item['volumeInfo']['publishedDate']))
							$year_published =  "(".substr($item['volumeInfo']['publishedDate'],0,4). ")";
							
						else
							$year_published = "";
						
						if(isset($item['volumeInfo']['pageCount']))
							$pages =  "- ". $item['volumeInfo']['pageCount'] . " pages";
						else
							$pages = "";

						if(isset($item['volumeInfo']['description']))
							$desc =  $item['volumeInfo']['description'];
						else
							$desc = "There is no description for this book.";

							$itemISBN = $item['volumeInfo']['industryIdentifiers'][0]['identifier'];
						
						
						$correct_isbn = "0";
						$correct_lang = "";
						$correct_modified = "";
						
						
						for($buk = 0;$buk < count($ISBNs);$buk++){
							if($itemISBN == $ISBNs[$buk]){
							  $correct_isbn = $ISBNs[$buk];
							  $correct_lang = $LANGs[$buk];
							  $correct_modified = $MODIFIED[$buk];
							  break;
							}
						}
						
						$book_entry = array(
									"ISBN"		=> $correct_isbn,
									"LANG"		=> $correct_lang,
									"Thumbnail" => $thumb,
									"Author"	=> $item['volumeInfo']['authors'][0],
									"Title"		=> $item['volumeInfo']['title'],
									"Rating"	=> $rating,
									"Year"		=> $year_published,
									"Pages"		=> $pages,
									"Description"=> $desc,
									"LastModified"=> $correct_modified
								);
						$BOOKS[] = $book_entry;
					}
				}
			}
		
		switch($sortuj){
			case 'title':
			usort($BOOKS, 'sort_title');
			break;
			
			case 'author':
			usort($BOOKS, 'sort_author');
			break;
			
			case 'rating':
			usort($BOOKS, 'sort_rating');
			break;
			
			case 'recent':
			usort($BOOKS, 'sort_recent');
			break;
			
			case 'pages':
			usort($BOOKS, 'sort_pages');
			break;
			
			case 'language':
			usort($BOOKS, 'sort_language');
			break;
			
			default:
			usort($BOOKS, 'sort_recent');
		}
	
		
		
		$b = 0;
		foreach($BOOKS as $book) {
			
			if(isset($book['Description']))
				$description =  $book['Description'];
			else
				$description = "There is no description for this book.";
			
			if (strlen($description) > 150) {
					$description = wordwrap($description, 200); 
					$description = substr($description, 0, strpos($description, "\n"));
					$description = $description ."...";
				}
			
			if($book['Rating'] > 0)
				if($disp == "list")
					$rating =  "Rating:<br/> ".$book['Rating']." / 5";
				else
					$rating = " ".$book['Rating']." &#9733; 5";
			else
				$rating = "No rating yet";

				$year_published = $book['Year'];	
				$pages =  $book['Pages'];
			
			if(isset($book['Thumbnail']))
				$thumb =  $book['Thumbnail'];
			else
				$thumb = "http://books.google.ie/googlebooks/images/no_cover_thumb.gif";
				
			$nr =  $book['ISBN'];
			
			$lang = "<img src=\"abc/".$book['LANG'].".gif\" />";
				
			if($disp == "grid"){
				if(((strtotime(date("Y-m-d H:i:s"))) - (strtotime($book['LastModified']))) < 259200)
						$recent_style = "item_recent";
					else
						$recent_style = "item";
			
			print_r("<div class=\"$recent_style\">
						<div class=\"book\">
							<div class=\"thumbnail\">
							<a href=\"details.php?isbn=".$nr."\">
								<img 
								src=\"".$thumb."\" border=\"0\"
								width=\"128\" height=\"156\" alt=\"thumb\"/>
								</a></div>
							<div class=\"desc\">
							  <span class=\"rating\">". $rating." </span><br />
							  <a href=\"details.php?isbn=".$nr."\"><span class=\"title\">".$book['Title']."</span></a><br />
							  <span class=\"author\">".$book['Author']."</span><br />
							  $lang
							</div>
						</div>
					</div>
					");
			}
			else {
				if(((strtotime(date("Y-m-d H:i:s"))) - (strtotime($book['LastModified']))) < 259200)
					$recent_style = "list_item_recent";
				else
					$recent_style = "list_item";
				
				print_r("
					<div class=\"$recent_style\">
					  <div class=\"book_list\">
						<div class=\"thumbnail_list\">
						  <a href=\"details.php?isbn=".$nr."\">
							<img src=\"".$thumb."\" border=\"0\"
							width=72 height=110 alt=\"thumb\"/>
						  </a>
						</div>
						<div class=\"desc_list\">
							<div class=\"rating\">".$rating."</div>
							  <a href=\"details.php?isbn=".$nr."\"><span class=\"title_list\">".$book['Title']."</span></a><br />
							  <span class=\"author_list\">".$book['Author']."</span><br/>
							  <a href=\"index.php?sort=language&display=list\">$lang</a> $year_published <a href=\"index.php?sort=pages&display=list\" class=\"publisher\">$pages</a><br />
							  <span class=\"modified\">Last Modified: ".$book['LastModified']."</span><br/>
							<p>".$description."</p>
						</div>
					  </div>
					</div>
					");
				}
				$b++;
			}
 ?>
</div>
</div>
</body>
 </html>