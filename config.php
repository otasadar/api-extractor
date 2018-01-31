<?php

/*
 * Refresh token generator : https://developers.google.com/oauthplayground/
 *  - Fill OAuth Client ID & OAuth Client secret:
 *  - Select scopes
 *  - Authorizes and get refresh token
 *
 * DCM query report generator : https://developers.google.com/doubleclick-advertisers/v3.0/reports/insert
 */

// General Variables
//$storage_data = '{"client":"annalect-api-jobs@annalect-api-jobs.iam.gserviceaccount.com","access_token":"ya29.c.ElwIBQ9QzuTBMDhi1oW74_ibk5T99f_g-kvJ5_fTuJOVK9Flr-Fk3aJWcyFPyYoQgTwnO6_DtMj_kzhUFBSezIJy120LplyFWrn6q1sinsDOmDo6BP5IrJqlO4fi5A","key":"-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC8XEBqrBmjW7mn\n8O+5k3DvnRxGsw1ZPPNVFEb3wOoYdmnxPQ0YInUVbm6axWwh2aMc6unB+yBSgKkF\nL+i/4R21m+xPUX84IApJRmYa1zFg4eqJU/XUZfvdKbXLNnVn7KlMhrTkoFp10ZFH\ngyt/0AfqE5GqX0sJ3rhwZxP5bmsWZnC0InEtVxlg/QvWc1OLyuLIyedU+gg9KAaG\nDJrx3aBGCbzxl5f+BLhdWeByx3JTouand2kQkuVQDAxClA0RfB7DPVfMRPgDV8sM\nXV08l5cOnXT6FCK9lNoMRHr1/s8kcrABJcBp04+9aPYzIwSsRU3amtnQLOjlLBm8\nCc9k/AnLAgMBAAECggEACeWg/tnfGP3DUgcvWWVdXEG5kB9tVqCEupYvqmnpAvMJ\n2wKVswxwcXlVd21jIp4wW18efDJJPvyCpQKg7KAT8wxnzL0f/Z91WudHiyZ+cjQ8\nog2Wz5uyMas04aIRZi4BsUMsswyX5DPoOcUzHmqcV9jCYRzTsQeSUlmgK415M/qD\n4vKKDebnH287+qJk1th+pg1aAUM1lJvXJGkDUAlKkWwEo+yRMVYdzz6TJHQiyD9B\nJbB9DsL0IInLHpxQxApl8e9bFV61IYfNlmo8GWrRR82EsbT+SantY4gHf1BP3vtv\nClJrGDZwKRjnZx4kCDmXKc5oMOB1cn4/nOe5WSjSTQKBgQDcI2KQGW07AQhRPGg+\nqoMNOX4UZjJCe1HanrAnOKbudBkBRA9lqp0jW5IYS9mt2RBgsM8Y1HhEUFmmyOHA\nukQtMk6dMq8h2mI8vJGjiEQeIB+TcwbINZJIokAud/M7LqNNnTE4oaYm2bnriayu\nkhDIYQylZIbzke5Y1YRVIGvKhwKBgQDbC5tW6m7w0vAgRodGAL1x+U8qRvjsw3pg\nXwqyBOyapXoTvcw2MuTNS4ADGpq1eGBjBsehb5XvW14d7WqHfcDPYXq3IE9vB1Ls\nATp+0D9/IVt13LfvGBIaD0AH/zr1N1IdYZRMtOuNIQMMl+B8qL+pnPmE4Ct0RPPs\nTirotoTDnQKBgQDQNrvL9fDFxUU7qPokg5yuznk9DChvjzqtoDiW8FOb6L2Z3+j8\nTTKRtdPqHRwH/e4qtjE7mAMlAia5xPkaFFPVt+Z5cu4JBAi0z9qkpYdgQxv6l+qL\nRXhWMPipuxSZHpShHZPnr6V6y6a5bJ+jAk7TaE/Qw9OM37Nj3Jhs99xcUwKBgCmf\nMfw4/a2rF0+6txeZKmZOzjklVUV/+2/2f0zGXMMh8Glx5iziTNGpqABu/LjAz+fh\nMOu/DUl3HhInu9dVEN8XEb9cV1usk5gev6O7JGWezAdAUn8PHtluzmb2m5he066b\njRdqRVwCytaIwXJOimTLXCpggkFMnODpFYQ0slONAoGBAIlolThayqWVasEdX0KR\ncmYV6h5KmT/trjhKpMlXz0bPvegZpF7XFbodKF+0eA1gV5MmnDEQy4epGhD0zRt3\noKyoM08zyFLqpKZqxl+5gTyqbEbzbT/tuo6rydad/01IbWlcRLaNpBfmsSCdEHO3\nTqeSqspjDoxfvQISvBAfHIzP\n-----END PRIVATE KEY-----\n","scope":"https:\/\/www.googleapis.com\/auth\/devstorage.full_control https:\/\/www.googleapis.com\/auth\/cloud-taskqueue","bucket":"annalect-dashboarding"}';
$storage_data = '{"client":"annalect-api-jobs@annalect-api-jobs.iam.gserviceaccount.com","access_token":"ya29.c.ElwIBQ9QzuTBMDhi1oW74_ibk5T99f_g-kvJ5_fTuJOVK9Flr-Fk3aJWcyFPyYoQgTwnO6_DtMj_kzhUFBSezIJy120LplyFWrn6q1sinsDOmDo6BP5IrJqlO4fi5A","key":"-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC8XEBqrBmjW7mn\n8O+5k3DvnRxGsw1ZPPNVFEb3wOoYdmnxPQ0YInUVbm6axWwh2aMc6unB+yBSgKkF\nL+i/4R21m+xPUX84IApJRmYa1zFg4eqJU/XUZfvdKbXLNnVn7KlMhrTkoFp10ZFH\ngyt/0AfqE5GqX0sJ3rhwZxP5bmsWZnC0InEtVxlg/QvWc1OLyuLIyedU+gg9KAaG\nDJrx3aBGCbzxl5f+BLhdWeByx3JTouand2kQkuVQDAxClA0RfB7DPVfMRPgDV8sM\nXV08l5cOnXT6FCK9lNoMRHr1/s8kcrABJcBp04+9aPYzIwSsRU3amtnQLOjlLBm8\nCc9k/AnLAgMBAAECggEACeWg/tnfGP3DUgcvWWVdXEG5kB9tVqCEupYvqmnpAvMJ\n2wKVswxwcXlVd21jIp4wW18efDJJPvyCpQKg7KAT8wxnzL0f/Z91WudHiyZ+cjQ8\nog2Wz5uyMas04aIRZi4BsUMsswyX5DPoOcUzHmqcV9jCYRzTsQeSUlmgK415M/qD\n4vKKDebnH287+qJk1th+pg1aAUM1lJvXJGkDUAlKkWwEo+yRMVYdzz6TJHQiyD9B\nJbB9DsL0IInLHpxQxApl8e9bFV61IYfNlmo8GWrRR82EsbT+SantY4gHf1BP3vtv\nClJrGDZwKRjnZx4kCDmXKc5oMOB1cn4/nOe5WSjSTQKBgQDcI2KQGW07AQhRPGg+\nqoMNOX4UZjJCe1HanrAnOKbudBkBRA9lqp0jW5IYS9mt2RBgsM8Y1HhEUFmmyOHA\nukQtMk6dMq8h2mI8vJGjiEQeIB+TcwbINZJIokAud/M7LqNNnTE4oaYm2bnriayu\nkhDIYQylZIbzke5Y1YRVIGvKhwKBgQDbC5tW6m7w0vAgRodGAL1x+U8qRvjsw3pg\nXwqyBOyapXoTvcw2MuTNS4ADGpq1eGBjBsehb5XvW14d7WqHfcDPYXq3IE9vB1Ls\nATp+0D9/IVt13LfvGBIaD0AH/zr1N1IdYZRMtOuNIQMMl+B8qL+pnPmE4Ct0RPPs\nTirotoTDnQKBgQDQNrvL9fDFxUU7qPokg5yuznk9DChvjzqtoDiW8FOb6L2Z3+j8\nTTKRtdPqHRwH/e4qtjE7mAMlAia5xPkaFFPVt+Z5cu4JBAi0z9qkpYdgQxv6l+qL\nRXhWMPipuxSZHpShHZPnr6V6y6a5bJ+jAk7TaE/Qw9OM37Nj3Jhs99xcUwKBgCmf\nMfw4/a2rF0+6txeZKmZOzjklVUV/+2/2f0zGXMMh8Glx5iziTNGpqABu/LjAz+fh\nMOu/DUl3HhInu9dVEN8XEb9cV1usk5gev6O7JGWezAdAUn8PHtluzmb2m5he066b\njRdqRVwCytaIwXJOimTLXCpggkFMnODpFYQ0slONAoGBAIlolThayqWVasEdX0KR\ncmYV6h5KmT/trjhKpMlXz0bPvegZpF7XFbodKF+0eA1gV5MmnDEQy4epGhD0zRt3\noKyoM08zyFLqpKZqxl+5gTyqbEbzbT/tuo6rydad/01IbWlcRLaNpBfmsSCdEHO3\nTqeSqspjDoxfvQISvBAfHIzP\n-----END PRIVATE KEY-----\n","scope":"https:\/\/www.googleapis.com\/auth\/devstorage.full_control https:\/\/www.googleapis.com\/auth\/cloud-taskqueue","bucket":"api-jobs-files"}';
$dcm_today= date('Y-m-d'); // 2018-01-24
$dcm_yesterday= date("Y-m-d",  strtotime(date("Y-m-d", strtotime($dcm_today)) . " -1 day") );
$dcm_last_6months= date("Y-m-d",  strtotime(date("Y-m-d", strtotime($dcm_today)) . " -6 month") );
$current_profileId = '123456789';
$current_floodlightConfigId = '123456789';
$current_advertiserId = '123456789';

