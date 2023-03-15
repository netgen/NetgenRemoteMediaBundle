<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Tests\Core\Provider\Cloudinary\Verifier\Request;

use Cloudinary;
use Cloudinary\SignatureVerifier as CloudinarySignatureVerifier;
use Netgen\RemoteMedia\Core\Provider\Cloudinary\Verifier\Request\Signature as SignatureVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

use function json_encode;
use function time;

#[CoversClass(SignatureVerifier::class)]
class SignatureTest extends TestCase
{
    private const CLOUD_NAME = 'testcloud';

    private const API_KEY = 'apikey';

    private const API_SECRET = 'secret';

    private SignatureVerifier $signatureVerifier;

    protected function setUp(): void
    {
        $cloudinary = new Cloudinary();
        $cloudinary->config(
            [
                'cloud_name' => self::CLOUD_NAME,
                'api_key' => self::API_KEY,
                'api_secret' => self::API_SECRET,
                'cdn_subdomain' => true,
            ],
        );

        $this->signatureVerifier = new SignatureVerifier();
    }

    public function testVerifyValid(): void
    {
        $body = json_encode([
            'notification_type' => 'upload',
            'timestamp' => '2020-12-16T12:09:39+00:00',
            'request_id' => '71763d4cacf19521f5691a02c8b143b1',
            'asset_id' => 'ede59e6d3befdc65a8adc2f381c0f96f',
            'public_id' => 'sample',
            'version' => 1608120578,
            'version_id' => '3144395a27aa6c02df1ca8aaf9aa6e7a',
            'width' => 1279,
            'height' => 853,
            'format' => 'jpg',
            'resource_type' => 'image',
            'created_at' => '2020-12-16T12:09:38Z',
            'tags' => [],
            'bytes' => 380250,
            'type' => 'upload',
            'etag' => '0b40494da087cba7092d29c58aede2e2',
            'placeholder' => false,
            'url' => 'http://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'original_filename' => 'jeans-1421398-1279x852',
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $timestamp = time();
        $payloadToSign = $body . $timestamp;
        $signature = CloudinarySignatureVerifier::generateHmac($payloadToSign, self::API_SECRET);

        $request->headers->add(
            [
                'x-cld-timestamp' => $timestamp,
                'x-cld-signature' => $signature,
            ],
        );

        self::assertTrue(
            $this->signatureVerifier->verify($request),
        );
    }

    public function testVerifyInvalidRequest(): void
    {
        $signatureVerifier = new SignatureVerifier();
        self::assertFalse(
            $signatureVerifier->verify(new Request()),
        );
    }

    public function testVerifyInvalidSgnature(): void
    {
        $body = json_encode([
            'notification_type' => 'upload',
            'timestamp' => '2020-12-16T12:09:39+00:00',
            'request_id' => '71763d4cacf19521f5691a02c8b143b1',
            'asset_id' => 'ede59e6d3befdc65a8adc2f381c0f96f',
            'public_id' => 'sample',
            'version' => 1608120578,
            'version_id' => '3144395a27aa6c02df1ca8aaf9aa6e7a',
            'width' => 1279,
            'height' => 853,
            'format' => 'jpg',
            'resource_type' => 'image',
            'created_at' => '2020-12-16T12:09:38Z',
            'tags' => [],
            'bytes' => 380250,
            'type' => 'upload',
            'etag' => '0b40494da087cba7092d29c58aede2e2',
            'placeholder' => false,
            'url' => 'http://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/v1608120578/sample.jpg',
            'original_filename' => 'jeans-1421398-1279x852',
        ]);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body,
        );

        $timestamp = time();

        $request->headers->add(
            [
                'x-cld-timestamp' => $timestamp,
                'x-cld-signature' => 'test',
            ],
        );

        self::assertFalse(
            $this->signatureVerifier->verify($request),
        );
    }
}
