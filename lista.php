<html>
<head>
<title>.:: Download Site ::.</title>
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
 <?php
    if(isset($_GET['path']))
		$path = $_GET['path'];

    if(!isset($path))
    {
        $path = ".";
    }

    if ($handle = opendir($path))
    {
        $curDir = substr($path, (strrpos(dirname($path."/."),"/")+1));
        print "************************<br>";
        print " Directory: ".dirname($path."/.")." <br>************************<br>";

		print "<table>";
		print "<tr><td></td><td style=\"padding-left: 10px\">File Name</td><td style=\"text-align: center;\">Size</td></tr>";
		$up = substr($path, 0, (strrpos(dirname($path."/."),"/")));
        print "[^]  <a href='index.php?path=$up'>[..]</a><br>";

        while (false !== ($file = readdir($handle)))
        {
            if ($file != "." && $file != ".." && substr($file,-3) != "php")
            {
                $fName = $file;
				$extaz = pathinfo($fName);

				if(strlen($extaz['basename']) > 3)
					$ext = $extaz['extension'];

				switch($ext){
					case "png":
						$img = "png.png";
						break;
					case "mobi":
						$img = "mobi.png";
						break;
					case "zip":
						$img = "zip.gif";
						break;
					case "exe":
						$img = "exe.png";
						break;
					default:
						$img = "file.png";

				}

                $file = $path.'/'.$file;
                if(is_file($file) && $fName != "index.php")
                {
                    print "<tr class=\"row\"><td align=\"center\"><img src=\"abc/$img\" /></td><td> <a href='".$file."'><ul class=\"rowlink\"><li onclick=\"enact(this);\">".$fName."</li></ul></a>    </td><td align=\"right\">". filesize($file)."B <br> </td></tr>";
                }
                if(is_dir($file))

                {
                    print "<tr><td align=\"center\"><img src=\"abc/dir.png\" /></td><td style=\"padding-left: 10px\"><a href='ex2.php?path=$file'>$fName</a><br></td><td>";
                }
            }
        }


        closedir($handle);
		print "</table>";
    }
 ?>
 </body>
 </html>
