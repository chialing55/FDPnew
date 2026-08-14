# 正式站備份與還原說明

本文件記錄 FDP 正式站的每日備份架構、排程、驗證方式與還原流程。備份由
Linode 先產生一致性的資料庫及應用檔案封存，再由 Synology NAS 主動透過 SSH
及 rsync 拉回。不要直接複製運作中的 MySQL data directory。

## 主機與時區

| 角色 | 主機 | 時區 |
| --- | --- | --- |
| 正式站 | Linode `139.162.91.125` | UTC |
| 備份端 | Synology ForestLab `140.117.33.102` | Asia/Taipei（CST, UTC+8） |

Linode 與 MySQL 均維持 UTC，不因備份排程而修改系統時區。Linode crontab 使用
`CRON_TZ=UTC`；UTC 18:00 對應次日台灣時間 02:00。

## 備份流程

```text
02:00 Asia/Taipei  Linode 產生所有 MySQL databases 的壓縮 dump
02:20 Asia/Taipei  Linode 封存 Laravel storage 與小型 public 資料
03:00 Asia/Taipei  NAS 透過專用 SSH 帳號拉取封存檔及大型資料
                   NAS 驗證 SHA-256、gzip 與 tar 後才發布每日快照
04:00 Asia/Taipei  DSM 對 FDP_Backup shared folder 建立 Btrfs snapshot
```

Linode 只保留最近三天的封存檔，避免正式機磁碟被備份填滿。NAS 使用
rsync `--link-dest` 建立 hard-link 每日快照；未變更的大型檔案不會重複占用
實體空間。

## 備份範圍

| 類型 | 正式站來源 | 備份方式 |
| --- | --- | --- |
| MySQL 全資料庫 | container `fdp_mysql_prod` | `mysqldump --all-databases`、gzip、SHA-256 |
| Laravel storage | container `fdp_app_prod:/app/storage` | tar.gz、SHA-256 |
| 其他 public 資料 | container `fdp_app_prod:/app/public` | 排除大型目錄後 tar.gz、SHA-256 |
| FDP 大型檔案 | `/srv/fdp/FDPfiles` | NAS rsync |
| SSP 大型檔案 | `/srv/fdp/SSPfiles` | NAS rsync |

public 封存刻意排除 `FDPfiles`、`SSPfiles` 與 `storage`，避免同一份資料重複備份。
運作中的 `/srv/fdp/mysql` 不以 rsync 備份；資料庫以一致性的 SQL dump 為準。

## Linode 設定

### 帳號與路徑

- 執行備份的帳號：`chialing`
- NAS 專用唯讀帳號：`fdpbackup`
- 資料庫腳本：`/home/chialing/bin/fdp-db-backup.sh`
- 應用檔案腳本：`/home/chialing/bin/fdp-files-backup.sh`
- 資料庫封存：`/srv/fdp/backups/mysql`
- 應用封存：`/srv/fdp/backups/app`

`fdpbackup` 沒有 sudo 權限。NAS 使用獨立 Ed25519 金鑰登入，Linode 的
`authorized_keys` 以來源 IP `140.117.33.102` 及 `restrict` 選項限制該金鑰。
私鑰、資料庫密碼與 `.env.production` 不可放入 Git。

### 排程

使用 `crontab -l` 應看到：

```cron
CRON_TZ=UTC
0 18 * * * /home/chialing/bin/fdp-db-backup.sh >> /srv/fdp/backups/mysql/backup.log 2>&1
20 18 * * * /home/chialing/bin/fdp-files-backup.sh >> /srv/fdp/backups/app/backup.log 2>&1
```

兩支腳本都先寫入 `.part` 暫存檔，完成完整性檢查後才以 `mv` 發布正式檔案，
並最後更新 `.ready`。NAS 只接受 `.ready` 指向的成品。

### 日常檢查

```bash
ls -lah /srv/fdp/backups/mysql
ls -lah /srv/fdp/backups/app
cat /srv/fdp/backups/mysql/.ready
cat /srv/fdp/backups/app/.ready
tail -50 /srv/fdp/backups/mysql/backup.log
tail -50 /srv/fdp/backups/app/backup.log
```

手動執行及驗證：

```bash
/home/chialing/bin/fdp-db-backup.sh
/home/chialing/bin/fdp-files-backup.sh

cd /srv/fdp/backups/mysql
sha256sum -c "$(cat .ready).sha256"
gzip -t "$(cat .ready)"

cd /srv/fdp/backups/app
sha256sum -c "$(ls -1t fdp-files-*.sha256 | head -1)"
```

## NAS 設定

### 帳號與路徑

- DSM shared folder：`/volume1/FDP_Backup`
- SSH 私鑰：`~/.ssh/fdp_linode_backup_ed25519`
- 拉取腳本：`/volume1/FDP_Backup/scripts/fdp-pull-backup.sh`
- 每日快照：`/volume1/FDP_Backup/snapshots`
- 最新成功快照：`/volume1/FDP_Backup/latest`
- 執行紀錄：`/volume1/FDP_Backup/logs`
- 防重複執行鎖：`/volume1/FDP_Backup/.backup.lock`

NAS 腳本先建立 `.incomplete-<時間>` 目錄。所有 rsync 與 checksum 驗證成功後，
才將它改名為正式快照並更新 `latest` symbolic link。失敗的 incomplete 目錄不會
冒充成功備份。

