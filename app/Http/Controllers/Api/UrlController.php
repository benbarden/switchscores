<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class UrlController extends BaseController
{
    public function generateLinkText()
    {
        $request = request();

        $title = $request->title;

        if (!$title) {
            return response()->json(['error' => 'Missing data: title'], 404);
        }

        $linkText = $title;

        $linkText = strip_tags($linkText);
        $linkText = html_entity_decode($linkText);
        $linkText = urldecode($linkText);
        $linkText = str_replace("'", '', $linkText);
        $linkText = preg_replace('/[^A-Za-z0-9]/', ' ', $linkText);
        // Replace multiple spaces with single space
        $linkText = preg_replace('/ +/', ' ', $linkText);
        $linkText = trim($linkText);
        $linkText = strtolower($linkText);
        $linkText = str_replace(' ', '-', $linkText);
        $linkText = str_replace('_', '-', $linkText);

        if ($linkText) {
            $data = array(
                'linkText' => $linkText,
            );
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Failed to generate link text'], 400);
        }
    }
}
