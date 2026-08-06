param(
    [string]$Output = "",
    [string]$DbUrl = $env:RENDER_DB_URL
)

if (-not $DbUrl) { $DbUrl = $env:DB_URL }
if (-not $DbUrl) { $DbUrl = $env:DATABASE_URL }

if (-not $DbUrl) {
    Write-Error "Set RENDER_DB_URL, DB_URL, or DATABASE_URL (External Database URL from Render Dashboard)"
    exit 1
}

if (-not $Output) {
    $stamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $Output = "storage/backups/render-backup-$stamp.sql"
}

$dir = Split-Path $Output -Parent
if ($dir -and -not (Test-Path $dir)) {
    New-Item -ItemType Directory -Path $dir -Force | Out-Null
}

Write-Host "==> Backup to $Output"
& pg_dump $DbUrl --no-owner --no-acl --clean --if-exists | Set-Content -Path $Output -Encoding utf8
$size = (Get-Item $Output).Length
Write-Host "==> Done ($size bytes)"
