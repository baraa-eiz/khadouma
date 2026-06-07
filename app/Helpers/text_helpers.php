<?php
/**
 * text_helpers.php
 * Khadomeh Arabic Text Helpers
 * 
 * Provides utilities for normalization of Arabic script for indexing/searching
 * and generating search engine friendly slugs.
 */

/**
 * Normalize Arabic text by removing diacritics, unifying alifs, yaas, and taa-marbootas.
 * Crucial for fuzzy search matching (e.g. matching "أحمد" with "احمد" and "احمد ").
 */
if (!function_exists('normalize_arabic')) {
    function normalize_arabic($text) {
        if (empty($text)) {
            return '';
        }

        // Convert to lowercase and trim
        $text = trim($text);

        // Remove Arabic diacritics (harakat)
        $diacritics = [
            '|', // Shadda
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

/**
 * Generate a URL-friendly slug, keeping Arabic characters intact.
 */
if (!function_exists('slugify')) {
    function slugify($text) {
        if (empty($text)) {
            return '';
        }

        // Trim whitespace
        $text = trim($text);

        // Convert spaces and special characters to hyphens
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);

        // Remove trailing/leading hyphens
        $text = trim($text, '-');

        // Convert to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        return empty($text) ? 'n-a' : $text;
    }
}
