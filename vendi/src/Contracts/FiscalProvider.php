<?php declare(strict_types=1);
namespace Vendi\Contracts;
interface FiscalProvider { public function stamp(array $cfdi): array; public function cancel(string $uuid,string $reason,?string $replacementUuid=null): array; public function status(string $uuid): array; }