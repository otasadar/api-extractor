<?php

/*
 * Google Refresh token generator : https://developers.google.com/oauthplayground/
 *  - Check Use your own OAuth credentials  then Fill OAuth Client ID & OAuth Client secret:
 *  - Select API scopes
 *  - Authorizes and get refresh token
 *
 * AdWords API explorer : https://developers.google.com/adwords/api/docs/reference/v201705/ReportDefinitionService
 * AdWords Queries Tester : https://www.awql.me/adwords
 * DCM query report generator : https://developers.google.com/doubleclick-advertisers/v3.0/reports/insert
 */

// General Variables
date_default_timezone_set('Asia/Dubai');

$extractions = [];
$extractions['items'] = [];

// Staging
//$extractions['global']['queue'] = "api-extractor-staging";
//$extractions['global']['storage_data']['bucket'] = "api-extractor-staging";
//$extractions['global']['google_sheet']['sheet_id'] = '1oUslYYAHVtqTwqUSHsPXkXH4EDNC-JGiXPQQAiyrQc0';

$extractions['global']['queue'] = "api-extractor";
$extractions['global']['storage_data']['bucket'] = "annalect-dashboarding"; // copy to run.php too
$extractions['global']['storage_data']['client'] = "annalect-api-jobs@annalect-api-jobs.iam.gserviceaccount.com";
$extractions['global']['storage_data']['access_token'] = "ya29.c.ElwIBQ9QzuTBMDhi1oW74_ibk5T99f_g-kvJ5_fTuJOVK9Flr-Fk3aJWcyFPyYoQgTwnO6_DtMj_kzhUFBSezIJy120LplyFWrn6q1sinsDOmDo6BP5IrJqlO4fi5A";
$extractions['global']['storage_data']['key'] = "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC8XEBqrBmjW7mn\n8O+5k3DvnRxGsw1ZPPNVFEb3wOoYdmnxPQ0YInUVbm6axWwh2aMc6unB+yBSgKkF\nL+i/4R21m+xPUX84IApJRmYa1zFg4eqJU/XUZfvdKbXLNnVn7KlMhrTkoFp10ZFH\ngyt/0AfqE5GqX0sJ3rhwZxP5bmsWZnC0InEtVxlg/QvWc1OLyuLIyedU+gg9KAaG\nDJrx3aBGCbzxl5f+BLhdWeByx3JTouand2kQkuVQDAxClA0RfB7DPVfMRPgDV8sM\nXV08l5cOnXT6FCK9lNoMRHr1/s8kcrABJcBp04+9aPYzIwSsRU3amtnQLOjlLBm8\nCc9k/AnLAgMBAAECggEACeWg/tnfGP3DUgcvWWVdXEG5kB9tVqCEupYvqmnpAvMJ\n2wKVswxwcXlVd21jIp4wW18efDJJPvyCpQKg7KAT8wxnzL0f/Z91WudHiyZ+cjQ8\nog2Wz5uyMas04aIRZi4BsUMsswyX5DPoOcUzHmqcV9jCYRzTsQeSUlmgK415M/qD\n4vKKDebnH287+qJk1th+pg1aAUM1lJvXJGkDUAlKkWwEo+yRMVYdzz6TJHQiyD9B\nJbB9DsL0IInLHpxQxApl8e9bFV61IYfNlmo8GWrRR82EsbT+SantY4gHf1BP3vtv\nClJrGDZwKRjnZx4kCDmXKc5oMOB1cn4/nOe5WSjSTQKBgQDcI2KQGW07AQhRPGg+\nqoMNOX4UZjJCe1HanrAnOKbudBkBRA9lqp0jW5IYS9mt2RBgsM8Y1HhEUFmmyOHA\nukQtMk6dMq8h2mI8vJGjiEQeIB+TcwbINZJIokAud/M7LqNNnTE4oaYm2bnriayu\nkhDIYQylZIbzke5Y1YRVIGvKhwKBgQDbC5tW6m7w0vAgRodGAL1x+U8qRvjsw3pg\nXwqyBOyapXoTvcw2MuTNS4ADGpq1eGBjBsehb5XvW14d7WqHfcDPYXq3IE9vB1Ls\nATp+0D9/IVt13LfvGBIaD0AH/zr1N1IdYZRMtOuNIQMMl+B8qL+pnPmE4Ct0RPPs\nTirotoTDnQKBgQDQNrvL9fDFxUU7qPokg5yuznk9DChvjzqtoDiW8FOb6L2Z3+j8\nTTKRtdPqHRwH/e4qtjE7mAMlAia5xPkaFFPVt+Z5cu4JBAi0z9qkpYdgQxv6l+qL\nRXhWMPipuxSZHpShHZPnr6V6y6a5bJ+jAk7TaE/Qw9OM37Nj3Jhs99xcUwKBgCmf\nMfw4/a2rF0+6txeZKmZOzjklVUV/+2/2f0zGXMMh8Glx5iziTNGpqABu/LjAz+fh\nMOu/DUl3HhInu9dVEN8XEb9cV1usk5gev6O7JGWezAdAUn8PHtluzmb2m5he066b\njRdqRVwCytaIwXJOimTLXCpggkFMnODpFYQ0slONAoGBAIlolThayqWVasEdX0KR\ncmYV6h5KmT/trjhKpMlXz0bPvegZpF7XFbodKF+0eA1gV5MmnDEQy4epGhD0zRt3\noKyoM08zyFLqpKZqxl+5gTyqbEbzbT/tuo6rydad/01IbWlcRLaNpBfmsSCdEHO3\nTqeSqspjDoxfvQISvBAfHIzP\n-----END PRIVATE KEY-----\n";
$extractions['global']['storage_data']['scope'] = 'https://www.googleapis.com/auth/devstorage.full_control https://www.googleapis.com/auth/devstorage.read_only https://www.googleapis.com/auth/devstorage.read_write https://www.googleapis.com/auth/cloud-taskqueue';

$extractions['global']['google']['client_id'] = '1072155568501-rpcfah9qpkg9jro4c6c9sh62go6pm7oe.apps.googleusercontent.com';
$extractions['global']['google']['client_secret'] = 'PAx5fz386w0groUL8JFgdVuQ';
$extractions['global']['google']['developer_token'] = 'RPVuoNbWrFpSxE-UCabf9w';

$extractions['global']['google_sheet']['refresh_token'] = '1/5Ex-mkYPcC96B8ycBWnNj_L1rtH46eTjE83PRIA_GadGpv_zXi070QcTmrGapWGf';
$extractions['global']['google_sheet']['email_credential'] = 'analyticsuae@annalect.com';
$extractions['global']['google_sheet']['sheet_id'] = '15917ONHrgyYmU49s_wvpjTguPXj9Dqbk5bhKMBNyMis';

$extractions['global']['adwords']['today'] = date('Ymd'); // YYYYMMDD
$extractions['global']['adwords']['yesterday'] = date("Ymd",  strtotime(date("Ymd", strtotime($extractions['global']['adwords']['today'])) . " -1 day") );
$extractions['global']['adwords']['last_6months'] = date("Ymd",  strtotime(date("Ymd", strtotime($extractions['global']['adwords']['today'])) . " -6 month") );
$extractions['global']['adwords']['historic'] = '20170601';

$extractions['global']['dcm']['today'] = date('Y-m-d');; // 2018-01-24
$extractions['global']['dcm']['yesterday'] = date("Y-m-d",  strtotime(date("Y-m-d", strtotime($extractions['global']['dcm']['today'] )) . " -1 day") );
$extractions['global']['dcm']['last_6months'] = date("Y-m-d",  strtotime(date("Y-m-d", strtotime($extractions['global']['dcm']['today'] )) . " -6 month") );

$extractions['global']['date']['today'] = date('Y-m-d'); // 2018-01-24
$extractions['global']['date']['yesterday'] = date("Y-m-d",  strtotime(date("Y-m-d", strtotime($extractions['global']['date']['today'])) . " -1 day") );




//       _          _                                 _
//      / \      __| | __      __   ___    _ __    __| |  ___
//     / _ \    / _` | \ \ /\ / /  / _ \  | '__|  / _` | / __|
//    / ___ \  | (_| |  \ V  V /  | (_) | | |    | (_| | \__ \
//   /_/   \_\  \__,_|   \_/\_/    \___/  |_|     \__,_| |___/


$phd_accounts_sample = array(
    array('accountId' => '100-624-9592', 'accountName' => 'AFGRE'),
    array('accountId' => '324-299-8908', 'accountName' => 'Al Futtaim Automall'),
);

$phd_accounts_data_display = array(
    array('accountId' => '773-519-1439', 'accountName' => 'AL-Futtaim_AF Retail'),
    array('accountId' => '509-004-0875', 'accountName' => 'Al-Futtaim_Toyota'),
    array('accountId' => '546-411-9101', 'accountName' => 'Arla_Organic Milk'),
    array('accountId' => '129-862-3810', 'accountName' => 'Arla_Puck'),
    array('accountId' => '665-214-2129', 'accountName' => 'Dubai World Trade Center'),
    array('accountId' => '722-400-9115', 'accountName' => 'Ellington'),
    array('accountId' => '897-582-5908', 'accountName' => 'Ferrero_Kinder Bueno'),
    array('accountId' => '126-425-1112', 'accountName' => 'Ferrero_Kinder Chocolate'),
    array('accountId' => '425-440-5495', 'accountName' => 'Ferrero_Kinder Maxi'),
    array('accountId' => '194-670-3343', 'accountName' => 'Ferrero_Kinder Surprise'),
    array('accountId' => '608-490-5526', 'accountName' => 'Ferrero_Nutella'),
    array('accountId' => '527-337-6281', 'accountName' => 'Ferrero_Raffaello'),
    array('accountId' => '634-966-1002', 'accountName' => 'Ferrero_Rocher'),
    array('accountId' => '133-412-8922', 'accountName' => 'Ferrero_Tic Tac'),
    array('accountId' => '997-390-8523', 'accountName' => 'Louvre AD'),
    array('accountId' => '971-131-3369', 'accountName' => 'Mashreq Bank'),
    array('accountId' => '803-906-0402', 'accountName' => 'ME | Youtube | Lurpak'),
    array('accountId' => '749-598-5964', 'accountName' => 'Meraas'),
    array('accountId' => '937-939-8883', 'accountName' => 'Pizza Hut - Ops'),
    array('accountId' => '170-162-6261', 'accountName' => 'Porsche 2014 - Ops'),
    array('accountId' => '857-808-0369', 'accountName' => 'Unilever BBRL Gulf'),
    array('accountId' => '888-805-6406', 'accountName' => 'Unilever BBRL KSA'),
    array('accountId' => '555-049-0181', 'accountName' => 'Unilever Clear Gulf 2015'),
    array('accountId' => '237-755-1506', 'accountName' => 'Unilever Clear KSA 2015'),
    array('accountId' => '281-418-8112', 'accountName' => 'Unilever Close Up'),
    array('accountId' => '426-325-7510', 'accountName' => 'Unilever Close Up KSA 2015'),
    array('accountId' => '237-982-5971', 'accountName' => 'Unilever Comfort Softner'),
    array('accountId' => '905-557-9732', 'accountName' => 'Unilever Dove Shampoo'),
    array('accountId' => '163-709-2604', 'accountName' => 'Unilever Dove Shampoo KSA 2015'),
    array('accountId' => '938-028-6910', 'accountName' => 'Unilever Fair&Lovely'),
    array('accountId' => '190-100-3571', 'accountName' => 'Unilever Fair&Lovely KSA'),
    array('accountId' => '339-135-0983', 'accountName' => 'Unilever Jif'),
    array('accountId' => '144-241-9919', 'accountName' => 'Unilever Jif KSA'),
    array('accountId' => '597-213-0806', 'accountName' => 'Unilever Knorr Range'),
    array('accountId' => '734-054-9038', 'accountName' => 'Unilever Lifebuoy Hand Wash'),
    array('accountId' => '521-358-3438', 'accountName' => 'Unilever Lifebuoy Handwash KSA'),
    array('accountId' => '832-272-6151', 'accountName' => 'Unilever Lipton YLTB'),
    array('accountId' => '307-697-6473', 'accountName' => 'Unilever Lipton YLTB KSA'),
    array('accountId' => '764-604-6232', 'accountName' => 'Unilever Lux Shower Gel'),
    array('accountId' => '286-841-8359', 'accountName' => 'Unilever OMO'),
    array('accountId' => '552-042-4475', 'accountName' => 'Unilever OMO KSA 2015'),
    array('accountId' => '573-183-9657', 'accountName' => 'Unilever Rexona Range'),
    array('accountId' => '237-180-4540', 'accountName' => 'Unilever Sunsilk Shampoo'),
    array('accountId' => '605-669-8140', 'accountName' => 'Unilever SunSilk Shampoo KSA'),
    array('accountId' => '426-412-6612', 'accountName' => 'Unilever Tresemme'),
    array('accountId' => '401-576-4851', 'accountName' => 'Unilever Tresemme KSA 2015'),
    array('accountId' => '746-647-6557', 'accountName' => 'Unilever Vaseline Body Lotion Gulf'),
    array('accountId' => '271-569-9953', 'accountName' => 'Unilever Vaseline Body Lotion KSA'),
    array('accountId' => '270-735-2960', 'accountName' => 'Unilever_Dove Hand & Body Gulf'),
);

