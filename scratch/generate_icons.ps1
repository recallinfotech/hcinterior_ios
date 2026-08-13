Add-Type -AssemblyName System.Drawing

$srcPath = "c:\Users\user\Desktop\hcinterior\hc logo.png"
if (!(Test-Path $srcPath)) {
    Write-Error "Source image not found at $srcPath"
    exit 1
}

$srcImg = [System.Drawing.Image]::FromFile($srcPath)

# Function specifically for iOS 1024x1024 icon without ALPHA CHANNEL (Solid White Background + Format24bppRgb)
function Resize-iOS-NoAlpha($w, $h, $dest) {
    $bmp = New-Object System.Drawing.Bitmap($w, $h, [System.Drawing.Imaging.PixelFormat]::Format24bppRgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    
    # Solid white background eliminates alpha channel transparency required by Apple
    $g.Clear([System.Drawing.Color]::White)
    $g.DrawImage($srcImg, 0, 0, $w, $h)
    
    $bmp.Save($dest, [System.Drawing.Imaging.ImageFormat]::Png)
    $g.Dispose()
    $bmp.Dispose()
}

function Resize-Img($w, $h, $dest) {
    $bmp = New-Object System.Drawing.Bitmap($w, $h)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.DrawImage($srcImg, 0, 0, $w, $h)
    $bmp.Save($dest, [System.Drawing.Imaging.ImageFormat]::Png)
    $g.Dispose()
    $bmp.Dispose()
}

# iOS Universal 1024x1024 Icon - NO ALPHA CHANNEL
$iosPath = "c:\Users\user\Desktop\hcinterior\ios\App\App\Assets.xcassets\AppIcon.appiconset\AppIcon-512@2x.png"
Resize-iOS-NoAlpha 1024 1024 $iosPath
Write-Host "Generated iOS Icon WITHOUT Alpha Channel: $iosPath"

# Android Icons
$sizes = @{
    'mipmap-mdpi' = 48
    'mipmap-hdpi' = 72
    'mipmap-xhdpi' = 96
    'mipmap-xxhdpi' = 144
    'mipmap-xxxhdpi' = 192
}

foreach ($folder in $sizes.Keys) {
    $dim = $sizes[$folder]
    $dir = "c:\Users\user\Desktop\hcinterior\android\app\src\main\res\$folder"
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir | Out-Null
    }
    Resize-Img $dim $dim "$dir\ic_launcher.png"
    Resize-Img $dim $dim "$dir\ic_launcher_round.png"
    Resize-Img $dim $dim "$dir\ic_launcher_foreground.png"
    Write-Host "Generated Android $folder Icons ($dim x $dim)"
}

$srcImg.Dispose()
Write-Host "ALL ICONS GENERATED SUCCESSFULLY!"
