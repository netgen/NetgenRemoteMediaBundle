<?php

declare(strict_types=1);

namespace Netgen\RemoteMedia\Core\Provider\Cloudinary\Verifier\Request;

use Cloudinary\SignatureVerifier;
use Netgen\RemoteMedia\Core\RequestVerifierInterface;
use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;

final class Signature implements RequestVerifierInterface
{
    public function verify(Request $request): bool
    {
        try {
            return SignatureVerifier::verifyNotificationSignature(
                $request->getContent(),
                $request->headers->get('x-cld-timestamp'),
                $request->headers->get('x-cld-signature'),
            );
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
