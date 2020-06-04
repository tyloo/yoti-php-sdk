<?php

declare(strict_types=1);

namespace Yoti\Test;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Yoti\Aml\Address as AmlAddress;
use Yoti\Aml\Country as AmlCountry;
use Yoti\Aml\Profile as AmlProfile;
use Yoti\Aml\Result as AmlResult;
use Yoti\Profile\ActivityDetails;
use Yoti\ShareUrl\DynamicScenarioBuilder;
use Yoti\ShareUrl\Policy\DynamicPolicyBuilder;
use Yoti\ShareUrl\Result as ShareUrlResult;
use Yoti\Util\Config;
use Yoti\YotiClient;

use function GuzzleHttp\Psr7\stream_for;

/**
 * @coversDefaultClass \Yoti\YotiClient
 */
class YotiClientTest extends TestCase
{
    private const SOME_ENV_URL = 'https://example.com/env/api';
    private const SOME_OPTION_URL = 'https://example.com/option/api';

    /**
     * Test empty SDK ID
     *
     * @covers ::__construct
     */
    public function testEmptySdkId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SDK ID cannot be empty');

        new YotiClient('', TestData::PEM_FILE);
    }

    /**
     * @test
     * @covers ::__construct
     */
    public function testDefaultApiUrl()
    {
        $this->assertApiUrlStartsWith(TestData::CONNECT_BASE_URL);
    }

    /**
     * @test
     * @covers ::__construct
     * @backupGlobals enabled
     */
    public function testApiUrlOptionOverridesEnvironmentVariable()
    {
        $_SERVER['YOTI_API_URL'] = self::SOME_ENV_URL;
        $this->assertApiUrlStartsWith(self::SOME_OPTION_URL, self::SOME_OPTION_URL);
    }

    /**
     * @test
     * @covers ::__construct
     * @backupGlobals enabled
     */
    public function testApiUrlEnvironmentVariable()
    {
        $_SERVER['YOTI_API_URL'] = self::SOME_ENV_URL;
        $this->assertApiUrlStartsWith(self::SOME_ENV_URL);
    }

    /**
     * @test
     * @covers ::__construct
     * @backupGlobals enabled
     */
    public function testEmptyApiUrlEnvironmentVariable()
    {
        $_SERVER['YOTI_API_URL'] = '';
        $this->assertApiUrlStartsWith(TestData::CONNECT_BASE_URL);
    }

    /**
     * Asserts API URL starts with expected URL.
     *
     * @param string $expectedUrl
     * @param string $clientApiUrl
     */
    private function assertApiUrlStartsWith($expectedUrl, $clientApiUrl = null)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(stream_for(file_get_contents(TestData::AML_CHECK_RESULT_JSON)));
        $response->method('getStatusCode')->willReturn(200);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->exactly(1))
            ->method('sendRequest')
            ->with($this->callback(function ($requestMessage) use ($expectedUrl) {
                $this->assertStringStartsWith(
                    $expectedUrl,
                    (string) $requestMessage->getUri()
                );
                return true;
            }))
            ->willReturn($response);

        $yotiClient = new YotiClient(TestData::SDK_ID, TestData::PEM_FILE, [
            Config::HTTP_CLIENT => $httpClient,
            Config::API_URL => $clientApiUrl,
        ]);

        $yotiClient->performAmlCheck($this->createMock(AmlProfile::class));
    }

    /**
     * @covers ::getActivityDetails
     * @covers ::__construct
     */
    public function testGetActivityDetails()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(file_get_contents(TestData::RECEIPT_JSON));
        $response->method('getStatusCode')->willReturn(200);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->exactly(1))
            ->method('sendRequest')
            ->willReturn($response);

        $yotiClient = new YotiClient(TestData::SDK_ID, TestData::PEM_FILE, [
            Config::HTTP_CLIENT => $httpClient,
        ]);

        $this->assertInstanceOf(
            ActivityDetails::class,
            $yotiClient->getActivityDetails(file_get_contents(TestData::YOTI_CONNECT_TOKEN))
        );
    }

    /**
     * @covers ::performAmlCheck
     * @covers ::__construct
     */
    public function testPerformAmlCheck()
    {
        $amlAddress = new AmlAddress(new AmlCountry('GBR'));
        $amlProfile = new AmlProfile('Edward Richard George', 'Heath', $amlAddress);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(stream_for(file_get_contents(TestData::AML_CHECK_RESULT_JSON)));
        $response->method('getStatusCode')->willReturn(200);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->exactly(1))
            ->method('sendRequest')
            ->willReturn($response);

        $yotiClient = new YotiClient(TestData::SDK_ID, TestData::PEM_FILE, [
            Config::HTTP_CLIENT => $httpClient,
        ]);

        $result = $yotiClient->performAmlCheck($amlProfile);

        $this->assertInstanceOf(AmlResult::class, $result);
    }

    /**
     * @covers ::createShareUrl
     * @covers ::__construct
     */
    public function testCreateShareUrl()
    {
        $dynamicScenario = (new DynamicScenarioBuilder())
            ->withCallbackEndpoint('/test-callback-url')
            ->withPolicy(
                (new DynamicPolicyBuilder())->build()
            )
            ->build();

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(stream_for(json_encode([
            'qrcode' => 'http://dynamic-code.yoti.com/some-qr-code',
            'ref_id' => 'some-ref-id',
        ])));
        $response->method('getStatusCode')->willReturn(201);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $yotiClient = new YotiClient(TestData::SDK_ID, TestData::PEM_FILE, [
            Config::HTTP_CLIENT => $httpClient,
        ]);

        $result = $yotiClient->createShareUrl($dynamicScenario);

        $this->assertInstanceOf(ShareUrlResult::class, $result);
    }

    public function testGetLoginUrl()
    {
        $someAppId = 'some-app-id';

        $this->assertEquals("https://www.yoti.com/connect/{$someAppId}", YotiClient::getLoginUrl($someAppId));
    }
}
