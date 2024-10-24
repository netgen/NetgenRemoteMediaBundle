<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Factory;

use Netgen\RemoteMedia\API\Factory\FileHash as FileHashFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

use function count;
use function curl_close;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function explode;
use function mb_strlen;
use function mb_strtolower;
use function trim;

use const CURLINFO_HTTP_CODE;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_NOBODY;

final class Md5FileHash implements FileHashFactoryInterface
{
    public function __construct(
        private readonly FileHashFactoryInterface $fileHashFactory,
    ) {}

    public function createHash(string $path): string
    {
        return $this->getUsingHead($path) ?? $this->fileHashFactory->createHash($path);
    }

    private function getUsingHead(string $path): ?string
    {
        $ch = curl_init($path);
        $headers = [];

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            static function ($curl, $header) use (&$headers) {
                $len = mb_strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) {
                    return $len;
                }

                $headers[mb_strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            },
        );

        $status = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($status !== true || $code !== Response::HTTP_OK) {
            return null;
        }

        return ($headers['etag'][0] ?? null) ? trim($headers['etag'][0], ' "\'\t\n\r\0\v') : null;
    }
}
