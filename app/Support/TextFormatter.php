<?php

namespace App\Support;

class TextFormatter
{
    public static function linkifyUrls(?string $text, bool $skipTruncatedUrls = false): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $pattern = '~(?<![A-Za-z0-9@])((?:https?://|www\.)[^\s<>"\']+)~i';
        $result = '';
        $offset = 0;

        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] as [$candidate, $position]) {
            $result .= e(substr($text, $offset, $position - $offset));

            if ($skipTruncatedUrls && str_ends_with($candidate, '...')) {
                $result .= e($candidate);
                $offset = $position + strlen($candidate);
                continue;
            }

            [$url, $trailing] = self::splitTrailingPunctuation($candidate);

            if ($url === '') {
                $result .= e($candidate);
                $offset = $position + strlen($candidate);
                continue;
            }

            $href = preg_match('~^https?://~i', $url) ? $url : 'https://' . $url;
            $result .= '<a href="' . e($href) . '" target="_blank" rel="noopener noreferrer">' . e($url) . '</a>' . e($trailing);
            $offset = $position + strlen($candidate);
        }

        return $result . e(substr($text, $offset));
    }

    private static function splitTrailingPunctuation(string $url): array
    {
        $trailing = '';

        while ($url !== '') {
            $last = substr($url, -1);

            if (in_array($last, ['.', ',', ';', ':', '!', '?'], true)) {
                $trailing = $last . $trailing;
                $url = substr($url, 0, -1);
                continue;
            }

            if ($last === ')' && substr_count($url, ')') > substr_count($url, '(')) {
                $trailing = $last . $trailing;
                $url = substr($url, 0, -1);
                continue;
            }

            if ($last === ']' && substr_count($url, ']') > substr_count($url, '[')) {
                $trailing = $last . $trailing;
                $url = substr($url, 0, -1);
                continue;
            }

            if ($last === '}' && substr_count($url, '}') > substr_count($url, '{')) {
                $trailing = $last . $trailing;
                $url = substr($url, 0, -1);
                continue;
            }

            break;
        }

        return [$url, $trailing];
    }
}
