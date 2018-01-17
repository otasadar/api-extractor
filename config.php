<?php

/*
 * Refresh token generator : https://developers.google.com/oauthplayground/
 * DCM query generator : https://developers.google.com/doubleclick-advertisers/v2.8/reports/insert#auth
 */

// General Variables
$storage_data = '{"client":"annalect-api-jobs@annalect-api-jobs.iam.gserviceaccount.com","access_token":"ya29.c.ElwIBQ9QzuTBMDhi1oW74_ibk5T99f_g-kvJ5_fTuJOVK9Flr-Fk3aJWcyFPyYoQgTwnO6_DtMj_kzhUFBSezIJy120LplyFWrn6q1sinsDOmDo6BP5IrJqlO4fi5A","key":"-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC8XEBqrBmjW7mn\n8O+5k3DvnRxGsw1ZPPNVFEb3wOoYdmnxPQ0YInUVbm6axWwh2aMc6unB+yBSgKkF\nL+i/4R21m+xPUX84IApJRmYa1zFg4eqJU/XUZfvdKbXLNnVn7KlMhrTkoFp10ZFH\ngyt/0AfqE5GqX0sJ3rhwZxP5bmsWZnC0InEtVxlg/QvWc1OLyuLIyedU+gg9KAaG\nDJrx3aBGCbzxl5f+BLhdWeByx3JTouand2kQkuVQDAxClA0RfB7DPVfMRPgDV8sM\nXV08l5cOnXT6FCK9lNoMRHr1/s8kcrABJcBp04+9aPYzIwSsRU3amtnQLOjlLBm8\nCc9k/AnLAgMBAAECggEACeWg/tnfGP3DUgcvWWVdXEG5kB9tVqCEupYvqmnpAvMJ\n2wKVswxwcXlVd21jIp4wW18efDJJPvyCpQKg7KAT8wxnzL0f/Z91WudHiyZ+cjQ8\nog2Wz5uyMas04aIRZi4BsUMsswyX5DPoOcUzHmqcV9jCYRzTsQeSUlmgK415M/qD\n4vKKDebnH287+qJk1th+pg1aAUM1lJvXJGkDUAlKkWwEo+yRMVYdzz6TJHQiyD9B\nJbB9DsL0IInLHpxQxApl8e9bFV61IYfNlmo8GWrRR82EsbT+SantY4gHf1BP3vtv\nClJrGDZwKRjnZx4kCDmXKc5oMOB1cn4/nOe5WSjSTQKBgQDcI2KQGW07AQhRPGg+\nqoMNOX4UZjJCe1HanrAnOKbudBkBRA9lqp0jW5IYS9mt2RBgsM8Y1HhEUFmmyOHA\nukQtMk6dMq8h2mI8vJGjiEQeIB+TcwbINZJIokAud/M7LqNNnTE4oaYm2bnriayu\nkhDIYQylZIbzke5Y1YRVIGvKhwKBgQDbC5tW6m7w0vAgRodGAL1x+U8qRvjsw3pg\nXwqyBOyapXoTvcw2MuTNS4ADGpq1eGBjBsehb5XvW14d7WqHfcDPYXq3IE9vB1Ls\nATp+0D9/IVt13LfvGBIaD0AH/zr1N1IdYZRMtOuNIQMMl+B8qL+pnPmE4Ct0RPPs\nTirotoTDnQKBgQDQNrvL9fDFxUU7qPokg5yuznk9DChvjzqtoDiW8FOb6L2Z3+j8\nTTKRtdPqHRwH/e4qtjE7mAMlAia5xPkaFFPVt+Z5cu4JBAi0z9qkpYdgQxv6l+qL\nRXhWMPipuxSZHpShHZPnr6V6y6a5bJ+jAk7TaE/Qw9OM37Nj3Jhs99xcUwKBgCmf\nMfw4/a2rF0+6txeZKmZOzjklVUV/+2/2f0zGXMMh8Glx5iziTNGpqABu/LjAz+fh\nMOu/DUl3HhInu9dVEN8XEb9cV1usk5gev6O7JGWezAdAUn8PHtluzmb2m5he066b\njRdqRVwCytaIwXJOimTLXCpggkFMnODpFYQ0slONAoGBAIlolThayqWVasEdX0KR\ncmYV6h5KmT/trjhKpMlXz0bPvegZpF7XFbodKF+0eA1gV5MmnDEQy4epGhD0zRt3\noKyoM08zyFLqpKZqxl+5gTyqbEbzbT/tuo6rydad/01IbWlcRLaNpBfmsSCdEHO3\nTqeSqspjDoxfvQISvBAfHIzP\n-----END PRIVATE KEY-----\n","scope":"https:\/\/www.googleapis.com\/auth\/devstorage.full_control https:\/\/www.googleapis.com\/auth\/cloud-taskqueue","bucket":"annalect-dashboarding"}';

// API Adwords - Credentials
$client_id = '1072155568501-rpcfah9qpkg9jro4c6c9sh62go6pm7oe.apps.googleusercontent.com';
$client_secret = 'PAx5fz386w0groUL8JFgdVuQ';
$developer_token = 'RPVuoNbWrFpSxE-UCabf9w';
$dashboards = [];


/*
 * Project [AAC] ADWORDS
 */

array_push($dashboards, array(
    'api' => 'adwords',
    'dashboard_name' => 'dashboard1',
    'file_name' => 'performance.csv',
    'refresh_token' => '1/Fb-kcD0UFz63kdX2fg9tTPN3QR0izXPm0Tdkvvv_KOw',
    'accounts' => array('156-469-0702', '575-470-1972', '606-092-7999', '204-292-7012'),
    'report' => 'KEYWORDS_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,Criteria,KeywordMatchType,ClickType,Clicks,Impressions,Cost,Conversions',
    'date' => 'YESTERDAY'
));



/*
 * Project DCM PENDING VALUES
 */


array_push($dashboards, array(
    'api' => 'dcm',
    'dashboard_name' => 'dashboard1',
    'file_name' => 'performance.csv',
    'refresh_token' => '1/uYioDAW2hg6SjtXBQgT-ZmVhhybv5Ua-DarjmpjM8Jq_VovxMnDSrY-5sMF1i1Ji',

    'profileIds' => array('2188767', '4224518'),
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
            "expirationDate": "2018-12-17",
            "repeats": "MONTHLY",
            "startDate": "2017-12-17",
            "runsOnDayOfMonth": "DAY_OF_MONTH",
            "every": 12
          },
          "criteria": {
            "dateRange": {
              "relativeDateRange": "LAST_30_DAYS"
            },
            "dimensions": [
              {
                "name": "dfa:campaign"
              }
            ],
            "metricNames": [
              "dfa:clicks"
            ]
          }
    }'
));

/*
 *    "kind": "dfareporting#userProfile",
   "etag": "\"7zlfo93Zjrhp9GMqB3etTE1Z_kA/42SGa1sB0sddVgDxXlL8a1RyFKU\"",
   "profileId": "2188767",
   "userName": "PHD_AFG_annalectautomation",
   "accountId": "109605",
   "accountName": "PHD Dubai - AFM - DCM"

 */