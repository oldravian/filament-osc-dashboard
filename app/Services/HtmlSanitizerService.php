<?php

namespace App\Services;

class HtmlSanitizerService
{
    public static function addRelAttributes($html) {
        return preg_replace_callback('/<a\s+([^>]+)>/', function($matches) {
            $attributes = $matches[1];
    
            // Add target="_blank"
            if (!preg_match('/\btarget=/', $attributes)) {
                $attributes .= ' target="_blank"';
            }
    
            // Handle rel attribute
            if (preg_match('/\brel="([^"]+)"/', $attributes, $relMatches)) {
                $rels = explode(',', $relMatches[1]);
                $rels = array_map('trim', $rels);
    
                if (!in_array('nofollow', $rels)) {
                    $rels[] = 'nofollow';
                }
                if (!in_array('noreferrer', $rels)) {
                    $rels[] = 'noreferrer';
                }
    
                $attributes = str_replace($relMatches[0], 'rel="' . implode(', ', $rels) . '"', $attributes);
            } else {
                $attributes .= ' rel="nofollow, noreferrer"';
            }
    
            return '<a ' . $attributes . '>';
        }, $html);
    }
}