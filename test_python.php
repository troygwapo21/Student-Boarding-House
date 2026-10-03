<?php
$output = [];
$return_var = -1;
exec('python3 --version 2>&1', $output, $return_var);
echo "python3: " . implode("\n", $output) . " (code: $return_var)\n";

$output2 = [];
$return_var2 = -1;
exec('python --version 2>&1', $output2, $return_var2);
echo "python: " . implode("\n", $output2) . " (code: $return_var2)\n";
