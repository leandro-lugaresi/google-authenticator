google-authenticator
====================

Repository to integrate web sites and Google Authenticator

##Usage

###Step 1 - Register application

    ```PHP
    $google = new GoogleAuthenticator();

    // Register application
    echo $google->getQRCodeUrl('ApplicationName');

    // Save secret Key
    $secretKey = $google->getSecretKey();


###Step 2 - Verify Code

    ```PHP
    $google = new GoogleAuthenticator($secretKey);

    // User submit code
    $userSubmitCode = '';

    // Verify Code
    if ($google->verifyCode($userSubmitCode)) {

        // OK
    }

##Demonstration
soon!