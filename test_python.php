<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$disabled = explode(',', ini_get('disable_functions'));
$disabled = array_map('trim', $disabled);
echo "exec disabled: " . (in_array('exec', $disabled) ? 'YES' : 'NO') . "\n";
echo "shell_exec disabled: " . (in_array('shell_exec', $disabled) ? 'YES' : 'NO') . "\n";
echo "Disabled functions list:\n" . implode(", ", $disabled) . "\n";
