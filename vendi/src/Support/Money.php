<?php declare(strict_types=1);
namespace Vendi\Support;
final class Money {
 public static function round(float $v): float { return round($v,2,PHP_ROUND_HALF_UP); }
 public static function line(float $qty,float $price,float $discount,float $taxRate): array {
  if($qty<=0||$price<0||$discount<0||$discount>100||$taxRate<0) throw new \InvalidArgumentException('Valores de venta inválidos');
  $gross=$qty*$price; $disc=$gross*($discount/100); $base=$gross-$disc; $tax=$base*$taxRate;
  return ['gross'=>self::round($gross),'discount'=>self::round($disc),'tax'=>self::round($tax),'total'=>self::round($base+$tax)];
 }
}