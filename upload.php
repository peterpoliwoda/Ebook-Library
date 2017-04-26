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
echo("
<style>\n  #goodreads-widget {\n    font-family: georgia, serif;\n    padding: 18px 0;\n    width:565px;\n  }\n  #goodreads-widget h1 {\n    font-weight:normal;\n    font-size: 16px;\n    border-bottom: 1px solid #BBB596;\n    margin-bottom: 0;\n  }\n  #goodreads-widget a {\n    text-decoration: none;\n    color:#660;\n  }\n  iframe{\n    background-color: #fff;\n  }\n  #goodreads-widget a:hover { text-decoration: underline; }\n  #goodreads-widget a:active {\n    color:#660;\n  }\n  #gr_footer {\n    width: 100%;\n    border-top: 1px solid #BBB596;\n    text-align: right;\n  }\n  #goodreads-widget .gr_branding{\n    color: #382110;\n    font-size: 11px;\n    text-decoration: none;\n    font-family: verdana, arial, helvetica, sans-serif;\n  }\n</style>\n<div id=\"goodreads-widget\">\n  <div id=\"gr_header\"><h1><a href=\"http://www.goodreads.com/book/show/50.Hatchet\">Hatchet Reviews</a></h1></div>\n  <iframe id=\"the_iframe\" src=\"http://www.goodreads.com/api/reviews_widget_iframe?did=DEVELOPER_ID&amp;format=html&amp;isbn=0689840926&amp;links=660&amp;review_back=fff&amp;stars=000&amp;text=000\" width=\"565\" height=\"400\" frameborder=\"0\"></iframe>\n  <div id=\"gr_footer\">\n    <a href=\"http://www.goodreads.com/book/show/50.Hatchet?utm_medium=api&amp;utm_source=reviews_widget\" class=\"gr_branding\" target=\"_blank\">Reviews from Goodreads.com</a>\n  </div>\n</div>\
");
?>
 </body>
 </html>