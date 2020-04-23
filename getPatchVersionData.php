<?php

include("run.php");
if (getenv('VERSION') === false) {
    throw new Exception("VERSION var is mandatory.");
}


echo sprintf("--- Retrieving data for version %s", VERSION) . PHP_EOL;
echo "--- PR data..." . PHP_EOL;
$result_pr = $client->api('search')
    ->issues('type:pr is:merged milestone:'.VERSION.' repo:prestashop/prestashop');
echo sprintf("Found %s PRs.", $result_pr['total_count']) . PHP_EOL . PHP_EOL;

echo "--- Issues data..." . PHP_EOL;
$result_issues = $client->api('search')
    ->issues('type:issue is:closed label:"Regression" milestone:'.VERSION.' repo:prestashop/prestashop');

echo sprintf("Found %s issues.", $result_issues['total_count']) . PHP_EOL;
$results = [
    'Trivial' => 0,
    'Minor' => 0,
    'Major' => 0,
    'Critical' => 0,
];
$not_labelled = [];
if ($result_issues['total_count'] > 0) {
    $issues = $result_issues['items'];

    foreach($issues as $issue) {
        if (isset($issue['labels']) && count($issue['labels']) > 0) {
            $found = false;
            foreach($issue['labels'] as $label) {
                if (in_array($label['name'], array_keys($results))) {
                    $results[$label['name']] += 1 ;
                    $found = true;
                }
            }
            if (!$found) {
                $not_labelled[] = $issue['number'];
            }
        }
    }
}
echo sprintf("- Trivial:   %s", $results['Trivial']) . PHP_EOL;
echo sprintf("- Minor:     %s", $results['Minor']) . PHP_EOL;
echo sprintf("- Major:     %s", $results['Major']) . PHP_EOL;
echo sprintf("- Critical:  %s", $results['Critical']) . PHP_EOL;
if (count($not_labelled) > 0) {
    echo sprintf("/!\ There was %s issue(s) not labelled: %s", count($not_labelled), implode(',', $not_labelled)) . PHP_EOL;
}

