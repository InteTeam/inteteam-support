@echo off
echo Creating PWA icons for InteTeam CRM...

REM 192x192 icon
powershell -Command "Add-Type -AssemblyName System.Drawing; $bmp192 = New-Object System.Drawing.Bitmap(192, 192); $g192 = [System.Drawing.Graphics]::FromImage($bmp192); $g192.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias; $g192.Clear([System.Drawing.Color]::FromArgb(15, 23, 42)); $brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(59, 130, 246)); $g192.FillRectangle($brush, 30, 30, 132, 132); $font = New-Object System.Drawing.Font('Arial', 45, [System.Drawing.FontStyle]::Bold); $g192.DrawString('CRM', $font, [System.Drawing.Brushes]::White, 38, 68); $bmp192.Save('public\icon-192x192.png'); $g192.Dispose(); $bmp192.Dispose(); $brush.Dispose(); $font.Dispose();"

REM 512x512 icon
powershell -Command "Add-Type -AssemblyName System.Drawing; $bmp512 = New-Object System.Drawing.Bitmap(512, 512); $g512 = [System.Drawing.Graphics]::FromImage($bmp512); $g512.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias; $g512.Clear([System.Drawing.Color]::FromArgb(15, 23, 42)); $brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(59, 130, 246)); $g512.FillRectangle($brush, 80, 80, 352, 352); $font = New-Object System.Drawing.Font('Arial', 120, [System.Drawing.FontStyle]::Bold); $g512.DrawString('CRM', $font, [System.Drawing.Brushes]::White, 105, 185); $bmp512.Save('public\icon-512x512.png'); $g512.Dispose(); $bmp512.Dispose(); $brush.Dispose(); $font.Dispose();"

echo Done! Icons created in public folder.
pause
