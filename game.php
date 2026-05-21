<?php
// CORS制御用ヘッダー
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$go_api_base = "http://localhost:8081"; // GoサーバーのURL

// --- 1. Goから単語JSONを読み込んでフロントに返す ---
if (str_ends_with($path, '/api/words') && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $go_json = @file_get_contents($go_api_base . "/words");
    if ($go_json === false) {
        http_response_code(500);
        echo json_encode(["error" => "Goサーバーが起動していないか、データを取得できませんでした"]);
        exit;
    }
    echo $go_json;
    exit;
}

// --- 2. フロントからスコアを受け取り、Goへ転送、結果を読み込む ---
if (str_ends_with($path, '/api/score') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $js_input = file_get_contents('php://input');

    $ch = curl_init($go_api_base . "/score");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $js_input);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $go_response_json = curl_exec($ch);
    curl_close($ch);

    if ($go_response_json === false) {
        http_response_code(500);
        echo json_encode(["error" => "Goサーバーとの通信に失敗しました"]);
        exit;
    }

    echo $go_response_json;
    exit;
}

http_response_code(404);
echo json_encode(["error" => "Not Found", "debug_path" => $path]);