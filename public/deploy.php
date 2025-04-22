<?php

// ======= 設定 GitHub Webhook Secret =======
$secret = 'v7XfA3p9QeBz28LmTSr4YNcWJkM5tVUg';

// ======= Log 檔案位置（儲存於 storage 下）=======
$logFile = realpath(__DIR__ . '/../storage') . '/deploy.log';
function logWrite($msg) {
    global $logFile;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "({$ip}) " . $msg . "\n", FILE_APPEND);
}

// ======= 驗證 Webhook 簽章 =======
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');
$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    logWrite("🚫 Signature mismatch. Unauthorized webhook call.");
    exit('Invalid signature');
}

// ======= 安全標記 git safe.directory（防止權限錯）=======
$repoDir = realpath(__DIR__ . '/..');
exec("git config --global --add safe.directory {$repoDir}", $outSafe, $returnSafe);
logWrite("🛡️ Marked as safe.directory: {$repoDir}");

// ======= 執行 git pull =======
logWrite("📁 Pulling from: {$repoDir}");
exec("cd {$repoDir} && git pull 2>&1", $gitOutput, $gitReturn);
logWrite("🔄 GIT PULL:\n" . implode("\n", $gitOutput));


// ======= composer install（只在 composer.lock 更新時執行）=======
$lockFile = "{$repoDir}/composer.lock";

// 如果 composer.lock 最近有更新（30 秒內），才執行 install
if (file_exists($lockFile) && time() - filemtime($lockFile) < 30) {
    logWrite("📦 composer.lock recently updated, running composer install...");
    exec("cd {$repoDir} && composer install --no-dev --optimize-autoloader 2>&1", $composerOutput, $composerReturn);
    logWrite("🎶 COMPOSER:\n" . implode("\n", $composerOutput));
} else {
    logWrite("📦 composer.lock unchanged, skipping composer install.");
}


// ======= 回應 GitHub =========
http_response_code(200);
echo "✅ Deploy complete\n";
