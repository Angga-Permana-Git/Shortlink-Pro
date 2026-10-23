<?php

namespace App\Dto;

use Illuminate\Http\Request;

/**
 * Konteks permintaan HTTP yang perlu dicatat (IP, user agent, referer).
 *
 * Dibangun di controller supaya service tidak pernah menyentuh HTTP
 * (disiplin_struktur.md bagian 1: service dilarang membaca request).
 */
final readonly class RequestContextDTO
{
    public function __construct(
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?string $referer = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            referer: $request->headers->get('referer'),
        );
    }
}
