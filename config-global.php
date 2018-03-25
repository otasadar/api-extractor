<?php


/*
 * Google Refresh token generator : https://developers.google.com/oauthplayground/
 *  - Check Use your own OAuth credentials  then Fill OAuth Client ID & OAuth Client secret:
 *  - Select API scopes
 *  - Authorizes and get refresh token
 *

 * Result file : /bucket/extraction-group/input/api-name/extraction-name.csv
 * Result file i.g : /api-extractor/aio_phd/input/dcm/cross-reach.csv

 * Extractions is the main object that will send across all task
 * Extractions->global - Any common data across all API
 * Extractions->items - Any particular extraction for a particular API, particular Dimensions/Metrics & particular dates
 *
 * For each task current extraction + global, will be combine in new object :'extraction'
 */


date_default_timezone_set('Asia/Dubai');


$extractions = [];
$extractions['items'] = [];

$extractions['global']['queue'] = "api-extractor";
$extractions['global']['project'] = "annalect-api-jobs";
$extractions['global']['location'] = "europe-west1";

$extractions['global']['google']['client_id'] = '1072155568501-rpcfah9qpkg9jro4c6c9sh62go6pm7oe.apps.googleusercontent.com';
$extractions['global']['google']['client_secret'] = 'PAx5fz386w0groUL8JFgdVuQ';
$extractions['global']['google']['developer_token'] = 'RPVuoNbWrFpSxE-UCabf9w';

$extractions['global']['google_oauth']['api_version'] = 'v4';
$extractions['global']['google_bigquery']['api_version'] = 'v2';

$extractions['global']['google_storage']['api_version'] = 'v1';
$extractions['global']['google_storage']['bucket'] = "annalect-dashboarding"; // copy to run.php/index.php too
$extractions['global']['google_storage']['client'] = "annalect-api-jobs@annalect-api-jobs.iam.gserviceaccount.com";
$extractions['global']['google_storage']['access_token'] = "ya29.c.ElwIBQ9QzuTBMDhi1oW74_ibk5T99f_g-kvJ5_fTuJOVK9Flr-Fk3aJWcyFPyYoQgTwnO6_DtMj_kzhUFBSezIJy120LplyFWrn6q1sinsDOmDo6BP5IrJqlO4fi5A";
$extractions['global']['google_storage']['key'] = "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC8XEBqrBmjW7mn\n8O+5k3DvnRxGsw1ZPPNVFEb3wOoYdmnxPQ0YInUVbm6axWwh2aMc6unB+yBSgKkF\nL+i/4R21m+xPUX84IApJRmYa1zFg4eqJU/XUZfvdKbXLNnVn7KlMhrTkoFp10ZFH\ngyt/0AfqE5GqX0sJ3rhwZxP5bmsWZnC0InEtVxlg/QvWc1OLyuLIyedU+gg9KAaG\nDJrx3aBGCbzxl5f+BLhdWeByx3JTouand2kQkuVQDAxClA0RfB7DPVfMRPgDV8sM\nXV08l5cOnXT6FCK9lNoMRHr1/s8kcrABJcBp04+9aPYzIwSsRU3amtnQLOjlLBm8\nCc9k/AnLAgMBAAECggEACeWg/tnfGP3DUgcvWWVdXEG5kB9tVqCEupYvqmnpAvMJ\n2wKVswxwcXlVd21jIp4wW18efDJJPvyCpQKg7KAT8wxnzL0f/Z91WudHiyZ+cjQ8\nog2Wz5uyMas04aIRZi4BsUMsswyX5DPoOcUzHmqcV9jCYRzTsQeSUlmgK415M/qD\n4vKKDebnH287+qJk1th+pg1aAUM1lJvXJGkDUAlKkWwEo+yRMVYdzz6TJHQiyD9B\nJbB9DsL0IInLHpxQxApl8e9bFV61IYfNlmo8GWrRR82EsbT+SantY4gHf1BP3vtv\nClJrGDZwKRjnZx4kCDmXKc5oMOB1cn4/nOe5WSjSTQKBgQDcI2KQGW07AQhRPGg+\nqoMNOX4UZjJCe1HanrAnOKbudBkBRA9lqp0jW5IYS9mt2RBgsM8Y1HhEUFmmyOHA\nukQtMk6dMq8h2mI8vJGjiEQeIB+TcwbINZJIokAud/M7LqNNnTE4oaYm2bnriayu\nkhDIYQylZIbzke5Y1YRVIGvKhwKBgQDbC5tW6m7w0vAgRodGAL1x+U8qRvjsw3pg\nXwqyBOyapXoTvcw2MuTNS4ADGpq1eGBjBsehb5XvW14d7WqHfcDPYXq3IE9vB1Ls\nATp+0D9/IVt13LfvGBIaD0AH/zr1N1IdYZRMtOuNIQMMl+B8qL+pnPmE4Ct0RPPs\nTirotoTDnQKBgQDQNrvL9fDFxUU7qPokg5yuznk9DChvjzqtoDiW8FOb6L2Z3+j8\nTTKRtdPqHRwH/e4qtjE7mAMlAia5xPkaFFPVt+Z5cu4JBAi0z9qkpYdgQxv6l+qL\nRXhWMPipuxSZHpShHZPnr6V6y6a5bJ+jAk7TaE/Qw9OM37Nj3Jhs99xcUwKBgCmf\nMfw4/a2rF0+6txeZKmZOzjklVUV/+2/2f0zGXMMh8Glx5iziTNGpqABu/LjAz+fh\nMOu/DUl3HhInu9dVEN8XEb9cV1usk5gev6O7JGWezAdAUn8PHtluzmb2m5he066b\njRdqRVwCytaIwXJOimTLXCpggkFMnODpFYQ0slONAoGBAIlolThayqWVasEdX0KR\ncmYV6h5KmT/trjhKpMlXz0bPvegZpF7XFbodKF+0eA1gV5MmnDEQy4epGhD0zRt3\noKyoM08zyFLqpKZqxl+5gTyqbEbzbT/tuo6rydad/01IbWlcRLaNpBfmsSCdEHO3\nTqeSqspjDoxfvQISvBAfHIzP\n-----END PRIVATE KEY-----\n";
$extractions['global']['google_storage']['scope'] = 'https://www.googleapis.com/auth/devstorage.full_control https://www.googleapis.com/auth/devstorage.read_only https://www.googleapis.com/auth/devstorage.read_write https://www.googleapis.com/auth/cloud-taskqueue';

