<?php
require '/home/ivan/dev/app/jira/report/vendor/autoload.php';
use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;

$created = '2016-11-27T00:00:00.882+0100';
$dateCreated = new DateTime($created);

$startDate = new DateTime('2017-12-11T00:00:00');
$endDate = new DateTime('2017-12-17T00:00:00');
$weekStart = $startDate->format("W");
$weekEnd = $endDate->format("W");
$sprints = null;
for($i=$weekEnd;$i>=$weekStart;$i--)
    $sprints.= '"'.$startDate->format("Y") . '/Week ' . $i.'",';
    
    $jql = '(project="Mrt/Drt izmjene") or sprint in('.rtrim($sprints, ',').')and project not in("T2 Hrvatska aktivnosti") ';
   // $jql='updated > startOfWeek()';
    
      $jql='sprint="2017/Week 50A" and project="T2 Hrvatska Aktivnosti"';
    //  $jql='project="T2 Hrvatska Aktivnosti"';
    $date = date($created); // you can put any date you want
    $nbDay = date('N', strtotime($date));
    $monday = new DateTime($date);
    
    
    $issueService = new IssueService();
    echo $jql.PHP_EOL;
    $ret = $issueService->search($jql, 0, 100, array(
        'key',
        'name',
        'worklog',
        'project',
        'summary',
        'components'
    ));
  /*  $ret = $issueService->search($jql, 100, 200, array(
        'key',
        'name',
        'worklog',
        'project',
        'summary',
        'components'
    ));
   */ 
    
    $i = 0;
    
    try {
        $dataHeader = 'Project,Sprint,Issue,Description,Working hours,Date';
        $data = null;
        $total = 0;
        $_components = array();
        foreach ($ret->issues as $issue) {
           // if ($issue->key !='T2HRAKT-201') continue;
            if (is_object($issue->fields->worklog))
                foreach ($issue->fields->worklog as $val) {
                    $time = 0;
                    if (! is_array($val))
                        continue;
                        foreach ($val as $log) {
                            if (new DateTime($log->created) > $startDate) {
                                $time = $log->timeSpentSeconds;
                                $week = new DateTime($log->created);
                                $programer = $log->author->displayName;
                                $date = $week->format("d.m.Y");
                                $sprint = $week->format("Y") . '/Week ' . $week->format("W");
                                $components = null;
                                if (is_array($issue->fields->components))
                                    foreach ($issue->fields->components as $component) {
                                        $components .= ',' . $component->name;
                                        array_push($_components, $component->name);
                                    }
                                if (! $components) {
                                    $components = ',N/A';
                                }
                                $data .= $programer.','.'"' . $issue->fields->project->name . '","' . $sprint . '",' . $issue->key . ',"' . $issue->fields->summary . '"' . ',' . round(ceil($time)/ (60 * 60)) . ',' . $date . $components . PHP_EOL;
                                $total += $time;
                            }
                        }
                }
        }
        $data .= 'TOTAL,' . round(ceil($total / (60 * 60)));
    } catch (JiraException $e) {
        var_dump($e);
    }
    $componentsHeader = array_unique($_components);
    $i = 0;
    foreach ($componentsHeader as $comp) {
        $i ++;
        $dataHeader .= ',Component' . $i;
    }
    echo $dataHeader . PHP_EOL . $data;
    ?>
