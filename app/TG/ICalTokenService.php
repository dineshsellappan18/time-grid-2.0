<?php

namespace App\TG;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Timegridio\Concierge\Models\Business;

class ICalTokenService
{
    private const TABLE = 'ical_tokens';

    public function issue(Business $business): string
    {
        $plaintext = $this->generateToken();
        $hash = $this->hashToken($plaintext);
        $timestamp = Carbon::now();

        DB::table(self::TABLE)->insert([
            'business_id' => $business->id,
            'token_hash'  => $hash,
            'rotated_at'  => $timestamp,
            'created_at'  => $timestamp,
            'updated_at'  => $timestamp,
        ]);

        return $plaintext;
    }

    public function validate(Business $business, string $token): bool
    {
        if ($token === '' || strlen($token) > 128) {
            return false;
        }

        $candidateHash = $this->hashToken($token);

        $stored = DB::table(self::TABLE)
            ->where('business_id', $business->id)
            ->whereNull('revoked_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($stored === null) {
            return false;
        }

        $valid = hash_equals($stored->token_hash, $candidateHash);

        if ($valid) {
            DB::table(self::TABLE)
                ->where('id', $stored->id)
                ->update(['last_used_at' => Carbon::now()]);
        }

        return $valid;
    }

    public function rotate(Business $business): string
    {
        DB::table(self::TABLE)
            ->where('business_id', $business->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => Carbon::now()]);

        return $this->issue($business);
    }

    public function getActiveToken(Business $business): ?object
    {
        return DB::table(self::TABLE)
            ->where('business_id', $business->id)
            ->whereNull('revoked_at')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function backfillLegacy(Business $business, string $legacyToken): bool
    {
        $hash = $this->hashToken($legacyToken);

        $exists = DB::table(self::TABLE)
            ->where('token_hash', $hash)
            ->exists();

        if ($exists) {
            return false;
        }

        $timestamp = Carbon::now();

        DB::table(self::TABLE)->insert([
            'business_id' => $business->id,
            'token_hash'  => $hash,
            'rotated_at'  => null,
            'created_at'  => $timestamp,
            'updated_at'  => $timestamp,
        ]);

        return true;
    }

    public function isRevoked(Business $business, string $token): bool
    {
        $candidateHash = $this->hashToken($token);

        return DB::table(self::TABLE)
            ->where('business_id', $business->id)
            ->where('token_hash', $candidateHash)
            ->whereNotNull('revoked_at')
            ->exists();
    }

    private function generateToken(): string
    {
        return Str::random(32);
    }

    private function hashToken(string $token): string
    {
        $pepper = config('app.key', 'timegrid-ical-pepper');

        return hash('sha256', $pepper . $token);
    }
}
