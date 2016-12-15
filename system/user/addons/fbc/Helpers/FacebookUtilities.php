<?php

namespace Solspace\Addons\Fbc\Helpers;

class FacebookUtilities
{
    /**
     * This is testing to see if the email address is an MD5 hash plus @facebook.com.
     * It's the fake email format I use when someone passively registers.
     *
     * @param string $email
     *
     * @return bool
     */
    public static function isFacebookEmail($email)
    {
        return preg_match('/^[a-f0-9]{32}@facebook\.com/si', $email);
    }
}