SSH 測試：

```bash
ssh -i ~/.ssh/fdp_linode_backup_ed25519 \
  -o IdentitiesOnly=yes \
  -o BatchMode=yes \
  fdpbackup@139.162.91.125 \
  'id; cat /srv/fdp/backups/mysql/.ready'
```

手動啟動完整拉取：

```bash
nohup sh /volume1/FDP_Backup/scripts/fdp-pull-backup.sh \
  > /volume1/FDP_Backup/logs/manual-backup.log 2>&1 &
```

監看：

```bash
pgrep -af fdp-pull-backup
tail -f /volume1/FDP_Backup/logs/manual-backup.log
du -sh /volume1/FDP_Backup/snapshots/.incomplete-*
```

### DSM 排程

在 DSM「控制台 → 任務排程器」建立每日台灣時間 03:00 執行的使用者定義腳本：

```bash
sh /volume1/FDP_Backup/scripts/fdp-pull-backup.sh \
  >> /volume1/FDP_Backup/logs/scheduled-backup.log 2>&1
```

排程使用擁有 shared folder 寫入權限及 SSH 私鑰的 `chialing` 帳號。NAS 腳本
保留最近 14 個成功快照；輪替只比對已發布的日期快照目錄，不刪除 `latest` 指向
的最新快照。

### DSM Snapshot Replication

`/volume1` 使用 Btrfs。除了 rsync hard-link 每日快照之外，DSM 每天 04:00
再對整個 `FDP_Backup` shared folder 建立檔案系統快照，以防 NAS 上的誤刪、
腳本錯誤或惡意竄改。此快照位於同一台 NAS，不能取代異機或異地備份。

Snapshot Replication 使用與 LTSER 相同的進階保留策略：

- 保留過去 30 天內的所有快照。
- 每日保留最新快照，保留 14 天。
- 每週保留最新快照，保留 8 週。
- 每月保留最新快照，保留 12 個月。
- 每年保留最新快照，保留 5 年。
- 無論上述規則，至少保留最新 5 份快照。
- 不啟用每小時快照。
- 不建立 replication task。
- 初始快照不鎖定。

## NAS 備份驗證

```bash
ls -l /volume1/FDP_Backup/latest
du -sh /volume1/FDP_Backup/latest
find /volume1/FDP_Backup/latest -maxdepth 2 -type f | head
tail -50 /volume1/FDP_Backup/logs/scheduled-backup.log
```

進入最新快照驗證封存檔：

```bash
cd /volume1/FDP_Backup/latest/database
DB_FILE="$(cat .ready)"
sha256sum -c "${DB_FILE}.sha256"
gzip -t "$DB_FILE"

cd /volume1/FDP_Backup/latest/app
CHECKSUM_FILE="$(ls -1t fdp-files-*.sha256 | head -1)"
sha256sum -c "$CHECKSUM_FILE"
```

至少每季做一次實際還原演練。只有 checksum 正常不足以證明 SQL 可以完整匯入。

## 還原原則

還原前先停止會寫入資料的服務，並保留故障現場，不要直接覆蓋唯一一份正式資料。

### MySQL

1. 從 NAS 選擇已驗證的 `fdp-all-*.sql.gz`。
2. 複製到 Linode 的暫存位置。
3. 在隔離的測試 MySQL container 先試還原。
4. 確認 databases、tables、views、routines、events 與筆數後，才安排正式還原。

基本匯入形式如下；正式執行前須再次確認目標 container：

```bash
gzip -cd fdp-all-YYYYMMDDTHHMMSSZ.sql.gz \
  | docker exec -i fdp_mysql_prod sh -c \
      'mysql -uroot -p"$MYSQL_ROOT_PASSWORD"'
```

此命令會改寫資料庫，不能拿來做日常驗證。

### Laravel storage 與 public

先解壓到暫存目錄檢查，不直接覆蓋運作中的 volume：

```bash
mkdir -p /tmp/fdp-restore-check
tar -xzf fdp-storage-YYYYMMDDTHHMMSSZ.tar.gz \
  -C /tmp/fdp-restore-check
```

確認內容與權限後，再以維護窗口內的受控程序放回 Docker volume。

### FDPfiles 與 SSPfiles

從選定的 NAS snapshot 使用 rsync 還原。正式執行前先加 `--dry-run` 查看差異，
不要在未確認的情況下使用 `--delete`。

## 目前建置狀態

截至 2026-07-30：

- Linode 資料庫手動備份、gzip 與 SHA-256 驗證成功。
- Linode 應用檔案手動備份、tar 與 SHA-256 驗證成功。
- Linode 兩項每日排程已建立。
- NAS 專用 SSH 帳號、來源 IP 限制與 rsync 測試成功。
- NAS hard-link 快照測試成功。
- NAS 首次約 34 GB 完整拉取及三項封存完整性驗證成功。
- DSM 每日 03:00 排程已建立，並已由任務排程器手動執行成功。
- NAS 已啟用最近 14 個成功快照的輪替。
- DSM Snapshot Replication 已建立初始 Btrfs snapshot，並排程每日 04:00
  執行；進階保留策略與 LTSER 相同。
- 實際還原演練仍待執行。