$phd_accounts_data_search = array(
    array('accountId' => '100-624-9592', 'accountName' => 'AFGRE'),
    array('accountId' => '324-299-8908', 'accountName' => 'Al Futtaim Automall'),
    array('accountId' => '220-611-1827', 'accountName' => 'Al Futtaim - Bebe Arabia'),
    array('accountId' => '920-523-6542', 'accountName' => 'Doha Festival City'),
    array('accountId' => '481-866-0549', 'accountName' => 'Dubai Festival City'),
    array('accountId' => '839-622-2872', 'accountName' => 'Fast fit Auto Centre'),
    array('accountId' => '683-172-7825', 'accountName' => 'Hero Bikes MENA'),
    array('accountId' => '420-672-1384', 'accountName' => 'AF - Hertz'),
    array('accountId' => '502-100-0963', 'accountName' => 'Honda'),
    array('accountId' => '810-597-9110', 'accountName' => 'Lexus Tactical Adwords'),
    array('accountId' => '368-034-0014', 'accountName' => 'Toyota 86 - Always On'),
    array('accountId' => '919-742-5302', 'accountName' => 'Toyota AFS (After Sales) - Always On/Tactical'),
    array('accountId' => '761-958-7373', 'accountName' => 'Toyota Avalon - Always On'),
    array('accountId' => '909-628-9253', 'accountName' => 'Toyota Avanza - Always On'),
    array('accountId' => '903-305-1206', 'accountName' => 'Toyota Brand - Always On'),
    array('accountId' => '680-805-8128', 'accountName' => 'Toyota Camry - Always On'),
    array('accountId' => '154-604-5289', 'accountName' => 'Toyota Corolla - Always On'),
    array('accountId' => '962-491-5975', 'accountName' => 'Toyota CPO - Always On/Tactical'),
    array('accountId' => '978-941-9632', 'accountName' => 'Toyota FJ Cruiser - Always On'),
    array('accountId' => '341-558-4538', 'accountName' => 'Toyota Fortuner - Always On'),
    array('accountId' => '579-425-6785', 'accountName' => 'Toyota Generic - Always On'),
    array('accountId' => '570-381-5663', 'accountName' => 'Toyota Hilux - Always On'),
    array('accountId' => '164-616-9438', 'accountName' => 'Toyota Innova - Always On'),
    array('accountId' => '534-861-9065', 'accountName' => 'Toyota Land Cruiser - Always On'),
    array('accountId' => '797-478-3971', 'accountName' => 'Toyota Land Cruiser Prado - Always On'),
    array('accountId' => '458-231-0381', 'accountName' => 'Toyota Previa - Always On'),
    array('accountId' => '542-091-3140', 'accountName' => 'Toyota Prius - Always On'),
    array('accountId' => '399-031-6051', 'accountName' => 'Toyota Rav4 - Always On'),
    array('accountId' => '881-338-6730', 'accountName' => 'Toyota SME - Always On'),
    array('accountId' => '776-505-9753', 'accountName' => 'Toyota Tactical/Launch'),
    array('accountId' => '626-128-4359', 'accountName' => 'Toyota Yaris Sedan - Always On'),
    array('accountId' => '353-675-5222', 'accountName' => 'Volvo'),
    array('accountId' => '525-453-8889', 'accountName' => 'RSH ME LLC'),
    array('accountId' => '301-652-0667', 'accountName' => 'ME | Search | ARLA | Lurpak'),
    array('accountId' => '170-343-8214', 'accountName' => 'ME | Search | ARLA | Puck'),
    array('accountId' => '343-188-3433', 'accountName' => 'ME | Search | Lurpak'),
    array('accountId' => '686-314-2094', 'accountName' => 'ME | Search | Puck'),
    array('accountId' => '547-300-3023', 'accountName' => 'AL Nabooda (Audi) - Display'),
    array('accountId' => '233-236-5238', 'accountName' => 'Audi Grand Account - Gulf'),
    array('accountId' => '307-664-0503', 'accountName' => 'Audi Grand Account - KSA'),
    array('accountId' => '225-748-6074', 'accountName' => 'Audi GSP Account'),
    array('accountId' => '955-408-3698', 'accountName' => 'Audi Summer Search'),
    array('accountId' => '930-343-8072', 'accountName' => 'Audi Tactical Display (PHD)'),
    array('accountId' => '743-940-1870', 'accountName' => 'Bentley'),
    array('accountId' => '966-434-0099', 'accountName' => 'DWTC Authority'),
    array('accountId' => '794-319-2836', 'accountName' => 'DWTC Dubai Sports World'),
    array('accountId' => '971-105-3334', 'accountName' => 'DWTC Hospitality'),
    array('accountId' => '330-778-1054', 'accountName' => 'DWTC One Central'),
    array('accountId' => '261-786-0622', 'accountName' => 'DWTC Seven Sands'),
    array('accountId' => '172-154-0032', 'accountName' => 'DWTC The Apartments'),
    array('accountId' => '640-276-3743', 'accountName' => 'DWTC The Majlis'),
    array('accountId' => '530-646-4585', 'accountName' => 'DWTC Venue'),
    array('accountId' => '719-671-9404', 'accountName' => 'DWTC Wedding'),
    array('accountId' => '189-642-3292', 'accountName' => 'Eight Creative Technology FZ LLC'),
    array('accountId' => '395-695-0543', 'accountName' => 'Ellington Properties'),
    array('accountId' => '920-408-0886', 'accountName' => 'Hauwei Middle East'),
    array('accountId' => '716-451-3194', 'accountName' => 'Lacnor'),
    array('accountId' => '226-060-1201', 'accountName' => 'Louvre Abu Dhabi'),
    array('accountId' => '297-130-1747', 'accountName' => 'Louvre Abu Dhabi ITL'),
    array('accountId' => '682-978-6286', 'accountName' => 'Mashreq NEO'),
    array('accountId' => '798-057-0468', 'accountName' => 'Meraas - Bluewaters'),
    array('accountId' => '840-535-4545', 'accountName' => 'Meraas - Bvlgari Residences'),
    array('accountId' => '271-017-5890', 'accountName' => 'Meraas - Hub Zero'),
    array('accountId' => '744-378-3299', 'accountName' => 'Meraas - Mattel Play Town'),
    array('accountId' => '647-333-0970', 'accountName' => 'Meraas - Nikki Beach'),
    array('accountId' => '688-991-0154', 'accountName' => 'Meraas - Roxy Cinemas'),
    array('accountId' => '570-928-9127', 'accountName' => 'Meraas - Sizzling summer'),
    array('accountId' => '477-547-4196', 'accountName' => 'Meraas - The Green Planet'),
    array('accountId' => '643-643-6751', 'accountName' => 'Healthpoint'),
    array('accountId' => '467-509-2801', 'accountName' => 'ICLDC'),
    array('accountId' => '870-466-1436', 'accountName' => 'Mubadala Search'),
    array('accountId' => '454-369-6243', 'accountName' => 'NYU Abu Dhabi'),
    array('accountId' => '766-824-0769', 'accountName' => 'Pizza Hut (Bahrain)'),
    array('accountId' => '310-904-1336', 'accountName' => 'Pizza Hut (Egypt)'),
    array('accountId' => '901-723-8138', 'accountName' => 'Pizza Hut (Jeddah)'),
    array('accountId' => '454-052-2655', 'accountName' => 'Pizza Hut (Jordan)'),
    array('accountId' => '208-505-9573', 'accountName' => 'Pizza Hut (KSA excluding Jeddah)'),
    array('accountId' => '177-508-3536', 'accountName' => 'Pizza Hut (Kuwait)'),
    array('accountId' => '420-967-1271', 'accountName' => 'Pizza Hut (Oman)'),
    array('accountId' => '179-595-8883', 'accountName' => 'Pizza Hut (Qatar)'),
    array('accountId' => '616-893-1969', 'accountName' => 'Pizza Hut (UAE)'),
    array('accountId' => '788-070-7667', 'accountName' => 'Porsche 911'),
    array('accountId' => '680-102-8714', 'accountName' => 'Porsche Approved'),
    array('accountId' => '695-903-3630', 'accountName' => 'Porsche Boxster'),
    array('accountId' => '835-692-4622', 'accountName' => 'Porsche Brand'),
    array('accountId' => '429-909-7759', 'accountName' => 'Porsche Cayenne'),
    array('accountId' => '221-482-4916', 'accountName' => 'Porsche Cayman'),
    array('accountId' => '779-945-5926', 'accountName' => 'Porsche Generic'),
    array('accountId' => '531-289-9102', 'accountName' => 'Porsche Macan'),
    array('accountId' => '119-614-1867', 'accountName' => 'Porsche Panamera'),
    array('accountId' => '730-258-1922', 'accountName' => 'Porsche Service'),
    array('accountId' => '392-073-3037', 'accountName' => 'RTA'),
    array('accountId' => '412-227-0329', 'accountName' => 'TDIC - Gmail'),
    array('accountId' => '530-514-1423', 'accountName' => 'TDIC - Islands - Sch'),
    array('accountId' => '487-108-0969', 'accountName' => 'NAME_Unilever_Personal Care_Deodorants_Axe_Google'),
    array('accountId' => '414-573-5320', 'accountName' => 'NAME_Unilever_Careers_Google'),
    array('accountId' => '164-918-8104', 'accountName' => 'Name_Unilever_Home Care_Home Cleaning_Cleanipedia_Google_GULF'),
    array('accountId' => '808-225-9752', 'accountName' => 'Name_Unilever_Home Care_Home Cleaning_Cleanipedia_Google_KSA'),
    array('accountId' => '160-954-2223', 'accountName' => 'NAME_Unilever_Personal Care_Hair Care_Clear_Google'),
    array('accountId' => '726-228-9194', 'accountName' => 'NAME_Binzagr_Personal Care_Hair Care_Clear_Google'),
    array('accountId' => '665-472-5077', 'accountName' => 'Name_Binzagr_Personal Care_Oral_Close up Toothpaste_Google'),
    array('accountId' => '343-909-0861', 'accountName' => 'Name_Severn_Personal Care_Oral_Close up Toothpaste_Google'),
    array('accountId' => '430-705-5779', 'accountName' => 'NAME_Unilever_Personal Care_Hair Care_Dove_Google'),
    array('accountId' => '948-246-2261', 'accountName' => 'NAME_Unilever_Personal Care_Skin Cleansing_Dove_Google'),
    array('accountId' => '672-626-9931', 'accountName' => 'NAME_Binzagr_Personal Care_Hair Care_Dove_Google'),
    array('accountId' => '119-970-1129', 'accountName' => 'NAME_Binzagr_Personal Care_Skin Cleansing_Dove_Google'),
    array('accountId' => '368-052-8909', 'accountName' => 'Forsaty Challenge'),
    array('accountId' => '960-957-6720', 'accountName' => 'NAME_Unilever_Food_Knorr_Google'),
    array('accountId' => '155-688-4955', 'accountName' => 'NAME_Unilever_Personal Care_Skin Cleanse_Lifebuoy_Google'),
    array('accountId' => '802-709-7287', 'accountName' => 'NAME_Binzagr_Personal Care_Skin Cleanse_Lifebuoy_Google'),
    array('accountId' => '134-957-4163', 'accountName' => 'NAME_Unilever_Refreshment_Tea_Lipton_Google_GULF'),
    array('accountId' => '435-384-4972', 'accountName' => 'NAME_Unilever_Refreshment_Tea_Lipton_Google_KSA'),
    array('accountId' => '873-562-7453', 'accountName' => 'NAME_Severn_Personal Care_Skin Care_LUX_Google'),
    array('accountId' => '477-911-7277', 'accountName' => 'NAME_Binzagr_Personal Care_Skin Care_LUX_Google'),
    array('accountId' => '270-512-9922', 'accountName' => 'NAME_Unilever_Home Care_Fabric Cleaning_OMO_Google'),
    array('accountId' => '904-512-0232', 'accountName' => 'NAME_Severn_Home Care_Fabric Cleaning_OMO_Google'),
    array('accountId' => '817-460-2004', 'accountName' => 'NAME_Binzagr_Personal Care_Hair Care_Sunsilk_Google'),
    array('accountId' => '862-436-6761', 'accountName' => 'NAME_Severn_Personal Care_Hair Care_Sunsilk_Google'),
    array('accountId' => '292-952-1910', 'accountName' => 'NAME_Unilever_Personal Care_Hair Care_TRESemme_Google'),
    array('accountId' => '687-913-4852', 'accountName' => 'NAME_Unilever_Personal Care_Skin Care_Vaseline_Google'),
    array('accountId' => '921-911-5625', 'accountName' => 'NAME_Binzagr_Personal Care_Skin Care_Vaseline_Google'),
    array('accountId' => '390-321-1338', 'accountName' => 'NAME_Unilever_Personal Care_Oral Care_Zendium_Google'),
    array('accountId' => '502-447-5225', 'accountName' => 'NAME_Binzagr_Personal Care_Oral Care_Zendium_Google'),
    array('accountId' => '961-152-0813', 'accountName' => 'St Ives - Unilever Display'),
    array('accountId' => '897-961-3473', 'accountName' => '[VW]_[AE]_[New Car]_[PPC]_[Google]_Awareness'),
    array('accountId' => '228-182-3914', 'accountName' => '[VW]_[AE]_[New Car]_[PPC]_[Google]_Competitor'),
    array('accountId' => '821-987-2679', 'accountName' => '[VW]_[AE]_[New Car]_[PPC]_[Google]_Consideration'),
    array('accountId' => '565-619-1151', 'accountName' => '[VW]_[AE]_[New Car]_[PPC]_[Google]_Evalution'),

);




