<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller as Controller;

use App\Domain\News\Repository as NewsRepository;

use App\Domain\Url\LinkTitle;

class UrlController extends Controller
{
    public function generateLinkText()
    {
        $request = request();

        $title = $request->title;

        if (!$title) {
            return response()->json(['error' => 'Missing data: title'], 404);
        }

        $linkTitle = new LinkTitle();
        $linkText = $linkTitle->generate($title);

        if ($linkText) {
            $data = array(
                'linkText' => $linkText,
            );
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Failed to generate link text'], 400);
        }
    }

    public function generateNewsUrl()
    {
        $request = request();

        $title = $request->title;
        $newsId = $request->newsId;

        if (!$title) {
            return response()->json(['error' => 'Missing data: title'], 404);
        }

        if ($newsId) {
            $repoNews = new NewsRepository();
            $newsItem = $repoNews->find($newsId);
            if (!$newsItem) {
                return response()->json(['error' => 'Cannot find news item: '.$newsId], 404);
            }
            $newsDate = $newsItem->created_at;
        } else {
            $newsDate = new \DateTime('now');
        }
        $newsDateString = $newsDate->format('Y-m-d');

        $linkTitle = new LinkTitle();
        $linkText = $linkTitle->generate($title);

        if ($linkText) {
            $fullNewsUrl = '/news/'.$newsDateString.'/'.$linkText;
            $data = array(
                'linkText' => $fullNewsUrl,
            );
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Failed to generate link text'], 400);
        }
    }
}
