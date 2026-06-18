<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;

/**
 * Mendeteksi respons HTML dari firewall/proxy (bukan JSON API) saat login outbound ke HRIS API.
 */
final class HrApiOutboundInspector
{
    /** Pesan user-friendly untuk `$response->successful() === false` pada login API. */
    public static function loginFailureHumanMessage(Response $response): string
    {
        $status = $response->status();
        $body = (string) $response->body();
        $ctype = strtolower($response->header('Content-Type') ?? '');

        if ($body !== '' && self::looksLikeMarkupOrFilterPage($body, $ctype)) {
            return self::explainFilterOrHtmlResponse($status, $body);
        }

        $j = json_decode($body, true);
        $apiMsg = null;
        if (is_array($j)) {
            $apiMsg = $j['message'] ?? ($j['error'] ?? null);
            if (is_array($apiMsg)) {
                $apiMsg = null;
            }
        }

        if (is_string($apiMsg) && $apiMsg !== '') {
            return 'Gagal login ke API HRIS: '.$apiMsg;
        }

        if ($body !== '') {
            return 'Gagal login ke API HRIS: '.self::truncatePlain(strip_tags($body));
        }

        return 'Gagal login ke API HRIS (HTTP '.$status.').';
    }

    /** Saat HTTP 2xx tetapi body bukan struktur login JSON yang diharapkan. */
    public static function nonJsonBodyAfterLogin(Response $response): string
    {
        $body = (string) $response->body();
        $ctype = strtolower($response->header('Content-Type') ?? '');
        $status = $response->status();

        if ($body !== '' && self::looksLikeMarkupOrFilterPage($body, $ctype)) {
            return self::explainFilterOrHtmlResponse($status, $body);
        }

        return 'Respons login API tidak valid (bukan JSON). Hubungi TI jika status HTTP '.$status.'.';
    }

    private static function explainFilterBlockSnippet(string $html): ?string
    {
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        if (preg_match('/<h1[^>]*>([^<]+)<\/h1>/i', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        if (preg_match('/<h4[^>]*>([^<]+)<\/h4>/i', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return null;
    }

    /** HTML filter / captive atau respons non-JSON yang menyerupai halaman blokir. */
    private static function looksLikeMarkupOrFilterPage(string $body, string $contentTypeLower): bool
    {
        if (str_contains($contentTypeLower, 'text/html')) {
            return true;
        }

        $l = strtolower($body);
        if (
            preg_match('/<\s*(html|meta|div|frame|iframe|body)\b/i', $body)
            || str_contains($l, '<meta http-equiv=')
            || str_contains($l, 'restricted access')
            || str_contains($l, 'the administrator of this network')
            || str_contains($l, 'categorized as ')
            || str_contains($l, 'webfilter')
            || str_contains($l, 'fortinet')
            || str_contains($l, 'zscaler')
        ) {
            return true;
        }

        return false;
    }

    private static function explainFilterOrHtmlResponse(int $status, string $body): string
    {
        $snippet = self::explainFilterBlockSnippet($body);
        $base = parse_url(config('hris_api.base_url', '') ?: '', PHP_URL_HOST) ?: 'API HRIS';

        $hint = sprintf(
            'Permintaan ke %s tidak sampai pada API sebagai JSON.',
            $base
        ).' Ini biasanya pemblokiran category-based / proxy / DPI jaringan (bukan gagal Laravel).';

        if ($snippet !== null && $snippet !== '') {
            $hint .= ' Teks blokir dari jaringan: '.$snippet.'.';
        }

        $hint .= ' Minta whitelist outbound HTTPS dari IP server payroll ke hostname API (port 443).';
        $hint .= ' HRIS_API_VERIFY_SSL=false tidak mengatasi filter ini; whitelist diperlukan.';

        return $hint;
    }

    private static function truncatePlain(string $s, int $max = 400): string
    {
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;

        return strlen($s) > $max ? substr($s, 0, $max).'…' : $s;
    }
}
