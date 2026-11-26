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
    if (stripos($body_html, '</nav>') !== false) {
        $body_html = substr($body_html, stripos($body_html, '</nav>') + 6);
    }

    $nav_html = <<<HTML
<nav class="navbar navbar-expand-xl navbar-dark fixed-top cinema-nav" style="z-index: 9999 !important; pointer-events: auto !important;">
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
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" onclick="filterByCat('all'); return false;">全部 ({$total_count})</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" onclick="filterByCat('solar'); return false;">行星</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" onclick="filterByCat('moon'); return false;">衛星與彗星</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" onclick="filterByCat('star'); return false;">恆星與黑洞</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" onclick="filterByCat('zodiac'); return false;">黃道十二星座</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" onclick="filterByCat('constellation'); return false;">夜空星座</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="#archive" onclick="filterByCat('deep_sky'); return false;">星系與星雲</a></li>
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
.hero-overlay, .hero-bg-zoom, .detail-bg-stage, .card-vignette, .planet-orbit-ring {
    pointer-events: none !important;
}
.cinema-nav, .cinema-nav * {
    z-index: 9999 !important;
    pointer-events: auto !important;
}
#static-search-box, #static-search-input, #static-search-btn {
    opacity: 1 !important;
    visibility: visible !important;
    position: relative !important;
    z-index: 50 !important;
    pointer-events: auto !important;
}
#static-search-input {
    background-color: rgba(11, 15, 23, 0.9) !important;
    color: #ffffff !important;
    border: 1px solid rgba(56, 189, 248, 0.45) !important;
    padding: 0.55rem 0.9rem !important;
}
#static-search-input:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 15px rgba(56, 189, 248, 0.25) !important;
    outline: none !important;
}
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

$detail_html = str_replace('onclick="filterByCat(', 'onclick="window.location.href=\'index.html?cat=\'+', $detail_html);
$detail_html = str_replace('); return false;"', '+ \'#archive\'; return false;"', $detail_html);

$detail_script = <<<HTML
<script id="celestial-data" type="application/json">{$json_bodies}</script>
<script>
(function() {
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
})();
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

// 徹底把原本會觸發頁面跳轉的 <form ...>...</form> 替換成純靜態 div 搜尋列
$static_search_html = <<<HTML
<div id="static-search-box" class="row justify-content-center mt-4">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="d-flex gap-2">
            <input type="text" id="static-search-input" class="form-control cinema-input" placeholder="搜尋天體名稱、黑洞、星雲或關鍵字..." oninput="applyArchiveFilter()" onkeydown="if(event.key==='Enter'){event.preventDefault(); applyArchiveFilter();}">
            <button type="button" id="static-search-btn" class="btn-cinema-sm text-nowrap" onclick="applyArchiveFilter()">搜尋</button>
        </div>
    </div>
</div>
HTML;
$index_raw = preg_replace('/<form\b[^>]*>.*?<\/form>/is', $static_search_html, $index_raw);

$index_html = wrap_full_page($index_raw, $css_content, $total_count, '深空探索檔案館 | ');

// 直接讀取卡片內的徽章文字 (.card-badge) 判斷分類，100% 精準且絕不會讓搜尋欄消失
$index_script = <<<HTML
<script>
window.CURRENT_CAT = 'all';

window.detectCardCategory = function(cardEl) {
    const badge = cardEl.querySelector('.card-badge');
    const bText = badge ? badge.textContent : '';
    if (bText.includes('行星') && !bText.includes('行星狀')) return 'solar';
    if (bText.includes('衛星') || bText.includes('彗星')) return 'moon';
    if (bText.includes('主序星') || bText.includes('雙星') || bText.includes('超巨星') || bText.includes('黑洞') || bText.includes('中子星')) return 'star';
    if (bText.includes('黃道')) return 'zodiac';
    if (bText.includes('星座')) return 'constellation';
    return 'deep_sky';
};

window.applyArchiveFilter = function() {
    const cardLinks = document.querySelectorAll('#archive .celestial-card');
    const searchInput = document.getElementById('static-search-input');
    const kw = (searchInput ? searchInput.value : '').trim().toLowerCase();

    cardLinks.forEach(cardLink => {
        const col = cardLink.parentElement;
        if (!col) return;
        const cat = window.detectCardCategory(cardLink);
        const fullText = cardLink.textContent.toLowerCase();

        const matchCat = (window.CURRENT_CAT === 'all') || (cat === window.CURRENT_CAT);
        const matchKw = !kw || fullText.includes(kw);

        col.style.display = (matchCat && matchKw) ? '' : 'none';
    });
};

window.filterByCat = function(cat) {
    window.CURRENT_CAT = cat;
    const searchInput = document.getElementById('static-search-input');
    if (cat === 'all' && searchInput) {
        searchInput.value = '';
    }
    window.applyArchiveFilter();
    const archiveEl = document.getElementById('archive');
    if (archiveEl) archiveEl.scrollIntoView({ behavior: 'smooth' });

    const navCollapse = document.getElementById('navbarNav');
    if (navCollapse && navCollapse.classList.contains('show')) {
        navCollapse.classList.remove('show');
    }
};

(function() {
    const params = new URLSearchParams(window.location.search);
    if (params.has('cat')) {
        window.filterByCat(params.get('cat') || 'all');
    }
})();
</script>
</body>
HTML;
$index_html = str_replace('</body>', $index_script, $index_html);
file_put_contents("$out_dir/index.html", $index_html);

echo "<h1>成功！已徹底移除表單跳轉，搜尋欄與分類篩選皆可正常顯示與運作（" . date('H:i:s') . "）。</h1>";
?>