<?php
/**
 * GoogleAuthenticator
 *
 *
 * @package  GoogleAuthenticator
 * @author   Leandro Lugaresi <leandrolugaresi92@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License 3
 * @link     https://github.com/johnstyle/google-authenticator
 */

namespace GoogleAuthenticator;

use Base32\Base32;
use Zend\Math\Rand;

/**
 * Class GoogleAuthenticator
 *
 * @author   Leandro Lugaresi <leandrolugaresi92@gmail.com>
 */
class GoogleAuthenticator
{
    const API_URL = 'https://chart.googleapis.com/chart?chs={chs}&chld=M|0&cht=qr&chl={chl}';
    const CODE_LENGTH = 6;
    const SECRET_LENGTH = 16;

    /** @var string $secretKey */
    protected $secretKey;

    /** @var array $base32Chars */
    protected $base32Chars= array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
        'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
        'Y', 'Z', '2', '3', '4', '5', '6', '7',
        '='
    );

    /**
     * @param  string $secretKey
     * @throws GoogleAuthenticatorException
     */
    public function __construct($secretKey = null)
    {
        $this->secretKey = $secretKey;

        if (is_null($this->secretKey)) {
            $this->secretKey = $this->generateSecretKey();
        }

        if (static::SECRET_LENGTH !== strlen($this->secretKey)
            || 0 !== count(array_diff(str_split($this->secretKey), $this->base32Chars))) {

            throw new GoogleAuthenticatorException('Invalid secret key');
        }
    }

    /**
     * @return string
     */
    public function generateSecretKey()
    {
        $base32Chars = $this->base32Chars;

        unset($base32Chars[32]);

        $secretKey = '';

        for ($i = 0; $i < static::SECRET_LENGTH; $i++) {
            $key = Rand::getInteger(0,count($base32Chars)-1);
            $secretKey .= $base32Chars[$key];
        }

        return $secretKey;
    }

    /**
     * @param  string $applicationName
     * @param  int    $size
     * @return mixed
     */
    public function getQRCodeUrl($applicationName, $size = 200)
    {
        return str_replace(
            array(
                '{chs}',
                '{chl}'
            ),
            array(
                $size . 'x' . $size,
                urlencode('otpauth://totp/' . $applicationName . '?secret=' . $this->getSecretKey())
            ),
            static::API_URL
        );
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     * @return GoogleAuthenticator
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now
     *
     * @param  string $code
     * @param  int    $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     * @return bool
     */
    public function verifyCode($code, $discrepancy = 1)
    {
        $currentTimeSlice = $this->getTimeIndex();

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($currentTimeSlice + $i);
            if ($calculatedCode == $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCode($timeSlice = null)
    {
        $secretKey = Base32::decode($this->secretKey);

        if ($timeSlice === null) {
            $timeSlice = $this->getTimeIndex();
        }

        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretKey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, static::CODE_LENGTH);

        return str_pad($value % $modulo, static::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * @return int
     */
    public function getTimeIndex()
    {
        return floor(time() / 30);
    }
}
