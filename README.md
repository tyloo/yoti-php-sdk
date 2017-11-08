Yoti PHP SDK
=============

Welcome to the Yoti PHP SDK. This repo contains the tools you need to quickly integrate your PHP back-end with Yoti, so that your users can share their identity details with your application in a secure and trusted way.

## Table of Contents

1) [An Architectural view](#an-architectural-view) -
High level overview of integration

2) [Requirements](#requirements)-
Check you have what you need

3) [Enabling the SDK](#enabling-the-sdk)-
How to install our SDK

4) [Client initialisation](#client-initialisation)-
How to initialise your configuration

5) [Profile Retrieval](#profile-retrieval)-
How to retrieve a Yoti profile using the token

6) [Handling users](#handling-users)-
How to manage users

7) [How to run the example](#how-to-run-the-example)-
How to run the example

8) [Support](#support)-
Please feel free to reach out

## An Architectural View
To integrate your application with Yoti, your back-end must expose a GET endpoint that Yoti will use to forward tokens.
The endpoint can be configured in Yoti Dashboard when you create/update your application.

The image below shows how your application back-end and Yoti integrate in the context of a Login flow.
Yoti SDK carries out steps 6 through 9 for you, including profile decryption and communication with backend services.

![alt text](login_flow.png "Login flow")


Yoti also allows you to enable user details verification from your mobile app by means of the Android (TBA) and iOS (TBA) SDKs. In that scenario, your Yoti-enabled mobile app is playing both the role of the browser and the Yoti app. By the way, your back-end doesn't need to handle these cases in a significantly different way. You might just decide to handle the `User-Agent` header in order to provide different responses for web and mobile clients.
   
## Requirements

* PHP 5.3
* CURL PHP extension

## Enabling the SDK
To import the Yoti SDK inside your project, you can use your favourite dependency management system.
If you are using Composer, you need to do one of the following:

Add the Yoti SDK dependency:

```json
"require": {
    "yoti/yoti-php-sdk" : "1.0.*"
}
```
Or run this Composer command
`composer require yoti/yoti-php-sdk`

## Client Initialisation
The YotiClient is the SDK entry point. To initialise it you need include the following snippet inside your endpoint initialisation section:
```php
<?php
require_once './vendor/autoload.php';
$client = new \Yoti\YotiClient('SDK_ID', 'path/to/your-application-pem-file.pem');
```
Where:
* `YOUR_SDK_ID` is the identifier generated by Yoti Dashboard when you create your app.
* `PATH/TO/YOUR/APPLICATION/KEY_PAIR.pem` is the path to the pem file your browser generates for you, when you create your app on Yoti Dashboard.


## Profile Retrieval
When your application receives a token via the exposed endpoint (it will be assigned to a query string parameter named `token`), you can easily retrieve the user profile by adding the following to your endpoint handler:

```php
<?php
$activityDetails = $client->getActivityDetails();
```
Before you inspect the user profile, you might want to check whether the user validation was successful.
This is done as follows:

```php
<?php
$activityDetails = $client->getActivityDetails();
if ($client->getOutcome() == \Yoti\YotiClient::OUTCOME_SUCCESS)
{
    $profile = $activityDetails->getProfileAttribute();
}
else
{
    // handle unhappy path
}
``` 

### Available User Profile Attributes Through Getters
We have exposed user profile attributes through getters. You will find in the example below the getters available to you and how to use them:

```php
<?php
$activityDetails    = $client->getActivityDetails();

$userId             = $activityDetails->getUserId();

$familyName         = $activityDetails->getFamilyName();

$givenName          = $activityDetails->getGivenNames();

$dateOfBirth        = $activityDetails->getDateOfBirth();

$gender             = $activityDetails->getGender();

$nationality        = $activityDetails->getNationality();

$phoneNumber        = $activityDetails->getPhoneNumber();

$selfie             = $activityDetails->getSelfie();

$emailAddress       = $activityDetails->getEmailAddress();

$postalAddress      = $activityDetails->getPostalAddress();
```

## Handling Users
When you retrieve the user profile, you receive a user ID generated by Yoti exclusively for your application.
This means that if the same individual logs into another app, Yoti will assign her/him a different ID.
You can use this ID to verify whether (for your application) the retrieved profile identifies a new or an existing user.
Here is an example of how this works:

```php
<?php
$activityDetails = $client->getActivityDetails();
if ($client->getOutcome() == \Yoti\YotiClient::OUTCOME_SUCCESS) {
    $user = yourUserSearchFunction($activityDetails->getUserId());
    if ($user) {
        // handle login
    } else {
        // handle registration
    }
} else {
    // handle unhappy path
}
```
Where `yourUserSearchMethod` is a piece of logic in your app that is supposed to find a user, given a userId. 
No matter if the user is a new or an existing one, Yoti will always provide her/his profile, so you don't necessarily need to store it.

The `ActivityDetails` class provides a set of methods to retrieve different user attributes. Whether the attributes are present or not depends on the settings you have applied to your app on Yoti Dashboard.

#### ActivityDetails
The set of attributes the user has configured for the transaction.

#### YotiClient

Allows your app to retrieve a user profile, given an encrypted token.

## How to Run the Example

The example can be found in the [example folder](https://github.com/getyoti/php/tree/master/example). The steps required for the setup are explained below.

- Create your application in the Yoti Dashboard (this requires having a Yoti account)
- Fill in the YOTI_APPLICATION_ID and YOTI_SCENARIO_ID values in the index.html file 
- Fill in the SDK_ID and pem file path config details in the profile.php file
- Point your Yoti application callback URL to http://your-local-url.domain/profile.php
- Run `composer update` command inside [example folder](https://github.com/getyoti/php/tree/master/example) 
 
## Support

For any questions or support please email [sdksupport@yoti.com](mailto:sdksupport@yoti.com).
Please provide the following the get you up and working as quick as possible:

- Computer Type
- OS Version
- Version of PHP being used
- Screenshot

