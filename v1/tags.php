<?php
require_once './koneksi.php';

$batas = 5;
$halaman = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$i = $halaman_awal + 1;
$previous = $halaman - 1;
$next = $halaman + 1;

$count = mysqli_query($koneksi, "SELECT * FROM tags WHERE status=1");
$jumlah_data = mysqli_num_rows($count);
$total_halaman = ceil($jumlah_data / $batas);

$query = "SELECT * FROM tags WHERE status=1 ORDER BY nama LIMIT $halaman_awal, $batas";
$playlist_tags_query = mysqli_query($koneksi, $query);

$data = [];
while($playlist_tags = mysqli_fetch_array($playlist_tags_query)) {
    $url = $api_url . '/playlist?tag=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($playlist_tags['nama']))) . '&id_tag=' . $playlist_tags['id'];
    
    $id = $playlist_tags['id'];
    $count_query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM playlist_tags WHERE id_tag=$id");
    $count = mysqli_fetch_array($count_query);

    array_push($data, [
        '#' => (int)$i++,
        'id' => (int)$playlist_tags['id'],
        'name' => $playlist_tags['nama'],
        'total_playlist' => (int)$count['total'],
        'url' => $url
    ]);
}

$meta = [
    'total' => (int)$jumlah_data,
    'count' => mysqli_num_rows($playlist_tags_query),
    'per_page' => (int)$batas,
    'total_pages' => (int)$total_halaman,
    'links' => [
        'base_url' => $api_url . '/tags',
        'previous' => ($halaman > 1) ? $api_url . '/tags?page=' . $previous : false,
        'next' => ($halaman < $total_halaman) ? $api_url . '/tags?page=' . $next : false
    ]
];

$response =[
    'success' => true,
    'title' => 'Tags',
    'data' => $data,
    'meta' => $meta
];

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
http_response_code(200);
echo json_encode($response);