// Project [PHD - Ad]
/*
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'phd_ad',
    'file_name' => "adwords_historical_phd_ad_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/CuF84U4cVK1aa9paWHCk1MJniYGi1nAvPBPahSYZ_Ps',
    'accountsData' => $phd_accounts_sample,
    'report' => 'AD_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CreativeFinalUrls,AdGroupName,AverageCpv,CampaignName,Clicks,Cost,Ctr,Headline,Impressions,VideoQuartile100Rate,VideoViews',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));
*/

/*
// Project [PHD - Campaign cmp Jun-Jul]
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'phd_cmp1',
    'file_name' => "adwords_historical_phd_cmp1_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/CuF84U4cVK1aa9paWHCk1MJniYGi1nAvPBPahSYZ_Ps',
    'accountsData' => $phd_accounts_data_display,
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,ImpressionReach,AverageFrequency',
    'startDate' => '20170601',
    'endDate' => '20170801'
));


// Project [PHD - Campaign cmp Ago-Sep]
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'phd_cmp2',
    'file_name' => "adwords_historical_phd_cmp2_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/CuF84U4cVK1aa9paWHCk1MJniYGi1nAvPBPahSYZ_Ps',
    'accountsData' => $phd_accounts_data_display,
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,ImpressionReach,AverageFrequency',
    'startDate' => '20170802',
    'endDate' => '20171001'
));


// Project [PHD - Campaign cmp Oct-Nov]
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'phd_cmp3',
    'file_name' => "adwords_historical_phd_cmp3_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/CuF84U4cVK1aa9paWHCk1MJniYGi1nAvPBPahSYZ_Ps',
    'accountsData' => $phd_accounts_data_display,
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,ImpressionReach,AverageFrequency',
    'startDate' => '20171002',
    'endDate' => '20171201'
));


// Project [PHD - Campaign cmp Dec-Ene]
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'phd_cmp4',
    'file_name' => "adwords_historical_phd_cmp4_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/CuF84U4cVK1aa9paWHCk1MJniYGi1nAvPBPahSYZ_Ps',
    'accountsData' => $phd_accounts_data_display,
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,ImpressionReach,AverageFrequency',
    'startDate' => '20171202',
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [PHD - Campaign cmp Feb-YESTERDAY]
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'phd_cmp5',
    'file_name' => "adwords_historical_phd_cmp5_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/CuF84U4cVK1aa9paWHCk1MJniYGi1nAvPBPahSYZ_Ps',
    'accountsData' => $phd_accounts_data_display,
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,ImpressionReach,AverageFrequency',
    'startDate' => '20171202',
    'endDate' => $extractions['global']['adwords']['yesterday']
));


// Project [PHD - Search 1]
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'phd_search_campaign_1',
    'file_name' => "adwords_historical_phd_search_campaign_1_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/CuF84U4cVK1aa9paWHCk1MJniYGi1nAvPBPahSYZ_Ps',
    'accountsData' => $phd_accounts_data_search,
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,AdNetworkType1,CampaignName,ClickType,Device,Clicks,Cost,Ctr,Impressions,Conversions',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [PHD - Search 2]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'phd_search_campaign_2',
    'file_name' => "adwords_historical_phd_search_campaign_2_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/CuF84U4cVK1aa9paWHCk1MJniYGi1nAvPBPahSYZ_Ps',
    'accountsData' => $phd_accounts_data_search,
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,AdNetworkType1,CampaignName,AverageTimeOnSite,BounceRate',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));
*/





