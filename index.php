<?php
require 'GitLabHook.php';
require '/home/ivan/www/ivan/jira/resolved/php-jira-rest-client/vendor/autoload.php';
//require '/home/aivan/vendor/autoload.php';
use JiraRestApi\JiraException;
use JiraRestApi\Issue\Transition;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\RemoteLink\RemoteLink;
use JiraRestApi\RemoteLink\RemoteLinkService;


$data =file_get_contents("php://input");

$mergeRequest = new GitLabHook($data);

try{
    switch ($mergeRequest->getType()) {
        case 'merge_request':
            if($mergeRequest->getMergeStatus()=='can_be_merged'){
                $transition = new Transition();
                $issueService = new IssueService();
                $a= $issueService->create($issueField);
                $a->
                $rl = new RemoteLink();
                $rs = new RemoteLinkService();
                $rl->setUrl('https://docs.google.com/spreadsheets/d/'.$file);
                $rl->setTitle($mergeRequest->getCommitAuthor().' resolved issue with '.$mergeRequest->getCommitMessage());
                $transition->setTransitionId(801);
                foreach ($mergeRequest->getJiraTasks() as $issue){
                    $issueService->transition($issue, $transition);
                    $a = $rs->addRemoteLink($issue,$rl);
                }
            }
            break;
            
        default:
            # code...
            break;
    }
} catch (JiraException $e) {
    print("Error Occured! " . $e->getMessage());
}
?>
