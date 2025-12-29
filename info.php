<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

function lay_thong_tin_nguoi_dung($dinh_danh, $bang_id = false) {
    if ($bang_id) {
        $url = "https://www.tiktok.com/@$dinh_danh";
    } else {
        if (substr($dinh_danh, 0, 1) == '@') {
            $dinh_danh = substr($dinh_danh, 1);
        }
        $url = "https://www.tiktok.com/@$dinh_danh";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response !== false) {
        preg_match_all('/"user_id":"(\d+)"/', $response, $user_id);
        preg_match_all('/"uniqueId":"(.*?)"/', $response, $unique_id);
        preg_match_all('/"nickname":"(.*?)"/', $response, $nickname);
        preg_match_all('/"followerCount":(\d+)/', $response, $followers);
        preg_match_all('/"followingCount":(\d+)/', $response, $following);
        preg_match_all('/"heartCount":(\d+)/', $response, $likes);
        preg_match_all('/"videoCount":(\d+)/', $response, $videos);
        preg_match_all('/"signature":"(.*?)"/', $response, $signature);
        preg_match_all('/"verified":(true|false)/', $response, $verified);
        preg_match_all('/"secUid":"(.*?)"/', $response, $secUid);
        preg_match_all('/"privateAccount":(true|false)/', $response, $privateAccount);
        preg_match_all('/"region":"(.*?)"/', $response, $region);
        preg_match_all('/"heart":(\d+)/', $response, $heart);
        preg_match_all('/"diggCount":(\d+)/', $response, $diggCount);
        preg_match_all('/"friendCount":(\d+)/', $response, $friendCount);
        preg_match_all('/"avatarLarger":"(.*?)"/', $response, $profile_pic);

        $thong_tin = array(
            'user_id' => $user_id[1][0] ?? null,
            'unique_id' => $unique_id[1][0] ?? null,
            'nickname' => $nickname[1][0] ?? null,
            'followers' => $followers[1][0] ?? null,
            'following' => $following[1][0] ?? null,
            'likes' => $likes[1][0] ?? null,
            'videos' => $videos[1][0] ?? null,
            'signature' => $signature[1][0] ?? null,
            'verified' => $verified[1][0] ?? null,
            'secUid' => $secUid[1][0] ?? null,
            'privateAccount' => $privateAccount[1][0] ?? null,
            'region' => $region[1][0] ?? null,
            'heart' => $heart[1][0] ?? null,
            'diggCount' => $diggCount[1][0] ?? null,
            'friendCount' => $friendCount[1][0] ?? null,
            'profile_pic' => isset($profile_pic[1][0]) ? str_replace('\\u002F', '/', $profile_pic[1][0]) : null,
            'tiktok_link' => isset($unique_id[1][0]) ? "https://www.tiktok.com/@{$unique_id[1][0]}" : null
        );

        echo json_encode($thong_tin);
    } else {
        echo json_encode(null);
    }
}

if (isset($_GET['username'])) {
    $dinh_danh = $_GET['username'];
    lay_thong_tin_nguoi_dung($dinh_danh);
}
?>