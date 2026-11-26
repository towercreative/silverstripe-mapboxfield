<?php

namespace Bigfork\SilverStripeMapboxField\Tests;

use Bigfork\SilverStripeMapboxField\LeftAndMainExtension;
use Bigfork\SilverStripeMapboxField\MapboxField;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\View\Requirements;

class MapboxFieldTest extends SapphireTest
{
    protected $usesDatabase = false;

    protected function tearDown(): void
    {
        Requirements::clear();
        parent::tearDown();
    }

    public function testCreatesHiddenCoordinateFields(): void
    {
        $field = MapboxField::create('LocationMap', 'Choose a location', 'Latitude', 'Longitude');

        $this->assertSame('LocationMap', $field->getName());
        $this->assertSame('Choose a location', $field->Title());
        $this->assertStringContainsString('stacked', $field->extraClass());
        $this->assertSame('Latitude', $field->fieldByName('Latitude')->getAttribute('data-mapbox-field'));
        $this->assertSame('Longitude', $field->fieldByName('Longitude')->getAttribute('data-mapbox-field'));
    }

    public function testReturnsConfiguredMapStyle(): void
    {
        $style = 'mapbox://styles/example/custom-style';
        Config::modify()->set(MapboxField::class, 'style', $style);

        $field = MapboxField::create('LocationMap', 'Choose a location', 'Latitude', 'Longitude');

        $this->assertSame($style, $field->getMapboxStyle());
    }

    public function testRegistersMapboxAssetsAndEscapesAccessToken(): void
    {
        $token = "token';</script><script>alert('xss')</script>";
        Config::modify()->set(MapboxField::class, 'access_token', $token);
        Config::modify()->set(MapboxField::class, 'api_css_url', 'https://example.test/mapbox.css');
        Config::modify()->set(MapboxField::class, 'api_javascript_url', 'https://example.test/mapbox.js');
        Config::modify()->set(MapboxField::class, 'geocoder_css_url', 'https://example.test/geocoder.css');
        Config::modify()->set(MapboxField::class, 'geocoder_javascript_url', 'https://example.test/geocoder.js');

        (new LeftAndMainExtension())->onAfterInit();

        $this->assertArrayHasKey('https://example.test/mapbox.css', Requirements::backend()->getCSS());
        $this->assertArrayHasKey('https://example.test/geocoder.css', Requirements::backend()->getCSS());
        $this->assertArrayHasKey('https://example.test/mapbox.js', Requirements::backend()->getJavascript());
        $this->assertArrayHasKey('https://example.test/geocoder.js', Requirements::backend()->getJavascript());

        $scripts = Requirements::get_custom_scripts();
        $this->assertArrayHasKey('bigfork-mapboxfield-access-token', $scripts);
        $this->assertStringNotContainsString('</script>', $scripts['bigfork-mapboxfield-access-token']);
        $this->assertStringContainsString('window.mapboxAccessToken', $scripts['bigfork-mapboxfield-access-token']);
    }
}
