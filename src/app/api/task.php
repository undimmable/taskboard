<?php
if ($_REQUEST["method"] === "root") {
    echo "root";
} else {
    echo $_REQUEST["method"];
}