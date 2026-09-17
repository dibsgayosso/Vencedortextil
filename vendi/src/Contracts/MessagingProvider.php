<?php declare(strict_types=1);
namespace Vendi\Contracts;
interface MessagingProvider { public function sendText(string $to,string $message): array; public function sendDocument(string $to,string $url,string $caption=''): array; }