$extractions['global']['google_sheet']['api_version'] = 'v4';
$extractions['global']['google_sheet']['refresh_token'] = '1/5Ex-mkYPcC96B8ycBWnNj_L1rtH46eTjE83PRIA_GadGpv_zXi070QcTmrGapWGf';
$extractions['global']['google_sheet']['email_credential'] = 'analyticsuae@annalect.com';
$extractions['global']['google_sheet']['sheet_id'] = '15917ONHrgyYmU49s_wvpjTguPXj9Dqbk5bhKMBNyMis'; // prod
//$extractions['global']['google_sheet']['sheet_id'] = '1oUslYYAHVtqTwqUSHsPXkXH4EDNC-JGiXPQQAiyrQc0'; //staging

$extractions['global']['google_storage_transfer']['api_version'] = 'v1';
$extractions['global']['google_storage_transfer']['refresh_token'] = '1/jCtz0A-FtgG1aTqcBj9KMIj0krcjEhb99KR7TR-shs0';
$extractions['global']['google_storage_transfer']['email_credential'] = 'unknown';

$extractions['global']['tasks']['api_version'] = 'v2beta2';
$extractions['global']['tasks']['refresh_token'] = '1/SPvjhXVghH7SKYY2w-VFyCgI1xBBn40DMWNo_EU6M-8';
$extractions['global']['tasks']['email_credential'] = 'analyticsuae@annalect.com';

$extractions['global']['google_compute']['api_version'] = 'v1';
$extractions['global']['google_compute']['refresh_token'] = '1/sVw1xELv02Lqo-7zuu45flooZL8u8OOn07pxP6NU7Q8';
$extractions['global']['google_compute']['email_credential'] = 'unknown';