/*

// AAC
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'aac',
    'file_name' => "adwords_historical_aaac_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'annalectautomation@gmail.com',
    'refresh_token' => '1/Fb-kcD0UFz63kdX2fg9tTPN3QR0izXPm0Tdkvvv_KOw',
    'accounts' => array('156-469-0702','575-470-1972','606-092-7999','204-292-7012'),
    'report' => 'KEYWORDS_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,Criteria,KeywordMatchType,ClickType,Clicks,Impressions,Cost,Conversions',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [Infiniti]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'infiniti',
    'file_name' => "adwords_historical_infiniti_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'annalectautomation@gmail.com',
    'refresh_token' => '1/Fb-kcD0UFz63kdX2fg9tTPN3QR0izXPm0Tdkvvv_KOw',
    'accounts' => array('427-424-6315', '558-732-8768', '164-808-1409', '690-041-1221', '867-302-1843', '558-732-8768', '296-116-3862', '838-443-5660', '756-558-3856', '695-952-2231', '421-833-0827', '145-969-9760', '707-135-3207', '444-106-1264', '312-797-8811', '444-106-1264', '388-771-9605', '553-141-3280'),
    'report' => 'KEYWORDS_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,Criteria,KeywordMatchType,ClickType,Clicks,Impressions,Cost,Conversions',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [Nissan]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'nissan',
    'file_name' => "adwords_historical_nissan_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'annalectautomation@gmail.com',
    'refresh_token' => '1/Fb-kcD0UFz63kdX2fg9tTPN3QR0izXPm0Tdkvvv_KOw',
    'accounts' => array('520-736-9948', '666-680-9011', '328-503-0348', '406-860-1849', '535-372-6558', '404-064-4105', '290-524-8374', '625-250-1403', '389-428-7495'),
    'report' => 'KEYWORDS_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,Criteria,KeywordMatchType,ClickType,Clicks,Impressions,Cost,Conversions',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project OMD - Ad]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'omd_ad',
    'file_name' => "adwords_historical_omd_ad_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'refresh_token' => '1/v-rDuUwM3aZ9XP-Ewo0oa9IPglJ1YofIyGdSbcOdbQk',
    'accounts' => array('780-635-5944', '162-952-1091', '111-215-5758', '745-321-0048', '180-145-7948', '219-875-3614', '115-791-8453', '867-302-1843', '520-736-9948', '666-680-9011', '838-443-5660', '661-235-1106', '682-672-4117', '434-678-1112', '156-469-0702', '694-030-1351', '800-931-6351', '684-449-5733', '727-204-6923', '495-801-9334', '178-870-1816', '655-720-0785', '690-041-1221', '695-952-2231', '253-214-5451', '516-330-0303', '145-969-9760', '260-997-7972', '576-978-8881', '189-557-4068', '678-464-4724', '160-634-1253', '444-106-1264', '932-389-4720', '781-094-1606', '328-503-0348', '604-396-7359', '846-573-8853', '261-913-8410', '518-011-7932', '663-370-8085', '183-194-6380', '213-554-6736', '575-470-1972', '244-067-0150', '482-187-8768', '388-771-9605', '535-372-6558', '719-629-9377', '917-036-3837', '880-766-7381', '386-512-6357', '333-027-8551', '406-860-1849', '830-836-7777', '137-613-4118', '429-021-2006', '841-444-7450', '910-403-5521', '290-524-8374', '642-636-7032', '594-301-8559', '405-951-6488', '425-282-5237', '806-807-9187', '404-064-4105', '344-862-9189', '699-547-2573', '117-910-6175', '606-092-7999', '899-428-3852', '633-686-7828', '931-357-7878', '726-663-1132', '999-786-1627', '996-943-7684', '986-367-8208', '985-170-2809', '984-621-2979', '983-359-1701', '981-754-2118', '979-382-4676', '978-595-2904', '977-557-5680', '976-323-2234', '974-833-3937', '972-358-1838', '965-527-3090', '963-656-9874', '957-782-9610', '957-390-2231', '953-849-8080', '951-646-0491', '950-353-6423', '934-623-4286', '934-120-3021', '933-021-2170', '932-190-5008', '929-627-8429', '928-476-0827', '928-442-7078', '927-223-9004', '925-724-4889', '917-630-8870', '909-755-7716', '909-064-2740', '908-634-2539', '907-954-2829', '907-300-9885', '905-214-8963', '900-428-3126', '899-926-3502', '895-674-3729', '895-170-2989', '891-128-6718', '889-452-8778', '888-579-9190', '885-937-0762', '885-061-7354', '884-983-1171', '884-246-8243', '881-506-2621', '880-032-9574', '877-413-7306', '872-511-8029', '869-991-2110', '864-868-5416', '860-881-3085', '858-646-5232', '855-527-7918', '852-344-6306', '850-614-8538', '848-320-3074', '842-227-9981', '840-501-0723', '839-605-3980', '835-791-0961', '829-951-4021', '828-691-7386', '807-288-2429', '806-510-4334', '804-784-1517', '803-830-1926', '802-526-6584', '800-830-2410', '796-889-8074', '796-131-7321', '789-731-6258', '785-654-5002', '781-104-3785', '776-961-4536', '774-856-0407', '773-392-7583', '768-158-3053', '765-155-1395', '764-524-0476', '760-220-3797', '758-671-4978', '757-220-4974', '756-558-3856', '756-511-3412', '756-171-1476', '753-593-3672', '753-041-4714', '751-833-0565', '751-176-1472', '750-448-9002', '749-320-5929', '741-443-6523', '740-899-6887', '739-899-1527', '728-359-2557', '724-448-6729', '722-755-8864', '720-616-7872', '718-237-8957', '718-031-7342', '717-847-2922', '711-343-1474', '710-129-0820', '707-167-9687', '707-135-3207', '703-464-9619', '700-472-2206', '699-671-4282', '686-066-4729', '680-265-3198', '680-144-0394', '676-465-7129', '672-876-9725', '672-751-2978', '671-865-1583', '668-788-2325', '664-369-3777', '660-249-0240', '653-195-5225', '646-634-0784', '632-781-5622', '627-166-0725', '626-258-9709', '625-250-1403', '621-502-7480', '616-529-1024', '613-630-0864', '601-577-1034', '600-317-8081', '598-518-2539', '598-133-0295', '593-331-8323', '592-133-4427', '588-377-4889', '584-153-2470', '583-821-4821', '574-286-7525', '573-885-7620', '562-685-7637', '562-599-5076', '558-732-8768', '553-141-3280', '548-613-9038', '547-307-0576', '542-167-6611', '538-138-9481', '531-765-8053', '527-721-1106', '526-543-0625', '518-428-8472', '515-467-8782', '514-400-3808', '508-223-7259', '506-770-3685', '506-501-1174', '500-378-7427', '497-781-7714', '489-547-2730', '487-855-7772', '485-605-7861', '484-713-9386', '481-278-7176', '478-969-9672', '478-777-3561', '474-498-9083', '474-007-6133', '466-937-6486', '460-153-1886', '455-083-3729', '454-693-9325', '452-540-5965', '451-489-4510', '442-775-9551', '435-289-3929', '434-745-1614', '434-198-0639', '431-634-3572', '430-234-3320', '427-424-6315', '425-013-9034', '424-632-5723', '421-833-0827', '421-267-4929', '416-509-4933', '415-998-8123', '414-742-8225', '413-862-6629', '413-655-1902', '413-331-0826', '405-698-9972', '403-958-4497', '401-512-8427', '398-119-3823', '396-412-8653', '395-268-8472', '392-323-1025', '391-321-0227', '389-428-7495', '381-344-0085', '381-253-8576', '379-400-8629', '378-907-8530', '378-135-5304', '378-042-7571', '375-733-0472', '375-213-4940', '373-455-2329', '369-937-3177', '368-966-0024', '368-898-3994', '368-727-6669', '356-739-8806', '356-594-7426', '342-818-4382', '342-507-7527', '341-417-9510', '340-087-9008', '338-822-7589', '336-931-5485', '336-152-7340', '330-875-6427', '329-466-0837', '323-629-7725', '322-805-5252', '322-468-0035', '322-385-5304', '321-669-4553', '320-979-9435', '319-781-9969', '315-963-0823', '314-752-3059', '313-304-7178', '312-797-8811', '309-362-6621', '297-754-9285', '296-991-1927', '296-116-3862', '292-521-3327', '287-364-8174', '280-774-5976', '274-509-3041', '273-046-2423', '266-301-8925', '264-521-2323', '261-762-6373', '258-725-4878', '255-446-8521', '251-556-1478', '251-142-3688', '245-955-6973', '241-637-7376', '239-281-9088', '237-648-5880', '235-936-0178', '235-137-1827', '235-043-0837', '231-709-7881', '229-415-3190', '227-351-7600', '225-943-2863', '225-915-5629', '225-528-9570', '223-380-5121', '215-611-0343', '214-750-8125', '207-500-6878', '204-292-7012', '204-069-9727', '203-109-9021', '201-045-0724', '200-631-2176', '199-526-0678', '198-702-2360', '192-988-9023', '185-575-4885', '184-043-2679', '183-969-0314', '182-989-1621', '182-658-8995', '179-653-2326', '173-621-3385', '173-408-8529', '170-902-5708', '170-706-2380', '167-988-9616', '166-254-3425', '164-808-1409', '161-924-6271', '160-692-0029', '156-449-4188', '153-799-6576', '151-413-2904', '138-857-4025', '138-815-0221', '138-026-9837', '135-986-4041', '135-496-2827', '132-816-6327', '123-811-5774', '123-437-4321', '119-039-3025', '118-692-0962', '117-659-8877', '117-111-2974', '116-167-8846', '112-690-3425', '112-501-2988', '111-644-2872', '106-024-1110', '104-224-6026', '102-960-5140', '102-725-9525', '100-664-8978', '462-247-5479', '832-081-6061', '385-378-8385', '153-759-9516', '557-798-2924', '663-700-4828', '218-306-8481', '880-439-6234', '885-695-1363', '177-600-7777', '591-650-6832', '838-666-2067', '239-434-9322', '369-918-9710', '967-825-7970', '496-628-4924', '890-301-5775', '335-636-2214', '193-692-4285', '945-964-0620', '183-023-0430', '846-646-2453', '705-832-4806', '295-594-5055', '401-441-3474', '161-882-1774', '169-416-4018', '259-418-0567', '340-319-3304', '344-841-3865', '274-751-4351', '536-670-2083', '995-901-7117', '952-970-4083', '926-253-6124', '855-687-0485', '774-537-4330', '766-257-0521', '738-598-5278', '732-597-2231', '624-247-7208', '618-997-2208', '602-387-3923', '588-492-7996', '531-727-2179', '477-058-0716', '426-445-4738', '367-575-4312', '345-732-4021', '267-899-7423', '225-874-2208', '209-262-7359', '169-439-3832', '156-883-1076', '106-259-5780', '105-693-4772', '946-461-1508', '666-436-7590', '124-076-8529', '933-499-8956', '826-940-9162', '802-191-6503', '547-588-0034', '659-350-3481', '493-941-8950', '472-133-6818', '444-518-8301', '272-756-1652', '200-740-5044', '159-216-5921', '126-155-4699', '619-597-8271', '253-530-5462', '735-338-1285', '903-016-4232', '769-217-8555', '592-750-6240', '404-009-3251', '242-546-5681', '629-082-1618', '678-257-3414', '272-411-2228', '353-311-3534', '314-254-6873', '861-291-2455', '126-322-3361', '719-344-0753', '712-698-8987', '450-107-0904', '594-291-4329', '587-080-9489', '426-828-1085', '454-665-9612', '237-330-3465', '923-782-1605', '335-879-2634', '331-840-2227', '462-071-4508', '348-033-6783'),
    'report' => 'AD_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CreativeFinalUrls,AdGroupName,AverageCpv,CampaignName,Clicks,Cost,Ctr,Headline,Impressions,VideoQuartile100Rate,VideoViews',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [OMD - Campaign]
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'omd_campaign',
    'file_name' => "adwords_historical_omd_campaign_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'refresh_token' => '1/v-rDuUwM3aZ9XP-Ewo0oa9IPglJ1YofIyGdSbcOdbQk',
    'accounts' => array('780-635-5944', '162-952-1091', '111-215-5758', '745-321-0048', '180-145-7948', '219-875-3614', '115-791-8453', '867-302-1843', '520-736-9948', '666-680-9011', '838-443-5660', '661-235-1106', '682-672-4117', '434-678-1112', '156-469-0702', '694-030-1351', '800-931-6351', '684-449-5733', '727-204-6923', '495-801-9334', '178-870-1816', '655-720-0785', '690-041-1221', '695-952-2231', '253-214-5451', '516-330-0303', '145-969-9760', '260-997-7972', '576-978-8881', '189-557-4068', '678-464-4724', '160-634-1253', '444-106-1264', '932-389-4720', '781-094-1606', '328-503-0348', '604-396-7359', '846-573-8853', '261-913-8410', '518-011-7932', '663-370-8085', '183-194-6380', '213-554-6736', '575-470-1972', '244-067-0150', '482-187-8768', '388-771-9605', '535-372-6558', '719-629-9377', '917-036-3837', '880-766-7381', '386-512-6357', '333-027-8551', '406-860-1849', '830-836-7777', '137-613-4118', '429-021-2006', '841-444-7450', '910-403-5521', '290-524-8374', '642-636-7032', '594-301-8559', '405-951-6488', '425-282-5237', '806-807-9187', '404-064-4105', '344-862-9189', '699-547-2573', '117-910-6175', '606-092-7999', '899-428-3852', '633-686-7828', '931-357-7878', '726-663-1132', '999-786-1627', '996-943-7684', '986-367-8208', '985-170-2809', '984-621-2979', '983-359-1701', '981-754-2118', '979-382-4676', '978-595-2904', '977-557-5680', '976-323-2234', '974-833-3937', '972-358-1838', '965-527-3090', '963-656-9874', '957-782-9610', '957-390-2231', '953-849-8080', '951-646-0491', '950-353-6423', '934-623-4286', '934-120-3021', '933-021-2170', '932-190-5008', '929-627-8429', '928-476-0827', '928-442-7078', '927-223-9004', '925-724-4889', '917-630-8870', '909-755-7716', '909-064-2740', '908-634-2539', '907-954-2829', '907-300-9885', '905-214-8963', '900-428-3126', '899-926-3502', '895-674-3729', '895-170-2989', '891-128-6718', '889-452-8778', '888-579-9190', '885-937-0762', '885-061-7354', '884-983-1171', '884-246-8243', '881-506-2621', '880-032-9574', '877-413-7306', '872-511-8029', '869-991-2110', '864-868-5416', '860-881-3085', '858-646-5232', '855-527-7918', '852-344-6306', '850-614-8538', '848-320-3074', '842-227-9981', '840-501-0723', '839-605-3980', '835-791-0961', '829-951-4021', '828-691-7386', '807-288-2429', '806-510-4334', '804-784-1517', '803-830-1926', '802-526-6584', '800-830-2410', '796-889-8074', '796-131-7321', '789-731-6258', '785-654-5002', '781-104-3785', '776-961-4536', '774-856-0407', '773-392-7583', '768-158-3053', '765-155-1395', '764-524-0476', '760-220-3797', '758-671-4978', '757-220-4974', '756-558-3856', '756-511-3412', '756-171-1476', '753-593-3672', '753-041-4714', '751-833-0565', '751-176-1472', '750-448-9002', '749-320-5929', '741-443-6523', '740-899-6887', '739-899-1527', '728-359-2557', '724-448-6729', '722-755-8864', '720-616-7872', '718-237-8957', '718-031-7342', '717-847-2922', '711-343-1474', '710-129-0820', '707-167-9687', '707-135-3207', '703-464-9619', '700-472-2206', '699-671-4282', '686-066-4729', '680-265-3198', '680-144-0394', '676-465-7129', '672-876-9725', '672-751-2978', '671-865-1583', '668-788-2325', '664-369-3777', '660-249-0240', '653-195-5225', '646-634-0784', '632-781-5622', '627-166-0725', '626-258-9709', '625-250-1403', '621-502-7480', '616-529-1024', '613-630-0864', '601-577-1034', '600-317-8081', '598-518-2539', '598-133-0295', '593-331-8323', '592-133-4427', '588-377-4889', '584-153-2470', '583-821-4821', '574-286-7525', '573-885-7620', '562-685-7637', '562-599-5076', '558-732-8768', '553-141-3280', '548-613-9038', '547-307-0576', '542-167-6611', '538-138-9481', '531-765-8053', '527-721-1106', '526-543-0625', '518-428-8472', '515-467-8782', '514-400-3808', '508-223-7259', '506-770-3685', '506-501-1174', '500-378-7427', '497-781-7714', '489-547-2730', '487-855-7772', '485-605-7861', '484-713-9386', '481-278-7176', '478-969-9672', '478-777-3561', '474-498-9083', '474-007-6133', '466-937-6486', '460-153-1886', '455-083-3729', '454-693-9325', '452-540-5965', '451-489-4510', '442-775-9551', '435-289-3929', '434-745-1614', '434-198-0639', '431-634-3572', '430-234-3320', '427-424-6315', '425-013-9034', '424-632-5723', '421-833-0827', '421-267-4929', '416-509-4933', '415-998-8123', '414-742-8225', '413-862-6629', '413-655-1902', '413-331-0826', '405-698-9972', '403-958-4497', '401-512-8427', '398-119-3823', '396-412-8653', '395-268-8472', '392-323-1025', '391-321-0227', '389-428-7495', '381-344-0085', '381-253-8576', '379-400-8629', '378-907-8530', '378-135-5304', '378-042-7571', '375-733-0472', '375-213-4940', '373-455-2329', '369-937-3177', '368-966-0024', '368-898-3994', '368-727-6669', '356-739-8806', '356-594-7426', '342-818-4382', '342-507-7527', '341-417-9510', '340-087-9008', '338-822-7589', '336-931-5485', '336-152-7340', '330-875-6427', '329-466-0837', '323-629-7725', '322-805-5252', '322-468-0035', '322-385-5304', '321-669-4553', '320-979-9435', '319-781-9969', '315-963-0823', '314-752-3059', '313-304-7178', '312-797-8811', '309-362-6621', '297-754-9285', '296-991-1927', '296-116-3862', '292-521-3327', '287-364-8174', '280-774-5976', '274-509-3041', '273-046-2423', '266-301-8925', '264-521-2323', '261-762-6373', '258-725-4878', '255-446-8521', '251-556-1478', '251-142-3688', '245-955-6973', '241-637-7376', '239-281-9088', '237-648-5880', '235-936-0178', '235-137-1827', '235-043-0837', '231-709-7881', '229-415-3190', '227-351-7600', '225-943-2863', '225-915-5629', '225-528-9570', '223-380-5121', '215-611-0343', '214-750-8125', '207-500-6878', '204-292-7012', '204-069-9727', '203-109-9021', '201-045-0724', '200-631-2176', '199-526-0678', '198-702-2360', '192-988-9023', '185-575-4885', '184-043-2679', '183-969-0314', '182-989-1621', '182-658-8995', '179-653-2326', '173-621-3385', '173-408-8529', '170-902-5708', '170-706-2380', '167-988-9616', '166-254-3425', '164-808-1409', '161-924-6271', '160-692-0029', '156-449-4188', '153-799-6576', '151-413-2904', '138-857-4025', '138-815-0221', '138-026-9837', '135-986-4041', '135-496-2827', '132-816-6327', '123-811-5774', '123-437-4321', '119-039-3025', '118-692-0962', '117-659-8877', '117-111-2974', '116-167-8846', '112-690-3425', '112-501-2988', '111-644-2872', '106-024-1110', '104-224-6026', '102-960-5140', '102-725-9525', '100-664-8978', '462-247-5479', '832-081-6061', '385-378-8385', '153-759-9516', '557-798-2924', '663-700-4828', '218-306-8481', '880-439-6234', '885-695-1363', '177-600-7777', '591-650-6832', '838-666-2067', '239-434-9322', '369-918-9710', '967-825-7970', '496-628-4924', '890-301-5775', '335-636-2214', '193-692-4285', '945-964-0620', '183-023-0430', '846-646-2453', '705-832-4806', '295-594-5055', '401-441-3474', '161-882-1774', '169-416-4018', '259-418-0567', '340-319-3304', '344-841-3865', '274-751-4351', '536-670-2083', '995-901-7117', '952-970-4083', '926-253-6124', '855-687-0485', '774-537-4330', '766-257-0521', '738-598-5278', '732-597-2231', '624-247-7208', '618-997-2208', '602-387-3923', '588-492-7996', '531-727-2179', '477-058-0716', '426-445-4738', '367-575-4312', '345-732-4021', '267-899-7423', '225-874-2208', '209-262-7359', '169-439-3832', '156-883-1076', '106-259-5780', '105-693-4772', '946-461-1508', '666-436-7590', '124-076-8529', '933-499-8956', '826-940-9162', '802-191-6503', '547-588-0034', '659-350-3481', '493-941-8950', '472-133-6818', '444-518-8301', '272-756-1652', '200-740-5044', '159-216-5921', '126-155-4699', '619-597-8271', '253-530-5462', '735-338-1285', '903-016-4232', '769-217-8555', '592-750-6240', '404-009-3251', '242-546-5681', '629-082-1618', '678-257-3414', '272-411-2228', '353-311-3534', '314-254-6873', '861-291-2455', '126-322-3361', '719-344-0753', '712-698-8987', '450-107-0904', '594-291-4329', '587-080-9489', '426-828-1085', '454-665-9612', '237-330-3465', '923-782-1605', '335-879-2634', '331-840-2227', '462-071-4508', '348-033-6783'),
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,ImpressionReach,AverageFrequency',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));





// AAC
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'aac',
    'file_name' => "adwords_historical_aaac_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'annalectautomation@gmail.com',
    'refresh_token' => '1/Fb-kcD0UFz63kdX2fg9tTPN3QR0izXPm0Tdkvvv_KOw',
    'accounts' => array('156-469-0702','575-470-1972','606-092-7999','204-292-7012'),
    'report' => 'KEYWORDS_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,Criteria,KeywordMatchType,ClickType,Clicks,Impressions,Cost,Conversions',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [Infiniti]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'infiniti',
    'file_name' => "adwords_historical_infiniti_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'annalectautomation@gmail.com',
    'refresh_token' => '1/Fb-kcD0UFz63kdX2fg9tTPN3QR0izXPm0Tdkvvv_KOw',
    'accounts' => array('427-424-6315', '558-732-8768', '164-808-1409', '690-041-1221', '867-302-1843', '558-732-8768', '296-116-3862', '838-443-5660', '756-558-3856', '695-952-2231', '421-833-0827', '145-969-9760', '707-135-3207', '444-106-1264', '312-797-8811', '444-106-1264', '388-771-9605', '553-141-3280'),
    'report' => 'KEYWORDS_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,Criteria,KeywordMatchType,ClickType,Clicks,Impressions,Cost,Conversions',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [Nissan]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'nissan',
    'file_name' => "adwords_historical_nissan_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'credential_email' => 'annalectautomation@gmail.com',
    'refresh_token' => '1/Fb-kcD0UFz63kdX2fg9tTPN3QR0izXPm0Tdkvvv_KOw',
    'accounts' => array('520-736-9948', '666-680-9011', '328-503-0348', '406-860-1849', '535-372-6558', '404-064-4105', '290-524-8374', '625-250-1403', '389-428-7495'),
    'report' => 'KEYWORDS_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,Criteria,KeywordMatchType,ClickType,Clicks,Impressions,Cost,Conversions',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project OMD - Ad]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'omd_ad',
    'file_name' => "adwords_historical_omd_ad_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'refresh_token' => '1/v-rDuUwM3aZ9XP-Ewo0oa9IPglJ1YofIyGdSbcOdbQk',
    'accounts' => array('780-635-5944', '162-952-1091', '111-215-5758', '745-321-0048', '180-145-7948', '219-875-3614', '115-791-8453', '867-302-1843', '520-736-9948', '666-680-9011', '838-443-5660', '661-235-1106', '682-672-4117', '434-678-1112', '156-469-0702', '694-030-1351', '800-931-6351', '684-449-5733', '727-204-6923', '495-801-9334', '178-870-1816', '655-720-0785', '690-041-1221', '695-952-2231', '253-214-5451', '516-330-0303', '145-969-9760', '260-997-7972', '576-978-8881', '189-557-4068', '678-464-4724', '160-634-1253', '444-106-1264', '932-389-4720', '781-094-1606', '328-503-0348', '604-396-7359', '846-573-8853', '261-913-8410', '518-011-7932', '663-370-8085', '183-194-6380', '213-554-6736', '575-470-1972', '244-067-0150', '482-187-8768', '388-771-9605', '535-372-6558', '719-629-9377', '917-036-3837', '880-766-7381', '386-512-6357', '333-027-8551', '406-860-1849', '830-836-7777', '137-613-4118', '429-021-2006', '841-444-7450', '910-403-5521', '290-524-8374', '642-636-7032', '594-301-8559', '405-951-6488', '425-282-5237', '806-807-9187', '404-064-4105', '344-862-9189', '699-547-2573', '117-910-6175', '606-092-7999', '899-428-3852', '633-686-7828', '931-357-7878', '726-663-1132', '999-786-1627', '996-943-7684', '986-367-8208', '985-170-2809', '984-621-2979', '983-359-1701', '981-754-2118', '979-382-4676', '978-595-2904', '977-557-5680', '976-323-2234', '974-833-3937', '972-358-1838', '965-527-3090', '963-656-9874', '957-782-9610', '957-390-2231', '953-849-8080', '951-646-0491', '950-353-6423', '934-623-4286', '934-120-3021', '933-021-2170', '932-190-5008', '929-627-8429', '928-476-0827', '928-442-7078', '927-223-9004', '925-724-4889', '917-630-8870', '909-755-7716', '909-064-2740', '908-634-2539', '907-954-2829', '907-300-9885', '905-214-8963', '900-428-3126', '899-926-3502', '895-674-3729', '895-170-2989', '891-128-6718', '889-452-8778', '888-579-9190', '885-937-0762', '885-061-7354', '884-983-1171', '884-246-8243', '881-506-2621', '880-032-9574', '877-413-7306', '872-511-8029', '869-991-2110', '864-868-5416', '860-881-3085', '858-646-5232', '855-527-7918', '852-344-6306', '850-614-8538', '848-320-3074', '842-227-9981', '840-501-0723', '839-605-3980', '835-791-0961', '829-951-4021', '828-691-7386', '807-288-2429', '806-510-4334', '804-784-1517', '803-830-1926', '802-526-6584', '800-830-2410', '796-889-8074', '796-131-7321', '789-731-6258', '785-654-5002', '781-104-3785', '776-961-4536', '774-856-0407', '773-392-7583', '768-158-3053', '765-155-1395', '764-524-0476', '760-220-3797', '758-671-4978', '757-220-4974', '756-558-3856', '756-511-3412', '756-171-1476', '753-593-3672', '753-041-4714', '751-833-0565', '751-176-1472', '750-448-9002', '749-320-5929', '741-443-6523', '740-899-6887', '739-899-1527', '728-359-2557', '724-448-6729', '722-755-8864', '720-616-7872', '718-237-8957', '718-031-7342', '717-847-2922', '711-343-1474', '710-129-0820', '707-167-9687', '707-135-3207', '703-464-9619', '700-472-2206', '699-671-4282', '686-066-4729', '680-265-3198', '680-144-0394', '676-465-7129', '672-876-9725', '672-751-2978', '671-865-1583', '668-788-2325', '664-369-3777', '660-249-0240', '653-195-5225', '646-634-0784', '632-781-5622', '627-166-0725', '626-258-9709', '625-250-1403', '621-502-7480', '616-529-1024', '613-630-0864', '601-577-1034', '600-317-8081', '598-518-2539', '598-133-0295', '593-331-8323', '592-133-4427', '588-377-4889', '584-153-2470', '583-821-4821', '574-286-7525', '573-885-7620', '562-685-7637', '562-599-5076', '558-732-8768', '553-141-3280', '548-613-9038', '547-307-0576', '542-167-6611', '538-138-9481', '531-765-8053', '527-721-1106', '526-543-0625', '518-428-8472', '515-467-8782', '514-400-3808', '508-223-7259', '506-770-3685', '506-501-1174', '500-378-7427', '497-781-7714', '489-547-2730', '487-855-7772', '485-605-7861', '484-713-9386', '481-278-7176', '478-969-9672', '478-777-3561', '474-498-9083', '474-007-6133', '466-937-6486', '460-153-1886', '455-083-3729', '454-693-9325', '452-540-5965', '451-489-4510', '442-775-9551', '435-289-3929', '434-745-1614', '434-198-0639', '431-634-3572', '430-234-3320', '427-424-6315', '425-013-9034', '424-632-5723', '421-833-0827', '421-267-4929', '416-509-4933', '415-998-8123', '414-742-8225', '413-862-6629', '413-655-1902', '413-331-0826', '405-698-9972', '403-958-4497', '401-512-8427', '398-119-3823', '396-412-8653', '395-268-8472', '392-323-1025', '391-321-0227', '389-428-7495', '381-344-0085', '381-253-8576', '379-400-8629', '378-907-8530', '378-135-5304', '378-042-7571', '375-733-0472', '375-213-4940', '373-455-2329', '369-937-3177', '368-966-0024', '368-898-3994', '368-727-6669', '356-739-8806', '356-594-7426', '342-818-4382', '342-507-7527', '341-417-9510', '340-087-9008', '338-822-7589', '336-931-5485', '336-152-7340', '330-875-6427', '329-466-0837', '323-629-7725', '322-805-5252', '322-468-0035', '322-385-5304', '321-669-4553', '320-979-9435', '319-781-9969', '315-963-0823', '314-752-3059', '313-304-7178', '312-797-8811', '309-362-6621', '297-754-9285', '296-991-1927', '296-116-3862', '292-521-3327', '287-364-8174', '280-774-5976', '274-509-3041', '273-046-2423', '266-301-8925', '264-521-2323', '261-762-6373', '258-725-4878', '255-446-8521', '251-556-1478', '251-142-3688', '245-955-6973', '241-637-7376', '239-281-9088', '237-648-5880', '235-936-0178', '235-137-1827', '235-043-0837', '231-709-7881', '229-415-3190', '227-351-7600', '225-943-2863', '225-915-5629', '225-528-9570', '223-380-5121', '215-611-0343', '214-750-8125', '207-500-6878', '204-292-7012', '204-069-9727', '203-109-9021', '201-045-0724', '200-631-2176', '199-526-0678', '198-702-2360', '192-988-9023', '185-575-4885', '184-043-2679', '183-969-0314', '182-989-1621', '182-658-8995', '179-653-2326', '173-621-3385', '173-408-8529', '170-902-5708', '170-706-2380', '167-988-9616', '166-254-3425', '164-808-1409', '161-924-6271', '160-692-0029', '156-449-4188', '153-799-6576', '151-413-2904', '138-857-4025', '138-815-0221', '138-026-9837', '135-986-4041', '135-496-2827', '132-816-6327', '123-811-5774', '123-437-4321', '119-039-3025', '118-692-0962', '117-659-8877', '117-111-2974', '116-167-8846', '112-690-3425', '112-501-2988', '111-644-2872', '106-024-1110', '104-224-6026', '102-960-5140', '102-725-9525', '100-664-8978', '462-247-5479', '832-081-6061', '385-378-8385', '153-759-9516', '557-798-2924', '663-700-4828', '218-306-8481', '880-439-6234', '885-695-1363', '177-600-7777', '591-650-6832', '838-666-2067', '239-434-9322', '369-918-9710', '967-825-7970', '496-628-4924', '890-301-5775', '335-636-2214', '193-692-4285', '945-964-0620', '183-023-0430', '846-646-2453', '705-832-4806', '295-594-5055', '401-441-3474', '161-882-1774', '169-416-4018', '259-418-0567', '340-319-3304', '344-841-3865', '274-751-4351', '536-670-2083', '995-901-7117', '952-970-4083', '926-253-6124', '855-687-0485', '774-537-4330', '766-257-0521', '738-598-5278', '732-597-2231', '624-247-7208', '618-997-2208', '602-387-3923', '588-492-7996', '531-727-2179', '477-058-0716', '426-445-4738', '367-575-4312', '345-732-4021', '267-899-7423', '225-874-2208', '209-262-7359', '169-439-3832', '156-883-1076', '106-259-5780', '105-693-4772', '946-461-1508', '666-436-7590', '124-076-8529', '933-499-8956', '826-940-9162', '802-191-6503', '547-588-0034', '659-350-3481', '493-941-8950', '472-133-6818', '444-518-8301', '272-756-1652', '200-740-5044', '159-216-5921', '126-155-4699', '619-597-8271', '253-530-5462', '735-338-1285', '903-016-4232', '769-217-8555', '592-750-6240', '404-009-3251', '242-546-5681', '629-082-1618', '678-257-3414', '272-411-2228', '353-311-3534', '314-254-6873', '861-291-2455', '126-322-3361', '719-344-0753', '712-698-8987', '450-107-0904', '594-291-4329', '587-080-9489', '426-828-1085', '454-665-9612', '237-330-3465', '923-782-1605', '335-879-2634', '331-840-2227', '462-071-4508', '348-033-6783'),
    'report' => 'AD_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CreativeFinalUrls,AdGroupName,AverageCpv,CampaignName,Clicks,Cost,Ctr,Headline,Impressions,VideoQuartile100Rate,VideoViews',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [OMD - Campaign]
array_push($extractions['items'], array(

    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'omd_campaign',
    'file_name' => "adwords_historical_omd_campaign_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'refresh_token' => '1/v-rDuUwM3aZ9XP-Ewo0oa9IPglJ1YofIyGdSbcOdbQk',
    'accounts' => array('780-635-5944', '162-952-1091', '111-215-5758', '745-321-0048', '180-145-7948', '219-875-3614', '115-791-8453', '867-302-1843', '520-736-9948', '666-680-9011', '838-443-5660', '661-235-1106', '682-672-4117', '434-678-1112', '156-469-0702', '694-030-1351', '800-931-6351', '684-449-5733', '727-204-6923', '495-801-9334', '178-870-1816', '655-720-0785', '690-041-1221', '695-952-2231', '253-214-5451', '516-330-0303', '145-969-9760', '260-997-7972', '576-978-8881', '189-557-4068', '678-464-4724', '160-634-1253', '444-106-1264', '932-389-4720', '781-094-1606', '328-503-0348', '604-396-7359', '846-573-8853', '261-913-8410', '518-011-7932', '663-370-8085', '183-194-6380', '213-554-6736', '575-470-1972', '244-067-0150', '482-187-8768', '388-771-9605', '535-372-6558', '719-629-9377', '917-036-3837', '880-766-7381', '386-512-6357', '333-027-8551', '406-860-1849', '830-836-7777', '137-613-4118', '429-021-2006', '841-444-7450', '910-403-5521', '290-524-8374', '642-636-7032', '594-301-8559', '405-951-6488', '425-282-5237', '806-807-9187', '404-064-4105', '344-862-9189', '699-547-2573', '117-910-6175', '606-092-7999', '899-428-3852', '633-686-7828', '931-357-7878', '726-663-1132', '999-786-1627', '996-943-7684', '986-367-8208', '985-170-2809', '984-621-2979', '983-359-1701', '981-754-2118', '979-382-4676', '978-595-2904', '977-557-5680', '976-323-2234', '974-833-3937', '972-358-1838', '965-527-3090', '963-656-9874', '957-782-9610', '957-390-2231', '953-849-8080', '951-646-0491', '950-353-6423', '934-623-4286', '934-120-3021', '933-021-2170', '932-190-5008', '929-627-8429', '928-476-0827', '928-442-7078', '927-223-9004', '925-724-4889', '917-630-8870', '909-755-7716', '909-064-2740', '908-634-2539', '907-954-2829', '907-300-9885', '905-214-8963', '900-428-3126', '899-926-3502', '895-674-3729', '895-170-2989', '891-128-6718', '889-452-8778', '888-579-9190', '885-937-0762', '885-061-7354', '884-983-1171', '884-246-8243', '881-506-2621', '880-032-9574', '877-413-7306', '872-511-8029', '869-991-2110', '864-868-5416', '860-881-3085', '858-646-5232', '855-527-7918', '852-344-6306', '850-614-8538', '848-320-3074', '842-227-9981', '840-501-0723', '839-605-3980', '835-791-0961', '829-951-4021', '828-691-7386', '807-288-2429', '806-510-4334', '804-784-1517', '803-830-1926', '802-526-6584', '800-830-2410', '796-889-8074', '796-131-7321', '789-731-6258', '785-654-5002', '781-104-3785', '776-961-4536', '774-856-0407', '773-392-7583', '768-158-3053', '765-155-1395', '764-524-0476', '760-220-3797', '758-671-4978', '757-220-4974', '756-558-3856', '756-511-3412', '756-171-1476', '753-593-3672', '753-041-4714', '751-833-0565', '751-176-1472', '750-448-9002', '749-320-5929', '741-443-6523', '740-899-6887', '739-899-1527', '728-359-2557', '724-448-6729', '722-755-8864', '720-616-7872', '718-237-8957', '718-031-7342', '717-847-2922', '711-343-1474', '710-129-0820', '707-167-9687', '707-135-3207', '703-464-9619', '700-472-2206', '699-671-4282', '686-066-4729', '680-265-3198', '680-144-0394', '676-465-7129', '672-876-9725', '672-751-2978', '671-865-1583', '668-788-2325', '664-369-3777', '660-249-0240', '653-195-5225', '646-634-0784', '632-781-5622', '627-166-0725', '626-258-9709', '625-250-1403', '621-502-7480', '616-529-1024', '613-630-0864', '601-577-1034', '600-317-8081', '598-518-2539', '598-133-0295', '593-331-8323', '592-133-4427', '588-377-4889', '584-153-2470', '583-821-4821', '574-286-7525', '573-885-7620', '562-685-7637', '562-599-5076', '558-732-8768', '553-141-3280', '548-613-9038', '547-307-0576', '542-167-6611', '538-138-9481', '531-765-8053', '527-721-1106', '526-543-0625', '518-428-8472', '515-467-8782', '514-400-3808', '508-223-7259', '506-770-3685', '506-501-1174', '500-378-7427', '497-781-7714', '489-547-2730', '487-855-7772', '485-605-7861', '484-713-9386', '481-278-7176', '478-969-9672', '478-777-3561', '474-498-9083', '474-007-6133', '466-937-6486', '460-153-1886', '455-083-3729', '454-693-9325', '452-540-5965', '451-489-4510', '442-775-9551', '435-289-3929', '434-745-1614', '434-198-0639', '431-634-3572', '430-234-3320', '427-424-6315', '425-013-9034', '424-632-5723', '421-833-0827', '421-267-4929', '416-509-4933', '415-998-8123', '414-742-8225', '413-862-6629', '413-655-1902', '413-331-0826', '405-698-9972', '403-958-4497', '401-512-8427', '398-119-3823', '396-412-8653', '395-268-8472', '392-323-1025', '391-321-0227', '389-428-7495', '381-344-0085', '381-253-8576', '379-400-8629', '378-907-8530', '378-135-5304', '378-042-7571', '375-733-0472', '375-213-4940', '373-455-2329', '369-937-3177', '368-966-0024', '368-898-3994', '368-727-6669', '356-739-8806', '356-594-7426', '342-818-4382', '342-507-7527', '341-417-9510', '340-087-9008', '338-822-7589', '336-931-5485', '336-152-7340', '330-875-6427', '329-466-0837', '323-629-7725', '322-805-5252', '322-468-0035', '322-385-5304', '321-669-4553', '320-979-9435', '319-781-9969', '315-963-0823', '314-752-3059', '313-304-7178', '312-797-8811', '309-362-6621', '297-754-9285', '296-991-1927', '296-116-3862', '292-521-3327', '287-364-8174', '280-774-5976', '274-509-3041', '273-046-2423', '266-301-8925', '264-521-2323', '261-762-6373', '258-725-4878', '255-446-8521', '251-556-1478', '251-142-3688', '245-955-6973', '241-637-7376', '239-281-9088', '237-648-5880', '235-936-0178', '235-137-1827', '235-043-0837', '231-709-7881', '229-415-3190', '227-351-7600', '225-943-2863', '225-915-5629', '225-528-9570', '223-380-5121', '215-611-0343', '214-750-8125', '207-500-6878', '204-292-7012', '204-069-9727', '203-109-9021', '201-045-0724', '200-631-2176', '199-526-0678', '198-702-2360', '192-988-9023', '185-575-4885', '184-043-2679', '183-969-0314', '182-989-1621', '182-658-8995', '179-653-2326', '173-621-3385', '173-408-8529', '170-902-5708', '170-706-2380', '167-988-9616', '166-254-3425', '164-808-1409', '161-924-6271', '160-692-0029', '156-449-4188', '153-799-6576', '151-413-2904', '138-857-4025', '138-815-0221', '138-026-9837', '135-986-4041', '135-496-2827', '132-816-6327', '123-811-5774', '123-437-4321', '119-039-3025', '118-692-0962', '117-659-8877', '117-111-2974', '116-167-8846', '112-690-3425', '112-501-2988', '111-644-2872', '106-024-1110', '104-224-6026', '102-960-5140', '102-725-9525', '100-664-8978', '462-247-5479', '832-081-6061', '385-378-8385', '153-759-9516', '557-798-2924', '663-700-4828', '218-306-8481', '880-439-6234', '885-695-1363', '177-600-7777', '591-650-6832', '838-666-2067', '239-434-9322', '369-918-9710', '967-825-7970', '496-628-4924', '890-301-5775', '335-636-2214', '193-692-4285', '945-964-0620', '183-023-0430', '846-646-2453', '705-832-4806', '295-594-5055', '401-441-3474', '161-882-1774', '169-416-4018', '259-418-0567', '340-319-3304', '344-841-3865', '274-751-4351', '536-670-2083', '995-901-7117', '952-970-4083', '926-253-6124', '855-687-0485', '774-537-4330', '766-257-0521', '738-598-5278', '732-597-2231', '624-247-7208', '618-997-2208', '602-387-3923', '588-492-7996', '531-727-2179', '477-058-0716', '426-445-4738', '367-575-4312', '345-732-4021', '267-899-7423', '225-874-2208', '209-262-7359', '169-439-3832', '156-883-1076', '106-259-5780', '105-693-4772', '946-461-1508', '666-436-7590', '124-076-8529', '933-499-8956', '826-940-9162', '802-191-6503', '547-588-0034', '659-350-3481', '493-941-8950', '472-133-6818', '444-518-8301', '272-756-1652', '200-740-5044', '159-216-5921', '126-155-4699', '619-597-8271', '253-530-5462', '735-338-1285', '903-016-4232', '769-217-8555', '592-750-6240', '404-009-3251', '242-546-5681', '629-082-1618', '678-257-3414', '272-411-2228', '353-311-3534', '314-254-6873', '861-291-2455', '126-322-3361', '719-344-0753', '712-698-8987', '450-107-0904', '594-291-4329', '587-080-9489', '426-828-1085', '454-665-9612', '237-330-3465', '923-782-1605', '335-879-2634', '331-840-2227', '462-071-4508', '348-033-6783'),
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,ImpressionReach,AverageFrequency',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));




// Project [HS - Ad]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'hs_ad',
    'file_name' => "adwords_historical_hs_ad_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'refresh_token' => '1/TosspM4R9xjQ_uC-ohUA_aGnXysV42DdFzghQszWOqs',
    'accounts' =>array('704-025-7619', '908-947-4290', '714-325-6698', '324-759-1001', '756-093-6674', '442-961-2601', '988-421-0729', '930-170-8395', '759-200-7664', '849-992-0813', '311-578-4939', '377-468-5688', '854-942-2560', '727-486-3217', '144-300-2462', '247-886-6052', '645-676-6082', '358-323-8078', '769-267-2754'),
    'report' => 'AD_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CreativeFinalUrls,AdGroupName,AverageCpv,CampaignName,Clicks,Cost,Ctr,Headline,Impressions,VideoQuartile100Rate,VideoViews',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));

// Project [HS - Campaign]
array_push($extractions['items'], array(
    'api' => 'adwords',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'hs_campaign',
    'file_name' => "adwords_historical_hs_campaign_2017-06-01_".$extractions['global']['date']['yesterday'] .".csv",
    'refresh_token' => '1/TosspM4R9xjQ_uC-ohUA_aGnXysV42DdFzghQszWOqs',
    'accounts' =>array('704-025-7619', '908-947-4290', '714-325-6698', '324-759-1001', '756-093-6674', '442-961-2601', '988-421-0729', '930-170-8395', '759-200-7664', '849-992-0813', '311-578-4939', '377-468-5688', '854-942-2560', '727-486-3217', '144-300-2462', '247-886-6052', '645-676-6082', '358-323-8078', '769-267-2754'),
    'report' => 'CAMPAIGN_PERFORMANCE_REPORT',
    'metrics' => 'Date,AccountDescriptiveName,CampaignName,ImpressionReach,AverageFrequency',
    'startDate' => $extractions['global']['adwords']['historic'],
    'endDate' => $extractions['global']['adwords']['yesterday']
));



*/



