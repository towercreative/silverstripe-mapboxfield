<?php

namespace Bigfork\SilverStripeMapboxField;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

class LeftAndMainExtension extends Extension
{
    public function onAfterInit()
    {
        $config = MapboxField::config();
        Requirements::css($config->api_css_url);
        Requirements::javascript($config->api_javascript_url);
        Requirements::css($config->geocoder_css_url);
        Requirements::javascript($config->geocoder_javascript_url);
        $accessToken = json_encode(
            $config->access_token,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        );
        Requirements::customScript(
            <<<JS
window.mapboxAccessToken = {$accessToken};
JS
            ,
            'bigfork-mapboxfield-access-token'
        );
    }
}
