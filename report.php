<?php
require '/home/ivan/dev/app/jira/report/vendor/autoload.php';
use JiraRestApi\JiraException;
use JiraRestApi\Group\GroupService;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Project\ProjectService;

function getProgrammers()
{
    try {
        $programmer = array();
        $queryParam = [
            'groupname' => 'jira-software-users',
            'includeInactiveUsers' => true, // default false
            'startAt' => 0,
            'maxResults' => 50
        ];
        
        $gs = new GroupService();
        
        $ret = $gs->getMembers($queryParam);
        foreach ($ret->values as $user)
            array_push($programmer, array(
                'Programer' => $user->displayName,
                'key' => $user->key
            ));
        return json_encode($programmer);
    } catch (JiraException $e) {
        print("Error Occured! " . $e->getMessage());
    }
}

function getProjects()
{
    $project = array();
    $proj = new ProjectService();
    $prjs = $proj->getAllProjects();
    foreach ($prjs as $p)
        array_push($project, array(
            'Project' => $p->name,
            'key' => $p->key
        ));
    return json_encode($project);
}

function getReport($startdt, $enddt, $projekt, $programer=null)
{
    $report = array();
    $startDate = new DateTime($startdt);
    $endDate = new DateTime($enddt);
    $weekStart = $startDate->format("W");
    $weekEnd = $endDate->format("W");
    $sprints = null;
    for ($i = $weekEnd; $i >= $weekStart; $i --)
        $sprints .= '"' . $startDate->format("Y") . '/Week ' . $i . '",';
    
    $jql = 'project="' . $projekt . '" and sprint in(' . rtrim($sprints, ',') . ') and assignee ='.$programer.'';
    
    if($projekt)
        $filter .='and project="' . $projekt . '"';
   /* if($programer)
        $filter.='and assignee ='.$programer.'';
    */
        $jql ='updatedDate >= "'.$startDate->format("Y/m/d").' 00:00" and updatedDate <="'.$endDate->format("Y/m/d").' 23:59"'.$filter;
    $issueService = new IssueService();
    $ret1 = $issueService->search($jql, 0, 100, array(
        'key',
        'name',
        'worklog',
        'project',
        'summary',
        'components'
    ));
    
    $ret2 = $issueService->search($jql, 100, 200, array(
        'key',
        'name',
        'worklog',
        'project',
        'summary',
        'components'
    ));
    
    $ret3 = $issueService->search($jql, 200, 300, array(
        'key',
        'name',
        'worklog',
        'project',
        'summary',
        'components'
    ));
    
    
    $issues=array_merge($ret1->issues,$ret2->issues,$ret3->issues);
    
    foreach ($issues as $issue) {
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
                        $programerdata = $log->author->displayName;
                        $date = $week->format("d.m.Y");
                        $sprint = $week->format("Y") . '/Week ' . $week->format("W");

                        $data .= $programerdata . ',' . '"' . $issue->fields->project->name . '","' . $sprint . '",' . $issue->key . ',"' . $issue->fields->summary . '"' . ',' . round(ceil($time) / (60 * 60)) . ',' . $date . $components . PHP_EOL;
                        $total += $time;
                        if ($log->author->key ==$programer and $programer!=null)
                        array_push($report, array(
                            'Programer' => $programerdata,
                            'Sati' =>  round(ceil($time) / (60 * 60)),
                            'Datum'=>$date,
                            'Task'=> $issue->key ,
                            'Projekt'=>$issue->fields->project->name ,
                            'Opis'=>$issue->fields->summary
                        ));
                    }
                }
            }
    }
    
    return json_encode($report);
}

switch ($_GET['action']) {
    case 'programmer':
        echo getProgrammers();
        break;
    case 'project':
        echo getProjects();
        break;
    case 'report':
        $data = $_GET['filter'];
        echo getReport($data['startdt'], $data['enddt'], $data['projekt'], $data['programer']);
        break;
}