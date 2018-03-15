google-authenticator
====================

> [DEPRECATED] use the [antonioribeiro/google2fa](https://github.com/antonioribeiro/google2fa) instead!

[![Latest Stable Version](https://poser.pugx.org/leandrolugaresi/google-authenticator/v/stable.svg)](https://packagist.org/packages/leandrolugaresi/google-authenticator) [![Total Downloads](https://poser.pugx.org/leandrolugaresi/google-authenticator/downloads.svg)](https://packagist.org/packages/leandrolugaresi/google-authenticator) [![Latest Unstable Version](https://poser.pugx.org/leandrolugaresi/google-authenticator/v/unstable.svg)](https://packagist.org/packages/leandrolugaresi/google-authenticator) [![License](https://poser.pugx.org/leandrolugaresi/google-authenticator/license.svg)](https://packagist.org/packages/leandrolugaresi/google-authenticator)
[ ![Codeship Status for leandro-lugaresi/google-authenticator](https://www.codeship.io/projects/c56f02e0-2489-0132-ed0d-5e8cf715c71c/status?branch=master)](https://www.codeship.io/projects/36901)

Introduction
------------

This is a module to integrate web sites with Google Authenticator.

Requirements
------------

* [ChristianRiesen/base32](https://github.com/ChristianRiesen/base32) (1.2)
* [zendframework/zend-math](https://github.com/zendframework/zf2) (>2.2.*)

Installation
------------

1. Add this project in your composer.json:

```json
    "require": {
        "leandrolugaresi/google-authenticator": "1.0.*"
    }
```

2. Now tell the composer to download the repository by running the command:

```bash
    $ php composer.phar update
```

Usage
-----

### Step 1 - Register application

Show the QrCode and the form:

```php
    $googleAuth = new \GoogleAuthenticator\GoogleAuthenticator();
    $googleAuth->setIssuer('YourApplicationName');
    //save the secretKey to register after
    $_SESSION['secretKeyTemp'] = $googleAuth->getSecretKey();

    // Show the qrcode to register
    //this param is an identifier of the user in this application
    echo $googleAuth->getQRCodeUrl($user->username.'@YourApplicationName');
```

Verify the code from form and save the secretKey of this user:

```php
    $google = new GoogleAuthenticator($_SESSION['secretKeyTemp']);
    $userSubmitCode = $_POST['codeFoo'];
    if ($google->verifyCode($userSubmitCode)) {
        //save the secretKey of this user
    }
```

### Step 2 - Verify Code at login

```PHP
    $google = new GoogleAuthenticator($user->getSecretKey());
    $userSubmitCode = $_POST['codeFoo'];

    // Verify Code
    if ($google->verifyCode($userSubmitCode)) {

        // OK - aloowed login
    }
```
