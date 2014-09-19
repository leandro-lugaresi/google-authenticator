<?php

namespace GoogleAuthenticatorTest;

use GoogleAuthenticator\GoogleAuthenticator;

class GoogleAuthenticatorTest extends PHPUnit_Framework_TestCase
{
    /* @var $googleAuthenticator GoogleAuthenticator */
    protected $googleAuthenticator;

    protected function setUp()
    {
        $this->googleAuthenticator = new GoogleAuthenticator();
    }

    public function codeProvider()
    {
        // Secret, time, code
        return array(
            array('SECRET', '0', '200470'),
            array('SECRET', '1385909245', '780018'),
            array('SECRET', '1378934578', '705013'),
        );
    }

    public function testItCanBeInstantiated()
    {
        $ga = new GoogleAuthenticator();
        $this->assertInstanceOf('GoogleAuthenticator\GoogleAuthenticator', $ga);
    }

    public function testCreateSecretDefaultsToSixteenCharacters()
    {
        $ga = $this->googleAuthenticator;
        $secret = $ga->generateSecretKey();
        $this->assertEquals(strlen($secret), 16);
    }

    /**
     * @dataProvider codeProvider
     */
    public function testgetCodeReturnsCorrectValues($secret, $timeSlice, $code)
    {
        $generatedCode = $this->googleAuthenticator->getCode($secret, $timeSlice);

        $this->assertEquals($code, $generatedCode);
    }

    public function testgetQRCodeUrlReturnsCorrectUrl()
    {
        $secret = 'SECRET';
        $name   = 'Test';
        $url = $this->googleAuthenticator->setSecretKey($secret)->getQRCodeUrl($name);

        $urlParts = parse_url($url);
        parse_str($urlParts['query'], $queryStringArray);

        $this->assertEquals($urlParts['scheme'], 'https');
        $this->assertEquals($urlParts['host'], 'chart.googleapis.com');
        $this->assertEquals($urlParts['path'], '/chart');

        $expectedChl = 'otpauth://totp/' . $name . '?secret=' . $secret;
        $this->assertEquals($queryStringArray['chl'], $expectedChl);
    }

    public function testVerifyCode()
    {
        $secret = 'SECRET';
        $this->googleAuthenticator->setSecretKey($secret);

        $code = $this->googleAuthenticator->getCode();
        $result = $this->googleAuthenticator->verifyCode($code);
        $this->assertEquals(true, $result);

        $code = 'INVALIDCODE';
        $result = $this->googleAuthenticator->verifyCode($code);
        $this->assertEquals(false, $result);

    }
}