// API Adwords - Credentials
$client_id = '1072155568501-rpcfah9qpkg9jro4c6c9sh62go6pm7oe.apps.googleusercontent.com';
$client_secret = 'PAx5fz386w0groUL8JFgdVuQ';
$developer_token = 'RPVuoNbWrFpSxE-UCabf9w';
$extractions = [];


/*
     _          _                                 _
    / \      __| | __      __   ___    _ __    __| |  ___
   / _ \    / _` | \ \ /\ / /  / _ \  | '__|  / _` | / __|
  / ___ \  | (_| |  \ V  V /  | (_) | | |    | (_| | \__ \
 /_/   \_\  \__,_|   \_/\_/    \___/  |_|     \__,_| |___/

 */
/*
array_push($extractions, array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'dashboard1',
    'file_name' => 'performance.csv',
    'refresh_token' => '1/Fb-kcD0UFz63kdX2fg9tTPN3QR0izXPm0Tdkvvv_KOw',
    'accounts' => array('156-469-0702', '575-470-1972', '606-092-7999', '204-292-7012'),
    'report' => 'KEYWORDS_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,Criteria,KeywordMatchType,ClickType,Clicks,Impressions,Cost,Conversions',
    'date' => 'YESTERDAY'
));
*/



/*
 / ___|  | |_    __ _   _ __     __| |   __ _   _ __    __| |
 \___ \  | __|  / _` | | '_ \   / _` |  / _` | | '__|  / _` |
  ___) | | |_  | (_| | | | | | | (_| | | (_| | | |    | (_| |
 |____/   \__|  \__,_| |_| |_|  \__,_|  \__,_| |_|     \__,_|
 */


