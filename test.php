<?php
header("Access-Control-Allow-Origin: *");

require '/home/ivan/www/ivan/jira/resolved/php-jira-rest-client/vendor/autoload.php';

use JiraRestApi\Project\ProjectService;
use JiraRestApi\JiraException;
use JiraRestApi\Group\GroupService;
try {
    $proj = new ProjectService();
    $iss = $_GET[issue];
    $prjs = $proj->getAllProjects();
    echo '<form action="copyBackend.php" method="post">
 <fieldset>
  <legend>Projekt</legend>';
    echo '<input list="project' . $iss . '" name="project' . $iss . '" id="projectinput' . $iss . '" ><datalist id="project' . $iss . '">';
    foreach ($prjs as $p) {
        echo '<option data-id="' . $p->key . '" value="' . $p->key . '">' . $p->name . '</option>';
    }
    echo '</datalist></fieldset>';
} catch (JiraException $e) {
    print("Error Occured! " . $e->getMessage());
}
try {
    $queryParam = [
        'groupname' => 'jira-software-users',
        'includeInactiveUsers' => true, // default false
        'startAt' => 0,
        'maxResults' => 50
    ];
    
    $gs = new GroupService();
    
    $ret = $gs->getMembers($queryParam);
    echo '<fieldset>
  <legend>Programer</legend>
<input list="user' . $iss . '" name="user' . $iss . '" id="userinput' . $iss . '">
<datalist id="user' . $iss . '">';
    foreach ($ret->values as $user) {
        echo '<option data-id="' . $user->key . '" value="' . $user->key . '">' . $user->displayName . '</option>';
    }
    echo '</datalist></fieldset>';
} catch (JiraException $e) {
    print("Error Occured! " . $e->getMessage());
}
echo '
<fieldset>
  <legend>Issue</legend>';
echo '<input type="hidden" id="issue' . $iss . '" value=' . $_GET[issue] . ' name="issue' . $iss . '">';
echo '</fieldset>';
echo '<input type="submit" onClick="update(\'' . $iss . '\')"></form>';
