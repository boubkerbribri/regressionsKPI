<?php

$f = fopen('res.json', 'w');

include("run.php");
if (getenv('VERSION') === false || getenv('FREEZE_DATE') === false || getenv('RELEASE_DATE') === false) {
    throw new Exception("VERSION, FREEZE_DATE and RELEASE_DATE vars are mandatory.");
}

//get the ".x" string from the version number
$version_x = substr_replace(VERSION , 'x', strrpos(VERSION , '.') +1);

echo sprintf("--- Retrieving data for version %s", VERSION) . PHP_EOL;
echo "--- PR data..." . PHP_EOL;
$first_pr = $client->api('search')
    ->issues('type:pr is:merged milestone:'.VERSION.' sort:updated-asc repo:prestashop/prestashop');

if ($first_pr['total_count'] > 0) {
    $first = $first_pr['items'][0];
    $date_first_merge = date('Y-m-d', strtotime($first['closed_at']));
} else {
    die("Couldn't find a first PR to correlate to..." . PHP_EOL);
}
$results = [
    'before_freeze' => [
        'Trivial' => 0,
        'Minor' => 0,
        'Major' => 0,
        'Critical' => 0,
    ],
    'before_release' => [
        'Trivial' => 0,
        'Minor' => 0,
        'Major' => 0,
        'Critical' => 0,
    ],
    'after_release' => [
        'Trivial' => 0,
        'Minor' => 0,
        'Major' => 0,
        'Critical' => 0,
    ],
];
$not_labelled = [
    'before_freeze' => [],
    'before_release' => [],
    'after_release' => []
];

//get number of regressions between start of the work and freeze date
$before_freeze = $client->api('search')
    ->issues('type:issue label:"Regression" label:"develop" created:'.$date_first_merge.'..'.FREEZE_DATE.' repo:prestashop/prestashop');
if ($before_freeze['total_count'] > 0) {
    $issues = $before_freeze['items'];

    foreach($issues as $issue) {
        if (isset($issue['labels']) && count($issue['labels']) > 0) {
            $found = false;
            foreach($issue['labels'] as $label) {
                if (in_array($label['name'], array_keys($results['before_freeze']))) {
                    $results['before_freeze'][$label['name']] += 1 ;
                    $found = true;
                }
            }
            if (!$found) {
                $not_labelled['before_freeze'][] = $issue['number'];
            }
        }
    }
}

//get number of regressions between freeze and release
$before_release = $client->api('search')
    ->issues('type:issue label:"Regression" label:"'.$version_x.'" created:'.FREEZE_DATE.'..'.RELEASE_DATE.' repo:prestashop/prestashop');
if ($before_release['total_count'] > 0) {
    $issues = $before_release['items'];

    foreach($issues as $issue) {
        if (isset($issue['labels']) && count($issue['labels']) > 0) {
            $found = false;
            foreach($issue['labels'] as $label) {
                if (in_array($label['name'], array_keys($results['before_release']))) {
                    $results['before_release'][$label['name']] += 1 ;
                    $found = true;
                }
            }
            if (!$found) {
                $not_labelled['before_release'][] = $issue['number'];
            }
        }
    }
}

//get number of regressions after release
$today = date('Y-m-d');
$after_release = $client->api('search')
    ->issues('type:issue label:"Regression" label:"'.VERSION.'" created:'.RELEASE_DATE.'..'.$today.' repo:prestashop/prestashop');
if ($after_release['total_count'] > 0) {
    $issues = $after_release['items'];

    foreach($issues as $issue) {
        if (isset($issue['labels']) && count($issue['labels']) > 0) {
            $found = false;
            foreach($issue['labels'] as $label) {
                if (in_array($label['name'], array_keys($results['after_release']))) {
                    $results['after_release'][$label['name']] += 1 ;
                    $found = true;
                }
            }
            if (!$found) {
                $not_labelled['after_release'][] = $issue['number'];
            }
        }
    }
}

//show results
echo "--- Before freeze date " . PHP_EOL;
echo sprintf("- Trivial:   %s", $results['before_freeze']['Trivial']) . PHP_EOL;
echo sprintf("- Minor:     %s", $results['before_freeze']['Minor']) . PHP_EOL;
echo sprintf("- Major:     %s", $results['before_freeze']['Major']) . PHP_EOL;
echo sprintf("- Critical:  %s", $results['before_freeze']['Critical']) . PHP_EOL;
echo "Total: " .
    ($results['before_freeze']['Trivial']+$results['before_freeze']['Minor']+$results['before_freeze']['Major']+$results['before_freeze']['Critical'])
    . PHP_EOL;
if (count($not_labelled['before_freeze']) > 0) {
    echo sprintf("/!\ There was %s issue(s) not labelled: %s", count($not_labelled['before_freeze']), implode(',', $not_labelled['before_freeze'])) . PHP_EOL;
}
echo PHP_EOL;
echo "--- Before release date " . PHP_EOL;
echo sprintf("- Trivial:   %s", $results['before_release']['Trivial']) . PHP_EOL;
echo sprintf("- Minor:     %s", $results['before_release']['Minor']) . PHP_EOL;
echo sprintf("- Major:     %s", $results['before_release']['Major']) . PHP_EOL;
echo sprintf("- Critical:  %s", $results['before_release']['Critical']) . PHP_EOL;
echo "Total: " .
    ($results['before_release']['Trivial']+$results['before_release']['Minor']+$results['before_release']['Major']+$results['before_release']['Critical'])
    . PHP_EOL;
if (count($not_labelled['before_release']) > 0) {
    echo sprintf("/!\ There was %s issue(s) not labelled: %s", count($not_labelled['before_release']), implode(',', $not_labelled['before_release'])) . PHP_EOL;
}
echo PHP_EOL;
echo "--- After release date " . PHP_EOL;
echo sprintf("- Trivial:   %s", $results['after_release']['Trivial']) . PHP_EOL;
echo sprintf("- Minor:     %s", $results['after_release']['Minor']) . PHP_EOL;
echo sprintf("- Major:     %s", $results['after_release']['Major']) . PHP_EOL;
echo sprintf("- Critical:  %s", $results['after_release']['Critical']) . PHP_EOL;
echo "Total: " .
    ($results['after_release']['Trivial']+$results['after_release']['Minor']+$results['after_release']['Major']+$results['after_release']['Critical'])
    . PHP_EOL;
if (count($not_labelled['after_release']) > 0) {
    echo sprintf("/!\ There was %s issue(s) not labelled: %s", count($not_labelled['after_release']), implode(',', $not_labelled['after_release'])) . PHP_EOL;
}