array_push($extractions, array(
    'api' => 'dcm',
    'api_type' => 'google',
    'extraction_name' => 'dashboard1',
    'report_type' => "STANDARD",
    'max_execution_sec' => 1000,
    'file_name' => "standard_{profileId}_".$dcm_yesterday.".csv",
    'refresh_token' => '1/ymsZ6LP831oYWPc71ULlMt5hQG7zxs1nJG3SISJL7birTQexP-s4qh2O1RdHXIjH',
    'profileIds' => array('2188767','2896506','2718216','2872652','2719829','2719739','2727409'),

    'json_request' => '{
  "name": "test alex",
  "type": "STANDARD",
  "delivery": {
    "recipients": [
      {
        "deliveryType": "LINK",
        "email": "annalectautomation@gmail.com"
      }
    ]
  },
  "schedule": {
    "active": false,
    "expirationDate": "2018-01-25",
    "repeats": "MONTHLY",
    "startDate": "2018-01-25",
    "runsOnDayOfMonth": "DAY_OF_MONTH",
    "every": 12
  },
  "criteria": {
    "dateRange": {
       "relativeDateRange": "YESTERDAY"
    },
    "dimensions": [
      {
        "name": "dfa:campaign"
      },
      {
        "name": "dfa:site"
      },
      {
        "name": "dfa:placement"
      },
      {
        "name": "dfa:creativeSize"
      },
      {
        "name": "dfa:advertiser"
      },
      {
        "name": "dfa:advertiserId"
      },
      {
        "name": "dfa:campaignId"
      },
      {
        "name": "dfa:date"
      }
    ],
    "metricNames": [
      "dfa:bookedImpressions",
      "dfa:bookedClicks",
      "dfa:plannedMediaCost",
      "dfa:impressions",
      "dfa:clicks",
      "dfa:mediaCost",
      "dfa:richMediaInteractions",
      "dfa:richMediaInteractionRate",
      "dfa:richMediaVideoPlays",
      "dfa:richMediaVideoCompletions"
    ]
  }
}'
));


