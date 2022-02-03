<?php
require_once './koneksi.php';

$count_kategori_aktif = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM categories WHERE status=1");
$count_tag_aktif = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tags WHERE status=1");
$count_playlist_aktif = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM playlists WHERE status=1");
$count_video_aktif = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM videos WHERE status=1");

$kategori_aktif = mysqli_fetch_array($count_kategori_aktif);
$tag_aktif = mysqli_fetch_array($count_tag_aktif);
$playlist_aktif = mysqli_fetch_array($count_playlist_aktif);
$video_aktif = mysqli_fetch_array($count_video_aktif);

$response = [
    'success' => true,
    'message' => 'The main requests on VidLearn can be made using simple API calls integrated wherever you need.',
    'info' => [
        'name' => 'VidLearn',
        'title' => 'Exercises with Video Learning',
        'debug' => 'development',
        'version' => '1.0',
    ],
    'meta' => [
        'total_category' => (int)$kategori_aktif['total'],
        'total_tag' => (int)$tag_aktif['total'],
        'total_playlist' => (int)$playlist_aktif['total'],
        'total_video' => (int)$video_aktif['total'],
        'links' => [
            'base_url' => $api_url,
            'categories' => $api_url . '/categories',
            'tags' => $api_url . '/tags',
            'playlist' => $api_url . '/playlist',
            'search_playlist' => $api_url . '/playlist?search=example',
            'popular_playlist' => $api_url . '/playlist?popular'
        ]
    ]
];

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
http_response_code(200);
echo json_encode($response);