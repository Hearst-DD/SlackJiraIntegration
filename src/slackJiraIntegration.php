<? 
/******
Dec 22 2014 tedmetzger
Custom JIRA Slack Integration
There is already a JIRA integration made by slack, but it is missing many important events, like when a ticket is assigned in JIRA for example.

To set this up:

1) You need Slack, admin rights on your JIRA, and a public-facing web server running PHP where you can put this code.


2) Set up a DIY Incoming Webhook on Slack. 

	Go to this URL (modify the URL to be for your team): https://yourteamhere.slack.com/services/new/incoming-webhook

	and pick a channel of your choosing. You can actually have JIRA post to a different channel than the one you select, but you must choose one here.


3) Put this page on a public-facing web site.
	
	Modify the code in the CONFIG section below as necessary. 

	Test it out:  http://yourdomain.com/jira.php?slack_channel=[your_slack_channel]&slack_url=https://hooks.slack.com/services/[UNIQUE]/[IDENTIFIER]/[STRING]

	slack_channel is the name of your channel, minus the #
	slack_url is the URL Slack gave you for your DIY Incoming Webhook
	slack_user is an optional param to at-mention a slack user. you don't need to add a @


4) Set up a webhook on JIRA. 

	Keep the URL from step 3) handy. Logged in as a JIRA admin, go to the webhooks page on JIRA (modify the URL to be for your team): https://yourteamhere.atlassian.net/plugins/servlet/webhooks

	Create a webhook.

	For the URL, use the one you made in step 3). http://yourdomain.com/jira.php?slack_channel=[your_slack_channel]&slack_url=https://hooks.slack.com/services/[UNIQUE]/[IDENTIFIER]/[STRING]

	In the Jira Webhook, you can specify what events should send a message via Slack. You can reuse the URL from step 3)  (and you can modify the slack_channel and slack_user if desired). 
	This allows you to target different Slack users and channels for different JIRA events. 

******/



/********
CONFIG
********/

#you can specify a slack channel as a query param in the URL of your Jira webhooks call to this page. or you can add a default channel here.
$defaultSlackChannel = "htv_jira";

#text that prints out at the beginning of each post to Slack
$jiraUpdatedText = "JIRA ticket assigned to you has been created/modified. ";

#fully qualified path for your JIRA installation on the web, like: https://yourteam.atlassian.net/browse/
$jiraIssuesPath = "https://yourteam.atlassian.net/browse/";

#$slackUrlStem is a weak security check. This is the URL or a substring of your slack integration, like:  hooks.slack.com/services/[UNIQUE]/[IDENTIFIERS]/
#you can leave it as a zero-length string, but that's not recommended
$slackUrlStem = "hooks.slack.com/services/[UNIQUE]/[IDENTIFIERS]/"; 

$lineBreak = '\n';


#query params
$slackChannel = isset($_GET['slack_channel']) ? $_GET['slack_channel'] :  $defaultSlackChannel;
$slackUserTag = isset($_GET['slack_user']) ? "<@".$_GET['slack_user'].">" :  "";
$slackUrl = $_GET['slack_url'];

#weak security check.
$pos = strrpos($slackUrl, $slackUrlStem); 
if ($pos === false) { 
    exit();
}

#fields parsed from "webhook" JIRA issue JSON. More info: https://yourteam.atlassian.net/plugins/servlet/webhooks
$postBody = file_get_contents('php://input');
$postBodyObject = json_decode($postBody);

$jiraIssueKey = $postBodyObject->issue->key;
$jiraIssueSummary = $postBodyObject->issue->fields->summary;
$jiraCommentAuthor = $postBodyObject->comment->author->name;
$jiraCommentBody = $postBodyObject->comment->body;
$jiraUpdatedText = $slackUserTag.$jiraUpdatedText;


#slack payload 
$payloadText = $jiraUpdatedText . " <" . $jiraIssuesPath. $jiraIssueKey . "|" . $jiraIssueKey . "> ("  . $jiraIssueSummary . ")  " . $lineBreak. $lineBreak;
if($jiraCommentAuthor && $jiraCommentBody){
	$payloadText .=  " *" . $jiraCommentAuthor . "* _" . $jiraCommentBody . "_";
}
$payloadText = urlencode($payloadText);
$payload = "payload={\"channel\": \"#".$slackChannel."\", \"username\": \"jira-bot\", \"text\": \"".$payloadText."\", \"icon_emoji\": \":loudspeaker:\"}";


# post to slack
$curl_handle=curl_init();
curl_setopt($curl_handle, CURLOPT_URL,$slackUrl);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, 'jira-bot');
curl_setopt($curl_handle, CURLOPT_POSTFIELDS,  $payload );
$query = curl_exec($curl_handle);
curl_close($curl_handle);
?>

DEBUG:
<br/><br/>
<? echo "Slack response : ". $query ?><hr/>
<? echo "Slack Channel : ". $slackChannel ?><hr/>
<? echo "Slack Url : ". $slackUrl ?><hr/>
<? echo "Slack User Tag : ". $slackUserTag ?><hr/>