/*
  _____   _                       _   _   _           _       _
 |  ___| | |   ___     ___     __| | | | (_)   __ _  | |__   | |_
 | |_    | |  / _ \   / _ \   / _` | | | | |  / _` | | '_ \  | __|
 |  _|   | | | (_) | | (_) | | (_| | | | | | | (_| | | | | | | |_
 |_|     |_|  \___/   \___/   \__,_| |_| |_|  \__, | |_| |_|  \__|
                                              |___/
 */

/*
 *
 *
 *
'profileIds' => array('2188767','2896506','2718216','2872652','2719829','2719739','2727409'),
    'floodlightConfigIds' => array(
        '2188767' =>  array('4711608', '4898321', '5308422'),
        '2896506' =>  array('6199969'),
        '2718216' =>  array('2376384'),
        '2872652' =>  array('3801822'),
        '2719829' =>  array('5454647','8271328'),
        '2719739' =>  array('5912534'),
        '2727409' =>  array('4251577','4116173','4944371','4509632','6567610','5089702','4745537','4343157','4359113','5584068','3313505','4797970','3253807','3125621','4884700','5002322','3047713')
    ),

    'profileIds' => array('2896506','2188767'),
    'floodlightConfigIds' => array(
        '2896506' =>  array('123456'),
        '2188767' =>  array('4711608', '4743311')
    ),
 */

/*
array_push($extractions, array(
    'api' => 'dcm',
    'api_type' => 'google',
    'extraction_name' => 'dashboard1',
    'max_execution_sec' => 1000,
    'report_type' => 'FLOODLIGHT',
    'file_name' => "floodlight_{profileId}_".$dcm_yesterday.".csv",
    'refresh_token' => '1/ymsZ6LP831oYWPc71ULlMt5hQG7zxs1nJG3SISJL7birTQexP-s4qh2O1RdHXIjH',

    'profileIds' => array('2188767','2896506','2718216','2872652','2719829','2719739','2727409'),
    'floodlightConfigIds' => array(
        '2188767' =>  array('4711608', '4898321', '5308422'),
        '2896506' =>  array('6199969'),
        '2718216' =>  array('2376384'),
        '2872652' =>  array('3801822'),
        '2719829' =>  array('5454647','8271328'),
        '2719739' =>  array('5912534'),
        '2727409' =>  array('4251577','4116173','4944371','4509632','6567610','5089702','4745537','4343157','4359113','5584068','3313505','4797970','3253807','3125621','4884700','5002322','3047713')
    ),


    'json_request' => '{
          "name": "test alex",
          "type": "FLOODLIGHT",
          "delivery": {
            "recipients": [
              {
                "deliveryType": "LINK",
                "email": "annalectautomation@gmail.com"
              }
            ]
          },
          "schedule": {
            "active": false,
            "expirationDate": "2018-01-24",
            "repeats": "MONTHLY",
            "startDate": "2018-01-24",
            "runsOnDayOfMonth": "DAY_OF_MONTH",
            "every": 12
          },
          "floodlightCriteria": {
            "dateRange": {
               "relativeDateRange": "YESTERDAY"
            },
            "dimensions": [
              {"name": "dfa:campaign"},
              {"name": "dfa:site"},
              {"name": "dfa:placement"},
              {"name": "dfa:creativeSize"},
              {"name": "dfa:advertiser"},
              {"name": "dfa:advertiserId"},
              {"name": "dfa:campaignId"},
              {"name": "dfa:date"},
              {"name": "dfa:floodlightConfigId"},
              {"name": "dfa:activity"},
              {"name": "dfa:activityId"}
            ],
            "metricNames": [
              "dfa:activityClickThroughConversions",
              "dfa:activityViewThroughConversions",
              "dfa:totalConversions"
            ],
            "floodlightConfigId": {
              "value": "123456",
              "dimensionName": "dfa:floodlightConfigId"
            }
          }
        }'
));
*/