// AdWords API explorer : https://developers.google.com/adwords/api/docs/reference/v201705/ReportDefinitionService
// AdWords Queries Tester : https://www.awql.me/adwords
$extractions['global']['adwords']['api_version'] = 'v201705';
$extractions['global']['adwords']['today'] = date('Ymd'); // YYYYMMDD
$extractions['global']['adwords']['yesterday'] = date("Ymd", strtotime(date("Ymd", strtotime($extractions['global']['adwords']['today'])) . " -1 day"));
$extractions['global']['adwords']['last_6months'] = date("Ymd", strtotime(date("Ymd", strtotime($extractions['global']['adwords']['today'])) . " -6 month"));
$extractions['global']['adwords']['historic'] = '20170601';

// DCM query report generator : https://developers.google.com/doubleclick-advertisers/v3.0/reports/insert
$extractions['global']['dcm']['api_version'] = 'v3.0';
$extractions['global']['dcm']['today'] = date('Y-m-d'); // 2018-01-24
$extractions['global']['dcm']['yesterday'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($extractions['global']['dcm']['today'])) . " -1 day"));
$extractions['global']['dcm']['last_6months'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($extractions['global']['dcm']['today'])) . " -6 month"));

$extractions['global']['facebook']['api_version'] = 'v2.10';
$extractions['global']['facebook']['today'] = date('Y-m-d'); // 2018-01-24
$extractions['global']['facebook']['yesterday'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($extractions['global']['facebook']['today'])) . " -1 day"));
$extractions['global']['facebook']['last_6months'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($extractions['global']['facebook']['today'])) . " -6 month"));
$extractions['global']['facebook']['historic'] = '2017-06-01';
$extractions['global']['facebook']['long_token'] = 'EAATzANHol0ABAFPjoxdbpkMoJMYWLCaMaYUKWl77AnCmgBZCj9v4ymwnUNQvKMWBVcTOwMcO1nZA8tfspSeWteYptC8FZBe2cS54DrfljsIFCImB3Ovo4I1WvKxYtr1zdFK9AhA5thauxStmjtmFYhGu6of6PIZD';

// DBM query report generator : https://developers.google.com/bid-manager/v1/
$extractions['global']['dbm']['api_version'] = 'v1';
$extractions['global']['dbm']['today'] = date('Y-m-d'); // 2018-01-24
$extractions['global']['dbm']['yesterday'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($extractions['global']['dbm']['today'])) . " -1 day"));
$extractions['global']['dbm']['historic'] = '2017-06-01';

// GA API https://developers.google.com/analytics/devguides/reporting/core/v4/
$extractions['global']['ga']['api_version'] = 'v4';
$extractions['global']['ga']['today'] = date('Y-m-d'); // 2018-01-24
$extractions['global']['ga']['yesterday'] = date("Y-m-d",  strtotime(date("Y-m-d", strtotime($extractions['global']['ga']['today'] )) . " -1 day") );
$extractions['global']['ga']['last_6months'] = date("Y-m-d",  strtotime(date("Y-m-d", strtotime($extractions['global']['ga']['today'] )) . " -6 month") );

// DS https://developers.google.com/apis-explorer/#p/doubleclicksearch/v2/doubleclicksearch.reports.request
// DS Main query https://developers.google.com/doubleclick-search/v2/report-types/campaign
$extractions['global']['ds']['api_version'] = 'v2';
$extractions['global']['ds']['today'] = date('Y-m-d'); // 2018-01-24
$extractions['global']['ds']['yesterday'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($extractions['global']['dbm']['today'])) . " -1 day"));
$extractions['global']['ds']['historic'] = '2017-06-01';

// https://tech.yandex.com/direct/doc/dg/concepts/about-docpage/
$extractions['global']['yandex']['api_version'] = 'v5';
$extractions['global']['yandex']['today'] = date('Y-m-d'); // 2018-01-24
$extractions['global']['yandex']['yesterday'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($extractions['global']['dbm']['today'])) . " -1 day"));
$extractions['global']['yandex']['historic'] = '2017-06-01';


$extractions['global']['date']['today'] = date('Y-m-d'); // 2018-01-24
$extractions['global']['date']['yesterday'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($extractions['global']['date']['today'])) . " -1 day"));
