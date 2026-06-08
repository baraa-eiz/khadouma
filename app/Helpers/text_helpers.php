<?php

if (!function_exists('normalize_arabic')) {
    /**
     * Normalize Arabic text by removing diacritics, unifying Alifs, Yaas, and Taa-marbootas.
     */
    function normalize_arabic(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $text = trim($text);

        // Remove Arabic diacritics (harakat)
        $diacritics = [
            'ّ', // Shadda
            'َ', // Fatha
            'ً', // Tanween Fath
            'ُ', // Damma
            'ٌ', // Tanween Damm
            'ِ', // Kasra
            'ٍ', // Tanween Kasr
            'ْ', // Sukoon
            'ـ'  // Tatweel (stretch)
        ];
        $text = str_replace($diacritics, '', $text);

        // Unify Alifs (أ, إ, آ => ا)
        $text = preg_replace('/[أإآ]/u', 'ا', $text);

        // Unify Taa Marboota (ة => ه)
        $text = preg_replace('/ة/u', 'ه', $text);

        // Unify Yaa / Alif Maqsoora (ى => ي)
        $text = preg_replace('/ى/u', 'ي', $text);

        // Collapse multiple spaces
        $text = preg_replace('/\s+/u', ' ', $text);

        return $text;
    }
}

if (!function_exists('slugify')) {
    /**
     * Generate a URL-friendly slug, keeping Arabic characters intact.
     */
    function slugify(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $text = trim($text);

        // Replace spaces and special characters with hyphens
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);

        // Strip leading/trailing hyphens
        $text = trim($text, '-');

        // Convert to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        return empty($text) ? 'n-a' : $text;
    }
}

if (!function_exists('phone_format')) {
    /**
     * Clean and normalize phone numbers for Syrian operators.
     */
    function phone_format(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Keep only digits and plus sign
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Normalize +9639xxxxxxxx to 09xxxxxxxx
        if (strpos($phone, '+963') === 0) {
            $phone = '0' . substr($phone, 4);
        } elseif (strpos($phone, '963') === 0) {
            $phone = '0' . substr($phone, 3);
        }

        return $phone;
    }
}

if (!function_exists('price_format')) {
    /**
     * Format a price value in Syrian Pounds (ل.س).
     */
    function price_format($amount): string
    {
        $val = (float)$amount;
        return number_format($val, 0, '.', ',') . ' ل.س';
    }
}

if (!function_exists('date_format_locale')) {
    /**
     * Format a datetime string or timestamp into a readable date.
     */
    function date_format_locale($date, string $format = 'Y-m-d'): string
    {
        if (empty($date)) {
            return '';
        }
        $timestamp = is_numeric($date) ? (int)$date : strtotime($date);
        return $timestamp ? date($format, $timestamp) : '';
    }
}