/*
   ____                                ____    _                                    _
  / ___|  _ __    ___    ___   ___    |  _ \  (_)  _ __ ___     ___   _ __    ___  (_)   ___    _ __
 | |     | '__|  / _ \  / __| / __|   | | | | | | | '_ ` _ \   / _ \ | '_ \  / __| | |  / _ \  | '_ \
 | |___  | |    | (_) | \__ \ \__ \   | |_| | | | | | | | | | |  __/ | | | | \__ \ | | | (_) | | | | |
  \____| |_|     \___/  |___/ |___/   |____/  |_| |_| |_| |_|  \___| |_| |_| |___/ |_|  \___/  |_| |_|

 */



array_push($extractions, array(
    'api' => 'dcm',
    'api_type' => 'google',
    'extraction_name' => 'dashboard1',
    'max_execution_sec' => 200,
    'report_type' => "CROSS_DIMENSION_REACH",
    'file_name' => "crossreach_{advertiserId}_".$dcm_yesterday.".csv",
    'refresh_token' => '1/ymsZ6LP831oYWPc71ULlMt5hQG7zxs1nJG3SISJL7birTQexP-s4qh2O1RdHXIjH',

    'profileIds' => array('2188767','2896506','2718216','2872652','2719829','2719739','2727409'),
    'advertiserIds' => array(
        '2188767' =>  array('4743311', '4636880', '5308422'),
        '2896506' =>  array('6197584', '6203268', '6203788'),
        '2718216' =>  array('2376384'),
        '2872652' =>  array('3875593'),
        '2719829' =>  array('5452626','5454647','5449796'),
        '2719739' =>  array('5912534'),
        '2727409' =>  array('3824854','4473419','4251577','4116173','4944371','4509632','6567610','5089702','4745537','4343157','4359113','5584068','3313505','4797970','3253807','3125621','5032001','4884700','5002322','3047713'),
    ),



    'json_request' => '{
  "name": "test alex",
  "type": "CROSS_DIMENSION_REACH",
  "delivery": {
    "recipients": [
      {
        "deliveryType": "LINK",
        "email": "annalectautomation@gmail.com"
      }
    ]
  },
  "schedule": {
    "active": false,
    "expirationDate": "2018-01-24",
    "repeats": "MONTHLY",
    "startDate": "2018-01-24",
    "runsOnDayOfMonth": "DAY_OF_MONTH",
    "every": 12
  },
  "crossDimensionReachCriteria": {
    "dateRange": {
       "relativeDateRange": "YESTERDAY"
    },
    "metricNames": [
      "dfa:exclusiveClickReach",
      "dfa:exclusiveImpressionReach"
    ],
    "dimension": "CAMPAIGN",
    "breakdown": [
      {
        "name": "dfa:date",
        "kind": "dfareporting#sortedDimension"
      }
    ],
    "overlapMetricNames": [
      "dfa:overlapClickReach"
    ],
    "dimensionFilters": [
      {
        "dimensionName": "dfa:advertiser",
        "matchType": "EXACT",
        "id": "4743311"
      }
    ]
  }
}'
));
