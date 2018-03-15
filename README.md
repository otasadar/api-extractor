# annalect-api-extractor

Added API DBM #1 

 - Task Name dynamics for better log debug
 - Tasks Name patter for better identify
 - Modify app.yaml for task name dynamic
 - Added global DBM config setting
 - Added API version as global vars for all  al API's
 - Remove error 20x for curl
 - Rename in config.php $extractions['items']['extraction_name'] to $extractions['items']['extraction_group']
 - Now $extractions['items']['extraction_name'] means unique name for that extraction action
 - DBM storage report URL in a file, instead file because is too big, and PHP curl has memory leaks and interruptions
 - Add last task detector, using API Cloud Task beta
 - At the end of last task call a Talend endpoint to process report URL
 - Renamed result_log to live_log
 - Move syslog to gae_log for works also in local
 - Remove timeout for waiting report generation DBM response
 - DCM, DBM inject helper class in constructor
 - Added API version as a global variable for future updates and more visibility
 
 Added URL transfer :
 - Get URL content from URL and upload to Google Buckets
 -- Storage URLS ins files for asynchronous tasks
 -- Check tasks status with Google API task beta
 -- Send URL to Virtual Machine to get MD5
 -- Create a TSV file with URL + MD5
 -- Set Google TransferJob with TSV file
 -- Check transferjob status
 -- Move transfer job from tmp to final location
 -- Remove all tmp files (url, tsv, transfer)
 
 
Futures tasks
 - Move common vars from tasker to live_log
 - Add case in function set_curl in case of 40x or 50x for avoid continue sequence, for stopping or jump process 
 - DCM add validation after each request
 - AdWords combine files split by 2months range
 - DCM use tmp files for avoid memory leak
 - DCM on 404 error send error to sheets
 - Be sure when Empty response is not an error vs a real empty
 - Clean Adwords functions
 - Check ID duplicated
 - add message when timeout
 - remove profiles id duplicates in config file
 
 
 


  