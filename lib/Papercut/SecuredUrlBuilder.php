<?php

class SecuredUrlBuilder {

    /**
     * @param $params The params to hash.
     * @param $secureKey The key used to generate the hash.
     * @return string The hash calculated from the params.
     */
    public static function getHash($params, $secureKey) {
        // Remove any existing hash
        unset($params["cs"]);

        // Sort so that hash is not dependent on parameter order
        ksort($params);

        $s = "";
        foreach ($params as $key => $value) {
            $s .= $key . "=" . $value . "|";
        }

        return hash_hmac('sha256', $s, $secureKey);
    }

    /**
     * Returns a new array with the hash appended.
     * @param $params The params to create hash for.
     * @param $key The key used to generate the hash.
     * @return mixed The new params array that includes the hash.
     */
    public static function appendHash($params, $key) {
        $params["cs"] = self::getHash($params, $key);
        return $params;
    }

    /**
     * Returns a new params array with timestamp and hash appended.
     * @param $params array The params to append to.
     * @param $key The key used to generate the hash.
     * @param $time Used to pass in a timestamp for testing only.  If not provided current time is used.
     * @return mixed The params with timestamp and checksum appended.
     */
    public static function appendSecuredTimestamp($params, $key, $time = null) {
        $params["ct"] = isset($time) ? $time : time();
        return self::appendHash($params, $key);
    }

    public static function buildUrl($baseUrl, $params) {
        $qs = "";
        foreach ($params as $key => $value) {
            if ($qs) {
                $qs .= '&';
            }
            $qs .= $key . '=' . urlencode($value);
        }
        //echo $baseUrl . '?' . $qs;
        return $baseUrl . '?' . $qs;
    }
}
?>