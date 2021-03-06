<?php
/**
 * @link     https://en.wikipedia.org/wiki/Category:Postal_system Postal Systems by Country
 * @link     http://dmoztools.net/Reference/Directories/Address_and_Phone_Numbers/Postal_Codes/ DMOZ Post/Zip Code Info+DB
 * @link     https://en.wikipedia.org/wiki/List_of_postal_codes List of Postal Codes
 * @param $what
 * @param $codes
 * @return array
 * @internal param mixed $
 */
function get_codes_from($what, $codes) {
	$what = explode(',', $what);
	foreach ($what as $eachArea)
		if (trim($eachArea) != '' && trim($eachArea) != '- no codes -')
			$codes[] = "'".trim($eachArea)."'";
	return $codes;
}

require __DIR__.'/../../../include/functions.inc.php';
function_requirements('getcurlpage');
$page = getcurlpage('https://en.wikipedia.org/wiki/Special:Export/List_of_postal_codes');
$page = str_replace("\n\n", "\n", $page);
$lines = explode("\n", $page);
$found = [];
for ($x = 0, $xMax = sizeof($lines); $x < $xMax; $x++) {
	$line = $lines[$x];
	if ((trim($line) == '|-' || trim($line) == '|-.') && mb_substr($lines[$x + 1], 0, 1) != '!') {
		$x++;
		$country = preg_replace('/\| *\[\[Postal codes in [^\|]*\|(.*)\]\]/msU', '\1', $lines[$x]);
		$x++;
		$x++;
		$iso = preg_replace('/\| *\[\[ISO 3166-[0-9]*:[A-Z]*\|(.*)\]\]/msU', '\1', $lines[$x]);
		$x++;
		$area = trim(mb_substr($lines[$x], 1));
		$x++;
		$street = trim(mb_substr($lines[$x], 1));
		$x++;
		$notes = trim(mb_substr($lines[$x], 1));
		$codes = get_codes_from($area, []);
		$codes = get_codes_from($street, []);
		$found[] = $iso;
		echo "		'{$iso}' => [".str_replace(['N', 'A'], ['#', '@'], implode(', ', $codes)).'],'.(count($codes) == 0 ? '	' : '')."		// $country".(trim($notes) != '' ? ', Notes: '.$notes : '').PHP_EOL;
	}
}
$db = $GLOBALS['tf']->db;
$db->query('select * from country_t order by iso2;');
while ($db->next_record(MYSQL_ASSOC)) {
	if (!in_array($db->Record['iso2'], $found))
		echo "		'{$db->Record['iso2']}' => [],			// {$db->Record['short_name']}\n";
}
exit;



preg_match_all('/^\|-\.*.^\| \[\[Postal codes in [^\|]*\|(?P<country>[^$]*)\]\]$.^\| *(?P<since>[^$]*)$.^\| *\[\[ISO 3166-1:[A-Z]*\|(?P<iso>[^$]*)\]\]$.^\| *(?P<area>[^$]*)$.^\| *(?P<street>[^$]*)$.^\| (?P<notes>[^$]*)$/msU', $page, $matches);
//print_r($matches);
foreach ($matches['country'] as $idx => $country) {
	$iso = $matches['country_iso'][$idx];
	$area = $matches['area'][$idx];
	$street = $matches['street'][$idx];
	$notes = $matches['notes'][$idx];
	$codes = get_codes_from($area, []);
	$codes = get_codes_from($street, $codes);
	echo "		'{$iso}' => [".implode(', ', $codes)."]	// $country".(trim($notes) != '' ? ', Notes: '.$notes : '').PHP_EOL;
}
//$page = getcurlpage('https://en.wikipedia.org/wiki/List_of_postal_codes');
//function_requirements('xml2array');
//$data = xml2array($page, 1, 'attribute');
//$data = xml2array($page, 1);
//print_r($data);
exit;

/*
$file = file_get_contents('zip.txt');
$lines = explode("\n", $file);
$zips = [];
foreach ($lines as $line) {
	list($country, $street_zip, $street_zip, $notes) = explode("\t", $line);
	if (!isset($zips($country))) {
		$zips[$country] = [];
	$parts = explode(',', $area_zip);


	} else {
		echo "Country $country has area zip of $area_zip and Street zip $street zip    With Notes: $notes\n";
	}
}
*/
