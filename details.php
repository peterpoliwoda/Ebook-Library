<html>
<head>
<title>.:: My eBook Library ::.</title>
<link rel="stylesheet" type="text/css" href="abc/styles.css">
<link
href="abc/favicon.png"
rel="SHORTCUT ICON">
<link rel="stylesheet" type="text/css" href="abc/styles.css">
<script type="text/javascript">
function enact(what){
     var p = what.parentNode;
     var els = p.getElementsByTagName('li');
     for(i=0;i<els.length;i++){
          els[i].className = '';
     }
     what.className = 'active';
}
</script>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320-->
</head>
<body>
 <div id="library-menu">
 	<div id="view_container">
	  <ul class="sorting">
	    <li class="sorting"><a href="index.php" class="view"><img src="abc/back.png" alt="Back" /></a></li>
	  </ul>
    </div>
 </div>
<div id="books_container">
<?php

	function url_get_contents($Url) {
			if (!function_exists('curl_init')){ 
				die('CURL is not installed!');
			}
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $Url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // 2 is the default so this is not required

			$output = curl_exec($ch);
			
			if($output === false)
			{
				echo 'Curl error #'.curl_errno($ch).': ' . curl_error($ch);
			}
			curl_close($ch);
			return $output;
		}

$nr = $_GET['isbn'];

    if(isset($_GET['path']))
		$path = $_GET['path'];
	
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
				
                if(is_file($file) && $fExt == "mobi")
                {
				$isbn = explode("_",$fName);
				$LANGs[] = $isbn[0];
				$ISBNs[] = $isbn[1];
				$FILES[] = $fName;
				 	}
            	}
        	}
		}
        closedir($handle);

		
		foreach($FILES as $filename) {
			$isbn = explode("_",$filename);
			
			if($isbn[1] == $nr){
				$thisFile = $filename;
				$thisLang = $isbn[0];
				break;
				}
			}
		
			$url = 'https://www.googleapis.com/books/v1/volumes?country=US&q=isbn:'.$nr ;
			//$content = file_get_contents($url);
			$content = url_get_contents($url);
			
			$json = json_decode($content, true);	
			$jsonArray = array();		
			
			foreach($json['items'] as $item) {
				
				if(isset($item['volumeInfo']['averageRating']))
					$rating =  "Rating: ".$item['volumeInfo']['averageRating']." / 5";
				else
					$rating = "No rating yet";

				if(isset($item['volumeInfo']['imageLinks']['thumbnail']))
					$thumb =  $item['volumeInfo']['imageLinks']['thumbnail'];
				else
					$thumb = "http://books.google.ie/googlebooks/images/no_cover_thumb.gif";
				
				if(isset($item['volumeInfo']['description']))
					$description =  $item['volumeInfo']['description'];
				else
					$description = "There is no description for this book.";
				
				if(isset($item['volumeInfo']['publisher']))
					$publisher =  $item['volumeInfo']['publisher']. ",";
				else
					$publisher = "";
				
				print_r("<div class=\"book_detailed\">
						<div class=\"thumbnail_detailed\">
						<div class=\"thumb_border\">
						<a href=\"$thisFile\">
							<img 
							src=\"".$thumb."\" border=\"0\"
							width=\"128\" height=\"156\" alt=\"thumb\"/>
							</a>
							</div>
							<div class=\"more_info\">
								<p><a href=\"http://www.goodreads.com/book/isbn/".$nr."\" title=\"Goodreads\">
								<img src=\"abc/read_reviews_goodreads.png\" border=\"0\"></a></p>			
								<p><a href=\"".$item['volumeInfo']['infoLink']."\" title=\"Google Books\">
								<img src=\"abc/google_books.png\" border=\"0\"></a></p>			
							</div>
							</div>
						
						<div class=\"desc_detailed\">
						  <strong>".$item['volumeInfo']['title']."</strong><br>
						  <span class=\"author\">".$item['volumeInfo']['authors'][0]."</span><br />
				<span class=\"publisher\">".$publisher." ".$item['volumeInfo']['publishedDate']." - ".$item['volumeInfo']['pageCount']." pages
						</span><br />
						<img src=\"abc/".$thisLang.".gif\" /><br />
						  <span class=\"rating\">". $rating." </span> <br />
						</div>
						<div class=\"description\">
							$description
						</div>
					</div>
						");
				}
echo("
<div>
      <style>
  #goodreads-widget {
    font-family: georgia, serif;
    padding: 18px 0;
    width:575px;
  }
  #goodreads-widget h1 {
    font-weight:normal;
    font-size: 16px;
    border-bottom: 1px solid #BBB596;
    margin-bottom: 0;
  }
  #goodreads-widget a {
    text-decoration: none;
    color:#660;
  }
  iframe{
    background-color: #ffffff;
  }
  #goodreads-widget a:hover { text-decoration: underline; }
  #goodreads-widget a:active {
    color:#660;
  }
  #gr_footer {
    width: 100%;
    border-top: 1px solid #BBB596;
    text-align: right;
  }
  #goodreads-widget .gr_branding{
    color: #382110;
    font-size: 11px;
    text-decoration: none;
    font-family: verdana, arial, helvetica, sans-serif;
  }
</style>
<div id=\"goodreads-widget\">
  <div id=\"gr_header\"><h1><a href=\"#\">Goodreads reviews</a></h1></div>
  <iframe id=\"the_iframe\" src=\"http://www.goodreads.com/api/reviews_widget_iframe?did=5049&format=html&header_text=Goodreads+reviews+for+&isbn=".$nr."&links=660&min_rating=&num_reviews=&review_back=ffffff&stars=000000&stylesheet=&text=444\" width=\"575\" height=\"400\" frameborder=\"0\"></iframe>
</div>

    
</div>
");
?>
</div>
 </body>
 </html>