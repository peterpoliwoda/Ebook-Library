<?php

class Utils {

    public $numberOfQueries;
    public $totalBooks;
    public $bookData;
    private $dataFilePath = 'ebooks.json';

    function __construct($sortuj = 'modified') {
        $this->getJSONEbookFileArray();
        $this->getBooksFromGoogle($this->getBooksFromDirectory());
        $this->sort($sortuj);
        $this->totalBooks = count($this->bookData);
    }

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
            echo 'Curl error #'.curl_errno($ch).': ' . curl_error($ch);

        curl_close($ch);
        return $output;
    }

    function getJSONEbookFileArray() {
        $fileContents = file_get_contents($this->dataFilePath);
        $this->bookData = json_decode($fileContents, true);
    }

    function filterBooksFromDataFile($booksFromDirectory) {
        $filteredBooks = array();
        foreach ($booksFromDirectory as $bookFromDir) {
            $addBook = true;
            foreach($this->bookData as $dataBook) {
                if ($bookFromDir['isbn'] == $dataBook['isbn']) {
                    $addBook = false;
                    break;
                }
            }

            if ($addBook) {
                $filteredBooks[] = $bookFromDir;
            }
        }

        return $filteredBooks;
    }

    public function getFolderContents($path) {
        if(!isset($path))
            $path = '.';

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

    public function getBook($isbn) {
        $bookData = $this->bookData;
        foreach ($bookData as $i => $singleBook) {
            if ($singleBook['isbn'] == $isbn) {
                return $singleBook;
            }
        }
    }

    public function getBooksFromDirectory() {
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
        $totalBooks = count($books);
        $this->numberOfQueries = intval($totalBooks / 10);

        if ($totalBooks / 10 > intval($totalBooks / 10)) {
            $this->numberOfQueries += 1;
        }

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
            if (!array_key_exists('author', $ebook)) {
                $books[$i]['author'] = '';
            }
            if (!array_key_exists('rating', $ebook)) {
                $books[$i]['rating'] = 'No rating';
            }
            if (!array_key_exists('description', $ebook)) {
                $books[$i]['description'] = 'There is no description for this book.';
                }
            if (!array_key_exists('thumbnail', $ebook)) {
                $books[$i]['thumbnail'] = 'images/no-cover.png';
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

    function getBooksFromGoogle($booksFromDir) {
        $booksFromDir = $this->filterBooksFromDataFile($booksFromDir);
        $queries = $this->getISBNQueries($booksFromDir);
        $booksFromGoogle = array();

        foreach ($queries as $query) {
            $response = $this->makeUrlRequest($query);
            $resp = json_decode($response, true);

            if(isset($resp['items'])) {
                foreach($resp['items'] as $item) {
                    if(isset($item['volumeInfo']['averageRating']))
                        $rating =  $item['volumeInfo']['averageRating'];
                    else
                        $rating = 'No rating';

                    if(isset($item['volumeInfo']['imageLinks']['thumbnail']))
                        $thumb =  $item['volumeInfo']['imageLinks']['thumbnail'];
                    else
                        $thumb = 'images/no-cover.png';

                    if(isset($item['volumeInfo']['publishedDate']))
                        $year_published =  '('.substr($item['volumeInfo']['publishedDate'],0,4). ')';
                    else
                        $year_published = '';

                    if(isset($item['volumeInfo']['pageCount']))
                        $pages = $item['volumeInfo']['pageCount'];
                    else
                        $pages = '';

                    if(isset($item['volumeInfo']['description']))
                        $desc = $item['volumeInfo']['description'];
                    else
                        $desc = "There is no description for this book.";

                    $ISBNs = array();
                    foreach ($item['volumeInfo']['industryIdentifiers'] as $id => $value) {
                        $ISBNs[] = $value['identifier'];
                    }

                    foreach ($booksFromDir as $i => $book) {
                        if (in_array($book['isbn'], $ISBNs)) {
                            $booksFromDir[$i]['isbn'] = $book['isbn'];
                            $booksFromDir[$i]['thumbnail'] = $thumb;
                            $booksFromDir[$i]['author'] = $item['volumeInfo']['authors'][0];
                            $booksFromDir[$i]['title'] = $item['volumeInfo']['title'];
                            $booksFromDir[$i]['rating'] = $rating;
                            $booksFromDir[$i]['year'] = $year_published;
                            $booksFromDir[$i]['pages'] = $pages;
                            $booksFromDir[$i]['description'] = $desc;
                        }
                    }
                }
            }
        }

        $booksFromDir = $this->fillInBlanks($booksFromDir);
        $this->addBooksFromGoogleToDataFile($booksFromDir);
    }

    function addBooksFromGoogleToDataFile($booksFromGoogle) {
        if (count($booksFromGoogle)) {
            foreach ($booksFromGoogle as $i => $bookFromGB) {
                $this->bookData[] = $bookFromGB;
            }
            file_put_contents($this->dataFilePath, json_encode($this->bookData));
        }
    }

    public function sort($field) {
        usort($this->bookData, function($arr1, $arr2) use ($field) {
            if ($field === 'rating' || $field === 'modified')
                return ($arr1[$field] < $arr2[$field]) ? 1 : -1;
            else
                return ($arr1[$field] > $arr2[$field]) ? 1 : -1;
        });
    }

    public function getBookListHTML($viewType) {
        $html = '';
        foreach ($this->bookData as $book) {
            if ($viewType === 'list')
                $html .= $this->getListViewItem($book);
            else
                $html .= $this->getGridViewItem($book);
        }
        return $html;
    }

    function getRating($rating) {
        if ($rating !== 'No rating') {
            $stars = '';
            for ($i = 0; $i < round($rating); $i++) {
                $stars .= '★';
            }
            for ($i = 0; $i < (5 - round($rating)); $i++) {
                $stars .= '☆';
            }
            return $stars;
        } else {
            return $rating;
        }
    }

    function getGridViewItem($book) {
        $recentStyle = (strtotime(date('c')) - (strtotime($book['modified'])) < 259200) ? 'item_recent' : 'item';
        $rating = $this->getRating($book['rating']);
        $smallerTitle = (substr($book['title'], - 5) == '.mobi') ? 'title_smaller' : '';

        $gridHTML = '<div class="grid '.$recentStyle.'" style="background-image: url('.$book['thumbnail'].')">
            <div class="desc">
            <a href="details.php?isbn='.$book['isbn'].'">
            <span class="title '.$smallerTitle.'">'.$book['title'].'</span>
            </a><br />';

        if ($book['author'])
            $gridHTML .= '<span class="author">'.$book['author'].'</span><br />';

        $gridHTML .= '<img src="images/lang/'.$book['lang'].'.gif" /><br>
            <span class="rating">'.$rating.'</span>
            </div>
            </div>';
        return $gridHTML;
    }

    function getListViewItem($book) {
        $recentStyle = (strtotime(date('c')) - (strtotime($book['modified'])) < 259200) ? 'item_recent' : 'item';
        $rating = ($book['rating'] == 'No rating') ? 'No rating' : 'Rating <br />'.($book['rating']);
        return '<div class="list '.$recentStyle.'">
            <div class="book_list">
            <div class="thumbnail_list">
            <a href="details.php?isbn='.$book['isbn'].'">
            <img src="'.$book['thumbnail'].'" border="0" width="72" height="110" alt="thumbnail" />
            </a>
            </div>
            <div class="desc_list">
            <div class="rating">'.$rating.'</div>
            <a href="details.php?isbn='.$book['isbn'].'">
            <span class="title_list">'.$book['title'].'</span>
            </a><br />
            <span class="author_list">'.$book['author'].'</span><br/>
            <a href="index.php?sort=language&display=list"><img src="images/lang/'.$book['lang'].'.gif" /></a>
            '.$book['year'].' -
            <a href="index.php?sort=pages&display=list" class="publisher">'.$book['pages'].' pages</a><br />
            <span class="modified">Last Modified: '.$book['modified'].'</span><br/>
            <p>'.$book['description'].'</p>
            </div>
            </div>
            <div style="clear:both"></div>
            </div>';
    }

    public function showBookDetails($book) {
        $rating = $this->getRating($book['rating']) . '<br/> ('.$book['rating']. ' / 5)';
        print_r('<div class="book_detailed">
            <div class="thumbnail_detailed">
            <div class="thumb_border">
            <a href="'.$book['filename'].'">
            <img src="'.$book['thumbnail'].'" id="thumbnail" border="0" width="128" height="156" alt="thumb"/> <br/>
            Download ('.intval(filesize($book['filename']) / 1000).'kB)
            </a>
            </div>
            <div class="more_info">
            <p><a href="http://www.goodreads.com/book/isbn/'.$book['isbn'].'" title="Goodreads">
            <img src="images/read_reviews_goodreads.png" border="0"></a></p>
            <p><a href="https://books.google.com/books?isbn='.$book['isbn'].'" title="Google Books">
            <img src="images/google_books.png" border="0"></a></p>
            </div>
            </div>
            <div class="desc_detailed">
            <h2>'.$book['title'].'</h2>
            <div class="author">'.$book['author'].'</div>
            <div>'
            .$book['year'].' - '.$book['pages'].' pages
            </div>
            <img src="images/lang/'.$book['lang'].'.gif" /><br />
            <div>'.$rating.'</div>
            <div class="description">
            '.$book['description'].'
            </div>
            </div>
            </div>');
    }

    public function showGoodreadsComments($isbn) {
        print_r('<div>
            <div id="goodreads-widget">
            <div id="gr_header"><h1><a href="#">Goodreads reviews</a></h1></div>
            <iframe id="the_iframe" src="http://www.goodreads.com/api/reviews_widget_iframe?did=5049&format=html&header_text=Goodreads+reviews+for+&isbn='
            .$isbn.'&links=660&min_rating=&num_reviews=&review_back=ffffff&stars=000000&stylesheet=&text=444" width="575" height="400" frameborder="0"></iframe>
            </div>
            </div>');
    }
}

?>
