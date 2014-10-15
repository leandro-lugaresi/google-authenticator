<?php

namespace GoogleAuthenticatorTest;

use GoogleAuthenticator\GoogleAuthenticator;

/**
 * Override time() in current namespace for testing
 * see http://www.schmengler-se.de/en/2011/03/php-mocking-built-in-functions-like-time-in-unit-tests/
 *
 * @return int
 */
function time()
{
    return GoogleAuthenticatorTest::$now ?: time();
}

class GoogleAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var $googleAuthenticator GoogleAuthenticator */
    protected $googleAuthenticator;

    /**
     * Timestamp that will be returned by time()
     *
     * @var int $now
     */
    public static $now;

    protected function setUp()
    {
        $this->googleAuthenticator = new GoogleAuthenticator();
    }

    public function codeProvider()
    {
        // Secret, time, code
        return array(
            array('SECRET', 0, '857148'),
            array('SECRET', 1385909245, '979377'),
            array('SECRET', 1378934578, '560773'),
            array('SECRET2', 1378934578, '394728'),
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
        $this->assertEquals(16, strlen($secret));
    }

    /**
     * @dataProvider codeProvider
     */
    public function testgetCodeReturnsCorrectValues($secret, $time, $code)
    {
        static::$now = $time;

        $this->googleAuthenticator->setSecretKey($secret);
        $generatedCode = $this->googleAuthenticator->getCode($time);

        $this->assertEquals($code, $generatedCode, 'Invalid code for ' . $secret . ' at ' . $time);
    }

    public function testgetQRCodeUrlReturnsCorrectUrl()
    {
        $secret = 'SECRET';
        $name   = 'Test';
        $url = $this->googleAuthenticator->setSecretKey($secret)->getQRCodeUrl($name);

        $urlParts = parse_url($url);
        parse_str($urlParts['query'], $queryStringArray);

        $this->assertEquals('https', $urlParts['scheme']);
        $this->assertEquals('chart.googleapis.com', $urlParts['host']);
        $this->assertEquals('/chart', $urlParts['path']);

        $expectedChl = 'otpauth://totp/' . $name . '?secret=' . $secret;
        $this->assertEquals($expectedChl, $queryStringArray['chl']);
    }

    public function testgetQRCodeUrlReturnsCorrectUrlWhenIssuerSet()
    {
        $secret = 'SECRET';
        $name   = 'Test';
        $issuer = 'Test Co';
        $url = $this->googleAuthenticator
            ->setSecretKey($secret)
            ->setIssuer($issuer)
            ->getQRCodeUrl($name);

        $urlParts = parse_url($url);
        parse_str($urlParts['query'], $queryStringArray);

        $expectedChl = 'otpauth://totp/' . $name . '?secret=' . $secret . '&issuer=' . urlencode($issuer);
        $this->assertEquals($expectedChl, $queryStringArray['chl']);
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
