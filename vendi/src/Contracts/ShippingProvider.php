<?php declare(strict_types=1);
namespace Vendi\Contracts;
interface ShippingProvider { public function quote(array $shipment): array; public function createLabel(array $shipment,string $serviceId): array; public function track(string $trackingNumber): array; }