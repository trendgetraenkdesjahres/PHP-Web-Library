<?php

namespace Library\Utils;

class Convert
{
    public static function iso8601_to_seconds(string $iso8601_input): int
    {
        $regex        = "/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/";
        preg_match('/\d{1,2}[H]/', $iso8601_input, $hours);
        preg_match('/\d{1,2}[M]/', $iso8601_input, $minutes);
        preg_match('/\d{1,2}[S]/', $iso8601_input, $seconds);

        $duration = [
            'hours'   => $hours ? (int) $hours[0] : 0,
            'minutes' => $minutes ? (int) $minutes[0] : 0,
            'seconds' => $seconds ? (int) $seconds[0] : 0,
        ];

        $toltalSeconds = ($duration['hours'] * 60 * 60) + ($duration['minutes'] * 60) + $duration['seconds'];
        return $toltalSeconds;
    }
}



function details_of_videos__youtube_filter(array $video_details): array
{
    $video_details = $video_details['items'][0];
    return [
        'duration' => Convert::iso8601_to_seconds($video_details['contentDetails']['duration']),
        'title' => $video_details['snippet']['title'],
        'id' => $video_details["id"]
    ];
}
