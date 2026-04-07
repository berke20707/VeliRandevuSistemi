<?php
$css = "
#particles-js {
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: -1;
}
";
file_put_contents('assets/css/style.css', $css, FILE_APPEND);
echo "Done";
