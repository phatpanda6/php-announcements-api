<?php
require_once __DIR__ . "/validation.php";

$result = isNonEmptyString("Music club");

if ($result === true) {
    echo json_encode(["PASS" => "accepts a non-empty string"]) . PHP_EOL;
} else {
    echo json_encode(["FAIL" => "accepts a non-empty string"]);
    exit(1);
}

$nonEmptyStringTest = isNonEmptyString(" ");

if ($nonEmptyStringTest === false) {
    echo json_encode(["PASS" => "rejects whitespace-only strings"]) . PHP_EOL;
} else {
    echo json_encode(["FAIL" => "rejects whitespace-only strings"]);
    exit(1);
}

$numberTest = isNonEmptyString(42);

if ($numberTest === false) {
    echo json_encode(["PASS" => "rejects non-string values"]) . PHP_EOL;
} else {
    echo json_encode(["FAIL" => "rejects non-string values"]);
    exit(1);
}
