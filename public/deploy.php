<?php

// ======= 設定你的 GitHub Secret（請與 Webhook 裡輸入的一樣）=======
$secret = 'v7XfA3p9QeBz28LmTSr4YNcWJkM5tVUg'; // ← 改成你自己設定的 Secret

$logFile = realpath(__DIR__ . '/../storage') . '/deploy.log';
function logWrite($msg) {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
}

// ======= 驗證 HMAC SHA256 Signature =======
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    logWrite("🚫 Signature mismatch. Unauthorized webhook call.");
    exit('Invalid signature');
}

$repoDir = realpath(__DIR__ . '/..');
logWrite("📁 Pulling from: {$repoDir}");
exec("cd {$repoDir} && git pull 2>&1", $output, $return);
logWrite("GIT PULL:\n" . implode("\n", $output));
http_response_code(200);
echo "OK";
