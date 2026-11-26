<?php
require_once __DIR__ . '/includes/data.php';

$out_dir = __DIR__ . '/docs';
if (!is_dir($out_dir)) {
    mkdir($out_dir, 0777, true);
}

// 複製 assets 資料夾
function copy_dir($src, $dst) {
    if (!is_dir($dst)) mkdir($dst, 0777, true);
    foreach (scandir($src) as $file) {
        if ($file === '.' || $file === '..') continue;
        if (is_dir("$src/$file")) copy_dir("$src/$file", "$dst/$file");
        else copy("$src/$file", "$dst/$file");
    }
}
if (is_dir(__DIR__ . '/assets')) {
    copy_dir(__DIR__ . '/assets', "$out_dir/assets");
}

$css_content = file_exists(__DIR__ . '/assets/style.css') ? file_get_contents(__DIR__ . '/assets/style.css') : '';
$total_count = count($celestial_bodies);
$json_bodies = json_encode($celestial_bodies, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

function wrap_full_page($body_html, $css_content, $total_count, $title_prefix = '') {
    if (stripos($body_html, '<nav') !== false) {
        $body_html = substr($body_html, stripos($body_html, '<nav'));
    } elseif (stripos($body_html, '<header') !== false) {
        $body_html = substr($body_html, stripos($body_html, '<header'));
    }
    if (stripos($body_html, '</body>') !== false) {
        $body_html = substr($body_html, 0, stripos($body_html, '</body>'));
    }

    $has_nav = (stripos($body_html, '<nav') !== false);
    $nav_html = $has_nav ? '' : <<<HTML
<nav class="navbar navbar-expand-xl navbar-dark fixed-top cinema-nav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center text-nowrap" href="index.html">
            <span class="brand-zh">寂靜光年</span>
            <span class="brand-divider">|</span>
            <span class="brand-en d-none d-sm-inline">SILENT LIGHTYEAR</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center gap-2 gap-xl-3 text-center py-3 py-xl-0">
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" data-cat="all">全部 ({$total_count})</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" data-cat="solar">行星</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" data-cat="moon">衛星與彗星</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" data-cat="star">恆星與黑洞</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" data-cat="zodiac">黃道十二星座</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" data-cat="constellation">夜空星座</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" data-cat="deep_sky">星系與星雲</a></li>
            </ul>
        </div>
    </div>
</nav>
HTML;

    return <<<HTML
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title_prefix}寂靜光年 SILENT LIGHTYEAR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Montserrat:wght@300;400;600&family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
{$css_content}
.cinema-nav { z-index: 1050 !important; }
.hero-content, .archive-section, .btn-cinema, .btn-cinema-sm, .celestial-card, .detail-content { position: relative; z-index: 10; }
.hero-overlay, .hero-bg-zoom, .detail-bg-stage, .card-vignette { pointer-events: none !important; }
    </style>
</head>
<body>
{$nav_html}
{$body_html}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;
}

// 1. 產生詳情頁 detail.html
ob_start();
$_GET['id'] = 'saturn';
include __DIR__ . '/detail.php';
$detail_raw = ob_get_clean();
$detail_raw = str_replace('index.php', 'index.html', $detail_raw);
$detail_html = wrap_full_page($detail_raw, $css_content, $total_count, '天體觀測詳情 | ');

$detail_script = <<<HTML
<script id="celestial-data" type="application/json">{$json_bodies}</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rawData = JSON.parse(document.getElementById('celestial-data').textContent);
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id') || 'saturn';
    const body = rawData[id] || Object.values(rawData).find(x => x.id === id) || rawData['saturn'];
    
    document.title = body.name_zh + ' ' + body.name_en + ' | 寂靜光年 SILENT LIGHTYEAR';
    document.querySelector('.detail-category').textContent = body.category_zh;
    document.querySelector('.detail-title-zh').textContent = body.name_zh;
    document.querySelector('.detail-title-en').textContent = body.name_en;
    
    const vals = document.querySelectorAll('.telemetry-item .value');
    if (vals.length >= 4) {
        vals[0].textContent = body.distance;
        vals[1].textContent = body.light_time;
        vals[2].textContent = body.magnitude;
        vals[3].textContent = body.category_zh;
    }
    document.querySelector('.story-lead').textContent = body.summary;
    document.querySelector('.story-text').innerHTML = body.description.replace(/\\n/g, '<br>');

    const stage = document.querySelector('.detail-bg-stage');
    const isPlanet = ['solar', 'moon', 'star'].includes(body.category) && !['halley', 'sagittarius_a', 'crab_pulsar'].includes(body.id);
    if (body.id === 'jupiter') {
        stage.className = 'detail-bg-stage detail-jupiter-closeup';
        stage.style.backgroundImage = '';
    } else if (isPlanet) {
        stage.className = 'detail-bg-stage cinema-planet-closeup';
        stage.style.backgroundImage = "url('" + body.image + "')";
    } else {
        stage.className = 'detail-bg-stage cinema-cosmos-zoom';
        stage.style.backgroundImage = "url('" + body.image + "')";
    }
});
</script>
</body>
HTML;
$detail_html = str_replace('</body>', $detail_script, $detail_html);
file_put_contents("$out_dir/detail.html", $detail_html);

