# SlackJiraIntegration
Customizable PHP script for adapting JIRA webhooks for a Slack integration. You can publish selected JIRA events to a chosen channel in Slack.


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


