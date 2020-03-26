<?php

use GuzzleHttp\Psr7\Request;
use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\RequestCurlFormatter;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class RequestCurlFormatterTest extends TestCase
{
    /**
     * @var AbstractRequestFormatter
     */
    protected $formatter;

    public function setUp () : void {
        $this->formatter = new RequestCurlFormatter();
    }

    public function testMultiLineDisabled () {
        $this->formatter->setCommandLineLength(10);

        $request = new Request('GET', 'http://example.local', [ 'foo' => 'bar' ]);
        $curl = $this->formatter->format($request);

        $this->assertEquals(substr_count($curl, "\n"), 2);
    }

    public function testSkipHostInHeaders () {
        $request = new Request('GET', 'http://example.local');
        $curl = $this->formatter->format($request);

        $this->assertEquals("curl 'http://example.local'", $curl);
    }

    public function testSimpleGET () {
        $request = new Request('GET', 'http://example.local');
        $curl = $this->formatter->format($request);

        $this->assertEquals("curl 'http://example.local'", $curl);
    }

    public function testSimpleGETWithHeader () {
        $request = new Request('GET', 'http://example.local', [ 'foo' => 'bar' ]);
        $curl = $this->formatter->format($request);

        $this->assertEquals("curl 'http://example.local' -H 'foo: bar'", $curl);
    }

    public function testSimpleGETWithMultipleHeader () {
        $request = new Request('GET', 'http://example.local',
            [ 'foo' => 'bar', 'Accept-Encoding' => 'gzip,deflate,sdch' ]);
        $curl = $this->formatter->format($request);

        $this->assertEquals("curl 'http://example.local' -H 'foo: bar' -H 'Accept-Encoding: gzip,deflate,sdch'", $curl);
    }

    public function testGETWithQueryString () {
        $request = new Request('GET', 'http://example.local?foo=bar');
        $curl = $this->formatter->format($request);

        $this->assertEquals("curl 'http://example.local?foo=bar'", $curl);

        $request = new Request('GET', 'http://example.local?foo=bar');
        $curl = $this->formatter->format($request);

        $this->assertEquals("curl 'http://example.local?foo=bar'", $curl);

        $body = stream_for(http_build_query([ 'foo' => 'bar', 'hello' => 'world' ], '', '&'));

        $request = new Request('GET', 'http://example.local', [], $body);
        $curl = $this->formatter->format($request);

        $this->assertEquals("curl 'http://example.local' -G  -d 'foo=bar&hello=world'", $curl);

    }

    public function testPOST () {
        $body = stream_for(http_build_query([ 'foo' => 'bar', 'hello' => 'world' ], '', '&'));

        $request = new Request('POST', 'http://example.local', [], $body);
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $curl);
        $this->assertStringNotContainsString(" -G ", $curl);
    }

    public function testHEAD () {
        $request = new Request('HEAD', 'http://example.local');
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("--head", $curl);
    }

    public function testOPTIONS () {
        $request = new Request('OPTIONS', 'http://example.local');
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-X OPTIONS", $curl);
    }

    public function testDELETE () {
        $request = new Request('DELETE', 'http://example.local/users/4');
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-X DELETE", $curl);
    }

    public function testPUT () {
        $request = new Request('PUT', 'http://example.local', [], stream_for('foo=bar&hello=world'));
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $curl);
        $this->assertStringContainsString("-X PUT", $curl);
    }

    public function testUserAgent () {
        $request = new Request('GET', 'http://example.local', [
            'user-agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        ]);
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-A 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'", $curl);
    }

    public function testProperBodyReading () {
        $request = new Request('PUT', 'http://example.local', [], stream_for('foo=bar&hello=world'));
        $request->getBody()->getContents();

        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $curl);
        $this->assertStringContainsString("-X PUT", $curl);
    }

    /**
     * @dataProvider getHeadersAndBodyData
     */
    public function testExtractBodyArgument ($headers, $body) {
        // clean input of null bytes
        $body = str_replace(chr(0), '', $body);
        $request = new Request('POST', 'http://example.local', $headers, stream_for($body));

        $curl = $this->formatter->format($request);

        $this->assertStringContainsString('foo=bar&hello=world', $curl);
    }

    /**
     * The data provider for testExtractBodyArgument
     *
     * @return array
     */
    public function getHeadersAndBodyData () {
        return [
            [
                [ 'X-Foo' => 'Bar' ],
                chr(0) . 'foo=bar&hello=world',
            ],
        ];
    }
}