// 2. 產生首頁 index.html
ob_start();
$_GET = [];
include __DIR__ . '/index.php';
$index_raw = ob_get_clean();
$index_raw = str_replace(['detail.php', 'index.php'], ['detail.html', 'index.html'], $index_raw);

// 直接在產生 HTML 時，把每個天體的 category 寫進卡片的 data-category 屬性中！
foreach ($celestial_bodies as $b) {
    $search_str = 'href="detail.html?id=' . urlencode($b['id']) . '" class="celestial-card"';
    $replace_str = 'href="detail.html?id=' . urlencode($b['id']) . '" class="celestial-card" data-category="' . htmlspecialchars($b['category']) . '"';
    $index_raw = str_replace($search_str, $replace_str, $index_raw);
}

$index_html = wrap_full_page($index_raw, $css_content, $total_count, '深空探索檔案館 | ');

// 純 DOM 過濾腳本：完全不依賴 JSON 解析，直接比對 data-category 與卡片文字
$index_script = <<<HTML
<script>
window.addEventListener('load', () => {
    const cards = Array.from(document.querySelectorAll('#archive .col-12.col-md-6'));
    const navLinks = Array.from(document.querySelectorAll('.navbar-nav .nav-link'));
    const searchForm = document.querySelector('#archive form');
    const searchInput = document.querySelector('#archive input[name="q"]');
    let currentCat = 'all';

    function runFilter() {
        const kw = (searchInput ? searchInput.value : '').trim().toLowerCase();
        cards.forEach(col => {
            const cardLink = col.querySelector('.celestial-card');
            const cat = cardLink ? (cardLink.getAttribute('data-category') || '') : '';
            const fullText = col.textContent.toLowerCase();

            const matchCat = (currentCat === 'all') || (cat === currentCat);
            const matchKw = !kw || fullText.includes(kw);

            col.style.setProperty('display', (matchCat && matchKw) ? '' : 'none', 'important');
        });
    }

    // 綁定上方所有導覽按鈕
    navLinks.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const text = link.textContent.trim();
            if (link.hasAttribute('data-cat')) {
                currentCat = link.getAttribute('data-cat');
            } else if (text.includes('全部')) {
                currentCat = 'all';
            } else if (text.includes('行星')) {
                currentCat = 'solar';
            } else if (text.includes('衛星')) {
                currentCat = 'moon';
            } else if (text.includes('恆星')) {
                currentCat = 'star';
            } else if (text.includes('黃道')) {
                currentCat = 'zodiac';
            } else if (text.includes('夜空')) {
                currentCat = 'constellation';
            } else if (text.includes('星系')) {
                currentCat = 'deep_sky';
            } else {
                currentCat = 'all';
            }

            if (currentCat === 'all' && searchInput) {
                searchInput.value = '';
            }
            runFilter();
            const archiveEl = document.getElementById('archive');
            if (archiveEl) archiveEl.scrollIntoView({ behavior: 'smooth' });
        });
    });

    // 綁定搜尋表單與輸入框
    if (searchForm) {
        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            runFilter();
        });
    }
    if (searchInput) {
        searchInput.addEventListener('input', runFilter);
        searchInput.addEventListener('keyup', runFilter);
    }
});
</script>
</body>
HTML;
$index_html = str_replace('</body>', $index_script, $index_html);
file_put_contents("$out_dir/index.html", $index_html);

echo "<h1>成功！已改用純 DOM 直接過濾模式並更新於 /docs 資料夾。</h1>";
?>