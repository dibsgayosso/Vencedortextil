<?php declare(strict_types=1);
namespace Vendi\Licensing;
final class BranchGate {
 public function canOperate(array $branch,array $subscription): bool {
  if(($subscription['status']??'')!=='active') return false;
  if(($branch['status']??'')!=='active') return false;
  return !empty($branch['activated_at']);
 }
 public function activationHash(string $token): string { return hash('sha256',$token); }
 public function verify(string $token,string $hash): bool { return hash_equals($hash,$this->activationHash($token)); }
}