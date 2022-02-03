<?php
require_once './koneksi.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$url_playlist = $api_url . '/playlist';
if (isset($_GET['category'])) {
    $id_category = $_GET['id_category'];
    $category_query = mysqli_query($koneksi, "SELECT * FROM categories WHERE status=1 AND id=$id_category");
    $category = mysqli_fetch_array($category_query);

    $url_category = $url_playlist . '?category=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($category['nama']))) . '&id_category=' . $category['id'];
}

if (isset($_GET['tag'])) {
    $id_tag = $_GET['id_tag'];
    $tag_query = mysqli_query($koneksi, "SELECT * FROM tags WHERE status=1 AND id=$id_tag");
    $tag = mysqli_fetch_array($tag_query);

    $url_tag = $url_playlist . '?tag=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($tag['nama']))) . '&id_tag=' . $tag['id'];
}

if (isset($_GET['search'])) {
    $search = $_GET['search'];

    $url_search = $url_playlist . '?search=' . $search;
}

$batas = 5;
$halaman = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$i = $halaman_awal + 1;
$previous = $halaman - 1;
$next = $halaman + 1;

$split_data_query = [
    'SELECT playlists.id FROM',
    (isset($_GET['id_tag'])) ? ' playlist_tags,' : '',
    ' playlists WHERE playlists.status=1',
    (isset($_GET['category'])) ? " AND playlists.id_category=$id_category" : "",
    (isset($_GET['tag'])) ? " AND playlist_tags.id_tag=$id_tag AND playlist_tags.id_playlist=playlists.id" : "",
    (isset($_GET['search'])) ? " AND playlists.nama LIKE '%$search%'" : "",
];

$data_query = implode($split_data_query);
$data = mysqli_query($koneksi, $data_query);

$jumlah_data = mysqli_num_rows($data);
$total_halaman = ceil($jumlah_data / $batas);

$spit_query = [
    'SELECT playlists.id, users.nama AS author, playlists.nama, playlists.thumbnail, playlists.deskripsi, playlists.created_at FROM',
    (isset($_GET['id_tag'])) ? 'playlist_tags, ' : '',
    'playlists, users WHERE playlists.id_user=users.id AND playlists.status=1',
    (isset($_GET['category'])) ? "AND playlists.id_category=$id_category" : "",
    (isset($_GET['tag'])) ? "AND playlist_tags.id_tag=$id_tag AND playlist_tags.id_playlist=playlists.id" : "",
    (isset($_GET['search'])) ? "AND playlists.nama LIKE '%$search%'" : "",
    "ORDER BY created_at DESC LIMIT $halaman_awal, $batas",
];

$query = implode(" ", $spit_query);
$playlist_query = mysqli_query($koneksi, $query);

$data = [];
while ($playlist = mysqli_fetch_array($playlist_query)) {
    $waktu = strtotime($playlist['created_at']);
    $url = $url_playlist . '?show=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($playlist['nama']))) . '&id=' . $playlist['id'];

    array_push($data, [
        '#' => (int)$i++,
        'id' => (int)$playlist['id'],
        'name' => $playlist['nama'],
        'author' => $playlist['author'],
        'thumbnail' => $base_url . '/images/thumbnail/' . $playlist['thumbnail'],
        'description' => html_entity_decode(substr($playlist['deskripsi'], 0, 250)) . '.....',
        'created_at' => $playlist['created_at'],
        'created_at_date_format' => date("l, d F Y h:i:s A", $waktu),
        'url' => $url,
    ]);
}

$meta = [
    'total' => (int) $jumlah_data,
    'count' => mysqli_num_rows($playlist_query),
    'per_page' => (int) $batas,
    'total_pages' => (int) $total_halaman,
    'links' => [
        'base_url' => ((isset($url_category)) ? $url_category : ((isset($url_tag)) ? $url_tag : ((isset($url_search)) ? $url_search : $url_playlist))),
        'previous' => ($halaman > 1) ? ((isset($url_category)) ? $url_category . '&page=' . $previous : ((isset($url_tag)) ? $url_tag . '&page=' . $previous : ((isset($url_search)) ? $url_search . '&page=' . $previous : $url_playlist . '?page=' . $previous))) : false,
        'next' => ($halaman < $total_halaman) ? ((isset($url_category)) ? $url_category . '&page=' . $next : ((isset($url_tag)) ? $url_tag . '&page=' . $next : ((isset($url_search)) ? $url_search . '&page=' . $next : $url_playlist . '?page=' . $next))) : false,
    ],
];

$response = [
    'success' => true,
    'title' => ((isset($url_category)) ? 'Playlists in Category ' . $category['nama'] : ((isset($url_tag)) ? 'Playlists in Tag ' . $tag['nama'] : ((isset($url_search)) ? 'Playlist search results "' . $search . '"' : 'Playlists'))),
    'data' => $data,
    'meta' => $meta,
];

