<?php
include 'SecuredUrlBuilder.php';
Class PaperCutResellerUrlBuilder {
    /**
     * Creates a URL to automatically login as a reseller into the PaperCut order system. Upon completion
     * of the quotation the order data will be POSTed back to the return-url.  The order data is stored in
     * JSON in the "orderJson parameter.
     *
     * If the user cancels the order process, they will be returned to the return-url as a GET request.
     *
     * NOTE: The generated URL will only be valid for 20 seconds after generation.  Only generate the URL just
     * prior to directing the user to the site.  Also ensure your server clock is accurate.
     *
     * @param $authId The auth id to connect to the site (provided to you by PaperCut).
     * @param $resellerLogin The PaperCut reseller portal login to login as.
     * @param $returnUrl The full URL to return to on cancellation or completion.
     * @param $key The security key to secure the URLs (provided to you by PaperCut).
     * @param $orderSystemBaseUrl (Optional) Leave blank.
     * @return string The URL to auto login the user.
     */
    public static function create($authId, $resellerLogin, $returnUrl, $key, $orderSystemBaseUrl = "http://www.papercut.com/buy/") {
        $params = array("auth-id" => $authId,
                        "reseller-login" => $resellerLogin,
                        "return-url" => $returnUrl);

        $params = SecuredUrlBuilder::appendSecuredTimestamp($params, $key);

        return SecuredUrlBuilder::buildUrl($orderSystemBaseUrl . "reseller_login.php", $params);
    }
}