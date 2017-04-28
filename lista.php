<?php
	require_once('utils.php');
?>
<html>
<head>
<title>.:: Download Site ::.</title>
<link rel="stylesheet" type="text/css" href="images/styles.css">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/> <!--320-->
</head>
<body>
 <?php    
    $utils = new Utils();
    $allFiles = $utils->getFolderContents('.');

        print_r ('<table style="margin: 0 auto; width: 50%; margin-top: 30px; padding: 20px; border-top: 1px solid #EEE;">'
		    .'<td style="padding-left: 10px" colspan="2"><strong>File Name</strong></td>'
            .'<td style="text-align: center;"><strong>Size</strong></td></tr>');

        $fileIcons = array('png', 'mobi', 'zip', 'exe');
        foreach ($allFiles as $file) {
            if ($file != '.' && $file != '..' && substr($file, - 3) != 'php'
                && substr($file, 0, 1) != '.') {
                if (!is_dir($file)) {
                    $extaz = pathinfo($file);
                    if (strlen($extaz['basename']) > 3)
                        $ext = $extaz['extension'];
                    if (in_array($ext, $fileIcons))
                        $img = $ext.'.png';
                    else
                        $img = 'file.png';

                    if (is_file($file) && $file != 'index.php') {
                        print_r('<tr><td style="height: 10px; padding: 7px;" align="center">'
                            .'<img src="images/icons/'.$img.'" /></td>'
                            .'<td><a href="'.$file.'">'.$file.'</a></td>'
                            .'<td align="right">'.intval(filesize($file) / 1000).'kB'.'</td></tr>');
                    }
                }
            }
        }
		print_r ('</table>');
 ?>
 </body>
 </html>