if (isset($_GET['show'])) {
    $id = $_GET['id'];
    $playlist_query = mysqli_query($koneksi, "SELECT * FROM playlists WHERE status=1 AND id=$id");
    $playlist = mysqli_fetch_array($playlist_query);

    $url_show = $api_url . '/playlist?show=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($playlist['nama']))) . '&id=' . $playlist['id'];

    $playlist_tags_query = mysqli_query($koneksi, "SELECT tags.id, tags.nama FROM playlist_tags, tags WHERE playlist_tags.id_playlist=$id AND playlist_tags.id_tag=tags.id");

    $id_category = $playlist['id_category'];
    $kategori_query = mysqli_query($koneksi, "SELECT categories.id, categories.nama FROM categories, playlists WHERE categories.id=$id_category");
    $kategori = mysqli_fetch_array($kategori_query);

    $id_user = $playlist['id_user'];
    $user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE status=1 AND id=$id_user");
    $user = mysqli_fetch_array($user_query);

    $user_detail_query = mysqli_query($koneksi, "SELECT * FROM user_detail WHERE id_user=$id_user");
    $user_detail = mysqli_fetch_array($user_detail_query);

    $waktu = strtotime($playlist['created_at']);

    $video_query = mysqli_query($koneksi, "SELECT * FROM videos WHERE status=1 AND id_playlist=$id ORDER BY created_at");

    $tags = [];
    while ($playlist_tags = mysqli_fetch_array($playlist_tags_query)) {
        array_push($tags, [
            'name' => $playlist_tags['nama'],
            'url' => $api_url . '/playlist?tag=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($playlist_tags['nama']))) . '&id_tag=' . $playlist_tags['id'],
        ]);
    }

    $videos = [];
    while ($video = mysqli_fetch_array($video_query)) {
        $waktuV = strtotime($playlist['created_at']);
        array_push($videos, [
            'name' => $video['nama'],
            'url' => $url_show . '&video=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($video['nama']))) . '&id_video=' . $video['id'],
        ]);
    }

    $data = [
        'id' => (int)$playlist['id'],
        'name' => $playlist['nama'],
        'thumbnail' => $base_url . '/images/thumbnail/' . $playlist['thumbnail'],
        'description' => html_entity_decode($playlist['deskripsi']),
        'created_at' => $playlist['created_at'],
        'created_at_date_format' => date("l, d F Y h:i:s A", $waktu),
        'videos' => $videos,
        'category' => [
            'name' => $kategori['nama'],
            'url' => $api_url . '/playlist?category=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($kategori['nama']))) . '&id_category=' . $kategori['id'],
        ],
        'tags' => $tags,
        'author' => [
            'name' => $user['nama'],
            'profile' => $base_url . '/images/avatar/' . $user['profile'],
            'description' => $user_detail['deskripsi'],
        ],
    ];

    $response = [
        'success' => true,
        'title' => 'Show playlist ' . $playlist['nama'],
        'data' => $data,
    ];

    if (isset($_GET['video'])) {
        $id_video = $_GET['id_video'];
        $video_show_query = mysqli_query($koneksi, "SELECT * FROM videos WHERE status=1 AND id=$id_video");
        $video_show = mysqli_fetch_array($video_show_query);

        $popular_query = mysqli_query($koneksi, "SELECT * FROM popular WHERE id_playlist=$id");
        $popular = mysqli_fetch_array($popular_query);
        $cek = mysqli_num_rows($popular_query);

        if ($cek < 1) {
            mysqli_query($koneksi, "INSERT INTO popular (id, id_playlist, count) VALUES('', $id, 1)");
        } else {
            $count = (int) $popular['count'] + 1;
            mysqli_query($koneksi, "UPDATE popular SET count=$count WHERE id_playlist=$id");
        }

        $data = [
            'title' => $video_show['nama'],
            'playlist' => $playlist['nama'],
            'link_watch' => $video_show['link_video'],
            'link_playlist' => $url_show,
            'description' => $video_show['deskripsi'],
        ];

        $response = [
            'success' => true,
            'title' => $playlist['nama'] . ' - ' . $video_show['nama'],
            'data' => $data,
        ];
    }
}

if (isset($_GET['popular'])) {
    $query = "SELECT playlists.id, users.nama AS author, playlists.nama, playlists.thumbnail, playlists.created_at FROM popular, playlists, users WHERE popular.id_playlist=playlists.id AND playlists.id_user=users.id AND playlists.status=1 ORDER BY popular.count DESC LIMIT 4";
    $popular_playlist_query = mysqli_query($koneksi, $query);

    $response = [];
    $data = [];
    while ($popular_playlist = mysqli_fetch_array($popular_playlist_query)) {
        $waktu = strtotime($popular_playlist['created_at']);
        $url = $api_url . '/playlist?show=' . preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($popular_playlist['nama']))) . '&id=' . $popular_playlist['id'];

        array_push($data, [
            'id' => (int)$popular_playlist['id'],
            'name' => $popular_playlist['nama'],
            'author' => $popular_playlist['author'],
            'thumbnail' => $base_url . '/images/thumbnail/' . $popular_playlist['thumbnail'],
            'created_at' => $popular_playlist['created_at'],
            'created_at_date_format' => date("l, d F Y h:i:s A", $waktu),
            'url' => $url,
        ]);
    }

    $response = [
        'success' => true,
        'title' => 'Playlist Populer',
        'data' => $data,
    ];
}

http_response_code(200);
echo json_encode($response);