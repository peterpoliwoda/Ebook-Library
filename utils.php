<?php

/*	
  Book Entry Headings:
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

class Utils {

    public $numberOfQueries;
    public $totalBooks;
    
    public function makeUrlRequest($url) {
        if (!function_exists('curl_init')){ 
            die('CURL is not installed!');
        }
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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

  function getFolderContents($path) {
        if(!isset($path)) {
            $path = '.';
        }
  
        if ($handle = opendir($path)) {
          while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                  $files[] = $file;
                }
            }
        }
        closedir($handle);
        return $files;
    }

    public function getBooks() {
        $bookFiles = $this->getFolderContents('.');
        $books = array();
        foreach ($bookFiles as $i => $filename) {
            if (is_file($filename) && substr($filename, -4) === 'mobi') {
              $detail = explode('_', $filename);

              $books[] = array('lang' => $detail[0],
                  'isbn' => $detail[1],
                  'filename' => $filename,
                  'modified' => date('c', filectime($filename)));
            }
        }
        
        return $books;
    }

    function getISBNQueries($books) {
      /* Get number of Google Book queries */ 
      $this->totalBooks = count($books);
      $this->numberOfQueries = intval($this->totalBooks / 10);
      if ($this->totalBooks / 10 > intval($this->totalBooks / 10))
          $this->numberOfQueries += 1;
      
      $queries = array();
      
      for ($i = 0; $i < $this->numberOfQueries; $i++) {
          $url = 'https://www.googleapis.com/books/v1/volumes?country=US&q=isbn:';
          $url .= $books[(10 * $i)]['isbn'];
          for ($b = ((10 * $i) + 1); $b < (10 * ($i+1)); $b++) {
              if (!array_key_exists($b, $books))
                  break;
              $url .= '+OR+isbn:' . $books[$b]['isbn'];
          }
          $queries[] = $url;
        }
        return $queries;
    }

    function fillInBlanks($books) {
      
      foreach($books as $i => $ebook ) {
          if (!array_key_exists('title', $ebook) || $ebook['title'] == '') {
              $books[$i]['title'] = $books[$i]['filename'];
          }
          if (!array_key_exists('author', $ebook) || $ebook['author'] == '') {
              $books[$i]['author'] = '';
          }
          if (!array_key_exists('rating', $ebook)) {
              $books[$i]['rating'] = 0;
          }
          if (!array_key_exists('description', $ebook)) {
              $books[$i]['description'] = 'There is no description for this book.';
          }
          if (!array_key_exists('thumbnail', $ebook)) {
              $books[$i]['thumbnail'] = 'http://books.google.ie/googlebooks/images/no_cover_thumb.gif';
          }
          if (!array_key_exists('year', $ebook)) {
              $books[$i]['year'] = '';
          }
          if (!array_key_exists('pages', $ebook)) {
              $books[$i]['pages'] = '';
          }
      }

      return $books;
    }

    public function getBooksFromGoogle($myBooks) {
        $queries = $this->getISBNQueries($myBooks);
        $booksFromGoogle = array();
        foreach ($queries as $query) {
            $response = $this->makeUrlRequest($query);
            $resp = json_decode($response, true);
            
            if(isset($resp['items'])){
                foreach($resp['items'] as $item) {
                    if(isset($item['volumeInfo']['averageRating']))
                        $rating =  $item['volumeInfo']['averageRating'];
                    else
                        $rating = 0;

                    if(isset($item['volumeInfo']['imageLinks']['thumbnail']))
                        $thumb =  $item['volumeInfo']['imageLinks']['thumbnail'];
                    else
                        $thumb = 'http://books.google.ie/googlebooks/images/no_cover_thumb.gif';
                    
                    if(isset($item['volumeInfo']['publishedDate']))
                        $year_published =  '('.substr($item['volumeInfo']['publishedDate'],0,4). ')';
                    else
                        $year_published = '';

                    if(isset($item['volumeInfo']['pageCount']))
                      $pages = $item['volumeInfo']['pageCount'];
                    else
                      $pages = '';

                    if(isset($item['volumeInfo']['description']))
                      $desc =  $item['volumeInfo']['description'];
                    else
                      $desc = "There is no description for this book.";

                    $ISBNs = array();
                    foreach ($item['volumeInfo']['industryIdentifiers'] as $id => $value) {
                        $ISBNs[] = $item['volumeInfo']['industryIdentifiers'][$id]['identifier'];
                    }

                    foreach ($myBooks as $i =>$book) {
                        if (in_array($book['isbn'], $ISBNs)) {
                            $myBooks[$i]['thumbnail'] = $thumb;
                            $myBooks[$i]['author'] = $item['volumeInfo']['authors'][0];
                            $myBooks[$i]['title'] = $item['volumeInfo']['title'];
                            $myBooks[$i]['rating'] = $rating;
                            $myBooks[$i]['year'] = $year_published;
                            $myBooks[$i]['pages'] = $pages;
                            $myBooks[$i]['description'] = $desc;
                        }
                    }
                }
            }
        }
        return $this->fillInBlanks($myBooks);
    }
}

?>