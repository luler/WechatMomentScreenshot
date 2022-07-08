<?php
/* 自动获取微信公众号文章标题/封面 */

//允许跨域
header('Access-Control-Allow-Origin: *');

if (empty($_GET['url']) || strpos($_GET['url'], 'mp.weixin.qq.com') === false) {
    $result['title'] = '';
    $result['cover'] = '';
} else {
    $ch = curl_init(htmlspecialchars_decode($_GET['url']));
    curl_setopt_array($ch, [
        CURLOPT_VERBOSE => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
    ]);
    $response = curl_exec($ch);

    preg_match('/var msg_title = \'[\S\s]*?\'.html\\(false\\);/', $response, $matches);
    $result['title'] = empty($matches[0]) ? '' : htmlspecialchars_decode(str_replace(['var msg_title = \'', '\'.html(false);'], '', $matches[0]));
    preg_match('/var cdn_url_1_1 = "(.*)";/', $response, $matches);
    $result['cover'] = $matches[1] ?? '';
    if (!empty($result['cover'])) {
        $md5 = md5($_GET['url']);
        $image = 'image/cover/' . $md5 . '.jpg';
        file_put_contents('cover/' . $md5 . '.txt', $_GET['url']);
        file_put_contents($image, file_get_contents($result['cover']));
        $result['cover'] = $image;
    }
    curl_close($ch);
};


$result['success'] = !empty($result['title']) && !empty($result['cover']);
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>