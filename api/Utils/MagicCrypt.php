<?php

namespace App\Utils;


class MagicCrypt {

    private $iv = "0102030405060708";//密钥偏移量IV，可自定义

    private $encryptKey;

    private $enGzip = false;

    /**
     * MagicCrypt constructor.
     * @param $encryptKey
     * @param bool $enGzip
     */
    public function __construct($encryptKey, $enGzip = false)
    {
        $this->encryptKey = $encryptKey;
        $this->enGzip = $enGzip;
    }


    //加密
    public function encrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;

        if (true == $this->enGzip)   $encryptStr = gzencode($encryptStr);

        //Open module
        $module = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, $localIV);

        @mcrypt_generic_init($module, $encryptKey, $localIV);

        //Padding
        $block = @mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($encryptStr) % $block); //Compute how many characters need to pad
        $encryptStr .= str_repeat(chr($pad), $pad); // After pad, the str length must be equal to block or its integer multiples

        //encrypt
        $encrypted = @mcrypt_generic($module, $encryptStr);

        //Close
        @mcrypt_generic_deinit($module);
        @mcrypt_module_close($module);

        return $encrypted;
    }

    //解密
    public function decrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;

        //Open module
        $module = @mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, $localIV);

        @mcrypt_generic_init($module, $encryptKey, $localIV);

        $encryptedData = @mdecrypt_generic($module, $encryptStr);

        if (true == $this->enGzip)   $encryptedData = gzdecode($encryptedData);

        return $encryptedData;
    }
}