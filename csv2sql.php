<?php
if (count($argv) != 3) {
    echo "$argv[0] csv_file db_table\n";
    exit;
}

$file = $argv[1];
$table = $argv[2];

// get structure from csv and insert db
ini_set('auto_detect_line_endings',TRUE);
$handle = fopen($file,'r');
// first row, structure
if ( ($data = fgetcsv($handle) ) === FALSE ) {
    echo "Cannot read from csv $file";die();
}
$fields = array();
$field_count = 0;
for($i=0;$i<count($data); $i++) {
    $f = strtolower(trim($data[$i]));
    if ($f) {
        // normalize the field name, strip to 20 chars if too long
        $f = substr(preg_replace ('/[^0-9a-z]/', '_', $f), 0, 20);
        // derive type from field name
        $t = preg_match('/date/i', $f) ? 'TIMESTAMP' : 'VARCHAR(50)';
        $field_count++;
        $fields[] = "`$f` $t";
    }
}

$sql = "CREATE TABLE IF NOT EXISTS $table (" . implode(', ', $fields) . ');';
echo $sql . "\n";
// $db->query($sql);
while ( ($data = fgetcsv($handle) ) !== FALSE ) {
    $values = array();
    for($i=0;$i<$field_count; $i++) {
        $v = $data[$i];
        preg_match('/TIMESTAMP/',$fields[$i]) and $v = date('Y-m-d H:i:s', strtotime($v));
        $values[] = '\''.addslashes($v).'\'';
    }
    $sql = "INSERT into $table values(" . implode(', ', $values) . ');';
    echo $sql . "\n";
    // $db->query($sql);
}
fclose($handle);
ini_set('auto_detect_line_endings',FALSE);