//
//  / ___|  | |_    __ _   _ __     __| |   __ _   _ __    __| |
//  \___ \  | __|  / _` | | '_ \   / _` |  / _` | | '__|  / _` |
//   ___) | | |_  | (_| | | | | | | (_| | | (_| | | |    | (_| |
//  |____/   \__|  \__,_| |_| |_|  \__,_|  \__,_| |_|     \__,_|
//
//

$phd_sample= array(
    array('profileId' => '4342702', 'networkName' => 'Virgin Atlantic DCM - EMEA', 'advertiserName' => 'Virgin Atlantic ', 'advertiserId' => '5912534'),
    array('profileId' => '4341639', 'networkName' => 'Canon - DFA EMEA', 'advertiserName' => 'Canon - MENA ', 'advertiserId' => '6927278'),
    array('profileId' => '4341639', 'networkName' => 'Canon - DFA EMEA', 'advertiserName' => 'Canon Middle East OLD ', 'advertiserId' => '2376384'),
);
$phd_account_data_std_cross = array(
    array('profileId' => '4342702', 'networkName' => 'Virgin Atlantic DCM - EMEA', 'advertiserName' => 'Virgin Atlantic ', 'advertiserId' => '5912534'),
    array('profileId' => '4385849', 'networkName' => 'Alshaya CRM & Digital', 'advertiserName' => 'Mothercare Kuwait', 'advertiserId' => '8395950'),
    array('profileId' => '4385849', 'networkName' => 'Alshaya CRM & Digital', 'advertiserName' => 'H&M Kuwait', 'advertiserId' => '8384822'),
    array('profileId' => '4368959', 'networkName' => 'Arla-DCM-EMEA-AE', 'advertiserName' => 'Arla Foods', 'advertiserId' => '8271328'),
    array('profileId' => '4368959', 'networkName' => 'Arla-DCM-EMEA-AE', 'advertiserName' => 'Castello ', 'advertiserId' => '5452626'),
    array('profileId' => '4368959', 'networkName' => 'Arla-DCM-EMEA-AE', 'advertiserName' => 'Lurpak ', 'advertiserId' => '5449796'),
    array('profileId' => '4368959', 'networkName' => 'Arla-DCM-EMEA-AE', 'advertiserName' => 'Puck', 'advertiserId' => '5454647'),
    array('profileId' => '4368959', 'networkName' => 'Arla-DCM-EMEA-AE', 'advertiserName' => 'The Three cows ', 'advertiserId' => '6977903'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_KIN_BUE ', 'advertiserId' => '8173460'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_KIN_BUM ', 'advertiserId' => '8178103'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_KIN_KCC ', 'advertiserId' => '8173748'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_KIN_KCM ', 'advertiserId' => '8175500'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_KIN_KCR', 'advertiserId' => '8167479'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_KIN_KSR ', 'advertiserId' => '8178088'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_KIN_SCW ', 'advertiserId' => '8177185'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_NUT_NUB ', 'advertiserId' => '8172188'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_NUT_NUT ', 'advertiserId' => '8173463'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_PARENT', 'advertiserId' => '5060530'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_PRA_FROC ', 'advertiserId' => '8173745'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_PRA_GOG', 'advertiserId' => '8173730'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_PRA_RAF ', 'advertiserId' => '8173457'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_TIC_GUM ', 'advertiserId' => '8219982'),
    array('profileId' => '4363674', 'networkName' => 'Ferrero - DCM - USD', 'advertiserName' => 'FERRERO_MEA_GCC_TIC_TTAC', 'advertiserId' => '8173733'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Lexus Tactical Adwords ', 'advertiserId' => '4743311'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Toyota Tactical/Launch ', 'advertiserId' => '4636880'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Al Futtaim-Honda', 'advertiserId' => '5308422'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'IKEA', 'advertiserId' => '6961008'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Delphys', 'advertiserId' => '6812091'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Volvo', 'advertiserId' => '8061690'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'AFGRE', 'advertiserId' => '8100130'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Guess', 'advertiserId' => '8135621'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Doha Festival City', 'advertiserId' => '8098951'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Dubai Festival City', 'advertiserId' => '6959300'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Automall', 'advertiserId' => '6963390'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Hertz', 'advertiserId' => '6959844'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Robinsons', 'advertiserId' => '8080508'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Marks&Spencer', 'advertiserId' => '8198344'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'AF-Retail', 'advertiserId' => '8150568'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Ted Baker', 'advertiserId' => '8213353'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'AFAG-Autogroup', 'advertiserId' => '8301146'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Al Futtaim-FAMCO', 'advertiserId' => '6417186'),
    array('profileId' => '4342699', 'networkName' => 'Bentley Motors c/o PHD International - DFA EMEA', 'advertiserName' => 'Bentley - UAE ', 'advertiserId' => '3875593'),
    array('profileId' => '4341639', 'networkName' => 'Canon - DFA EMEA', 'advertiserName' => 'Canon - MENA ', 'advertiserId' => '6927278'),
    array('profileId' => '4341639', 'networkName' => 'Canon - DFA EMEA', 'advertiserName' => 'Canon Middle East OLD ', 'advertiserId' => '2376384'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'ADMAF', 'advertiserId' => '8324528'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Daman', 'advertiserId' => '8386540'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Avenue', 'advertiserId' => '4407259'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Bosch Siemens', 'advertiserId' => '4116173'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'DAFZA', 'advertiserId' => '4944371'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Dubai World Trade Center', 'advertiserId' => '4509632'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Ellington Group', 'advertiserId' => '6567610'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Ferrero', 'advertiserId' => '5089702'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Louvre AD', 'advertiserId' => '8157464'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Mashreq Bank', 'advertiserId' => '4343157'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'McLaren', 'advertiserId' => '4359113'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Meraas Holding', 'advertiserId' => '5584068'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Mubadala', 'advertiserId' => '3313505'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'New York University Abu Dhabi', 'advertiserId' => '5559511'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'NFPC', 'advertiserId' => '4448188'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Pizza Hut', 'advertiserId' => '4797970'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Porsche', 'advertiserId' => '3253807'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Power Horse', 'advertiserId' => '3125621'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Roads and Transport Authority (RTA)', 'advertiserId' => '8343518'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Saadiyat Development and Investment Company', 'advertiserId' => '4884700'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'SC Johnson', 'advertiserId' => '5002322'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'TDIC', 'advertiserId' => '3047713'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Unilever', 'advertiserId' => '4448401'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Abu Dhabi Film Festival (ADFF) ', 'advertiserId' => '3824854'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'All About Brands', 'advertiserId' => '4854554'),

);
$phd_account_data_flood = array(
    array('profileId' => '4342702', 'networkName' => 'Virgin Atlantic DCM - EMEA', 'advertiserName' => 'Virgin Atlantic ', 'floodlightConfigId' => '5912534'),
    array('profileId' => '4368959', 'networkName' => 'Arla-DCM-EMEA-AE', 'advertiserName' => 'Lurpak ', 'floodlightConfigId' => '5449796'),
    array('profileId' => '4368959', 'networkName' => 'Arla-DCM-EMEA-AE', 'advertiserName' => 'Puck', 'floodlightConfigId' => '5454647'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Lexus Tactical Adwords ', 'floodlightConfigId' => '4898321'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Toyota Tactical/Launch ', 'floodlightConfigId' => '4711608'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Al Futtaim-Honda', 'floodlightConfigId' => '5308422'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Volvo', 'floodlightConfigId' => '8061690'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'AFGRE', 'floodlightConfigId' => '8100130'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Automall', 'floodlightConfigId' => '6963390'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Hertz', 'floodlightConfigId' => '6959844'),
    array('profileId' => '4347691', 'networkName' => 'PHD Dubai - AFM - DCM', 'advertiserName' => 'Al Futtaim-FAMCO', 'floodlightConfigId' => '6417186'),
    array('profileId' => '4342699', 'networkName' => 'Bentley Motors c/o PHD International - DFA EMEA', 'advertiserName' => 'Bentley - UAE ', 'floodlightConfigId' => '3801822'),
    array('profileId' => '4341639', 'networkName' => 'Canon - DFA EMEA', 'advertiserName' => 'Canon Middle East OLD ', 'floodlightConfigId' => '2376384'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'ADMAF', 'floodlightConfigId' => '8324528'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Daman', 'floodlightConfigId' => '8386540'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Avenue', 'floodlightConfigId' => '4407259'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Bosch Siemens', 'floodlightConfigId' => '4116173'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'DAFZA', 'floodlightConfigId' => '4944371'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Dubai World Trade Center', 'floodlightConfigId' => '4509632'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Ellington Group', 'floodlightConfigId' => '6567610'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Louvre AD', 'floodlightConfigId' => '8157464'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Mashreq Bank', 'floodlightConfigId' => '4343157'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'McLaren', 'floodlightConfigId' => '4359113'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Meraas Holding', 'floodlightConfigId' => '5584068'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Mubadala', 'floodlightConfigId' => '3313505'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Pizza Hut', 'floodlightConfigId' => '4797970'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Porsche', 'floodlightConfigId' => '3253807'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Power Horse', 'floodlightConfigId' => '3125621'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Roads and Transport Authority (RTA)', 'floodlightConfigId' => '8343518'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Saadiyat Development and Investment Company', 'floodlightConfigId' => '4884700'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'TDIC', 'floodlightConfigId' => '3047713'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'Unilever', 'floodlightConfigId' => '4448401'),
    array('profileId' => '4342696', 'networkName' => 'PHD UAE - DFA EMEA', 'advertiserName' => 'All About Brands', 'floodlightConfigId' => '4854554'),

);


// histo phduae@annalect.com
/*
array_push($extractions['items'], array(
    'api' => 'dcm',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'standard_aio_phd',
    'report_type' => "STANDARD",
    'max_execution_sec' => 3600,
    'file_name' => "dcm_standard_historical_2017-06-01_".$extractions['global']['dcm']['yesterday'].".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/E7JWUMvbVu9v_eQKBBCvPOP6m1vtSUrG58LGyTEXn74',
    'accountsData' => $phd_account_data_std_cross,
    'json_request' => '{
  "name": "test alex",
  "type": "STANDARD",
  "delivery": {
    "recipients": [
      {
        "deliveryType": "LINK",
        "email": "phduae@annalect.com"
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
        "startDate": "2017-06-01",
        "endDate": "YESTERDAY"
    },
    "dimensions": [
      {"name": "dfa:campaign"},
      {"name": "dfa:site"},
      {"name": "dfa:placement"},
      {"name": "dfa:creativeSize"},
      {"name": "dfa:advertiser"},
      {"name": "dfa:advertiserId"},
      {"name": "dfa:activity"},
      {"name": "dfa:placementCostStructure"}
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
    ],
    "dimensionFilters": [
      {
        "dimensionName": "dfa:advertiser",
        "id": "4743311"
      },
      {
        "dimensionName": "dfa:advertiser",
        "id": "4636880"
      }
    ]
  }
}'
));
*/



//   _____   _                       _   _   _           _       _
//  |  ___| | |   ___     ___     __| | | | (_)   __ _  | |__   | |_
//  | |_    | |  / _ \   / _ \   / _` | | | | |  / _` | | '_ \  | __|
//  |  _|   | | | (_) | | (_) | | (_| | | | | | | (_| | | | | | | |_
//  |_|     |_|  \___/   \___/   \__,_| |_| |_|  \__, | |_| |_|  \__|
//                                               |___/



// histo phduae@annalect.com
/*
array_push($extractions['items'], array(
    'api' => 'dcm',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'floodlight_historical_aio_phd',
    'max_execution_sec' => 3600,
    'report_type' => 'FLOODLIGHT',
    'file_name' => "dcm_floodlight_historical_2017-06-01_".$extractions['global']['dcm']['yesterday'].".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/E7JWUMvbVu9v_eQKBBCvPOP6m1vtSUrG58LGyTEXn74',
    'accountsData' => $phd_account_data_flood,
    'json_request' => '{
          "name": "test alex",
          "type": "FLOODLIGHT",
          "delivery": {
            "recipients": [
              {
                "deliveryType": "LINK",
                "email": "phduae@annalect.com"
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
            "startDate": "2017-06-01",
            "endDate": "YESTERDAY"
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



//
//     ____                                ____    _                                    _
//    / ___|  _ __    ___    ___   ___    |  _ \  (_)  _ __ ___     ___   _ __    ___  (_)   ___    _ __
//   | |     | '__|  / _ \  / __| / __|   | | | | | | | '_ ` _ \   / _ \ | '_ \  / __| | |  / _ \  | '_ \
//   | |___  | |    | (_) | \__ \ \__ \   | |_| | | | | | | | | | |  __/ | | | | \__ \ | | | (_) | | | | |
//    \____| |_|     \___/  |___/ |___/   |____/  |_| |_| |_| |_|  \___| |_| |_| |___/ |_|  \___/  |_| |_|
//


// histo phduae@annalect.com
array_push($extractions['items'], array(
    'api' => 'dcm',
    'api_type' => 'google',
    'extraction_name' => 'aio_phd',
    'task_name' => 'crossreach_historical_aio_phd',
    'max_execution_sec' => 3600,
    'report_type' => "CROSS_DIMENSION_REACH",
    'file_name' => "dcm_crossreach_historical_2017-06-01_".$extractions['global']['dcm']['yesterday'].".csv",
    'credential_email' => 'phduae@annalect.com',
    'refresh_token' => '1/E7JWUMvbVu9v_eQKBBCvPOP6m1vtSUrG58LGyTEXn74',
    'accountsData' => $phd_account_data_std_cross,
    'json_request' => '{
  "name": "test alex",
  "type": "CROSS_DIMENSION_REACH",
  "delivery": {
    "recipients": [
      {
        "deliveryType": "LINK",
        "email": "phduae@annalect.com"
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
        "startDate": "2017-06-01",
        "endDate": "YESTERDAY"
    },
    "metricNames": [
      "dfa:cookieReachClickReach",
      "dfa:cookieReachImpressionReach"
    ],
    "dimension": "CAMPAIGN",
    "breakdown": [
      {
        "name": "dfa:date",
        "kind": "dfareporting#sortedDimension"
      }
    ],
    "overlapMetricNames": [
      "dfa:cookieReachOverlapClickReach"
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

