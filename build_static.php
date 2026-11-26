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
// 安全編碼 JSON，防止 </script> 或特殊引號破壞 HTML
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
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.html#archive" data-filter="all">全部 ({$total_count})</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.html?type=solar#archive" data-filter="solar">行星</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.html?type=moon#archive" data-filter="moon">衛星與彗星</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.html?type=star#archive" data-filter="star">恆星與黑洞</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.html?type=zodiac#archive" data-filter="zodiac">黃道十二星座</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.html?type=constellation#archive" data-filter="constellation">夜空星座</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap" href="index.html?type=deep_sky#archive" data-filter="deep_sky">星系與星雲</a></li>
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
$index_html = wrap_full_page($index_raw, $css_content, $total_count, '深空探索檔案館 | ');

$index_script = <<<HTML
<script id="celestial-data" type="application/json">{$json_bodies}</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rawData = JSON.parse(document.getElementById('celestial-data').textContent);
    const dataMap = {};
    Object.values(rawData).forEach(item => { dataMap[item.id] = item; });

    const cards = Array.from(document.querySelectorAll('#archive .col-12.col-md-6'));
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const searchForm = document.querySelector('#archive form');
    const searchInput = document.querySelector('#archive input[name="q"]');
    let currentType = 'all';

    function filterCards() {
        const kw = (searchInput ? searchInput.value : '').trim().toLowerCase();
        cards.forEach(col => {
            const link = col.querySelector('a');
            const href = link ? link.getAttribute('href') : '';
            const id = new URLSearchParams(href.split('?')[1] || '').get('id');
            const item = dataMap[id];
            
            let matchType = true;
            let matchKw = true;

            if (item) {
                matchType = (currentType === 'all') || (item.category === currentType);
                const text = (item.name_zh + ' ' + item.name_en + ' ' + item.category_zh + ' ' + item.summary + ' ' + item.description).toLowerCase();
                matchKw = !kw || text.includes(kw);
            } else {
                const fallbackText = col.textContent.toLowerCase();
                matchKw = !kw || fallbackText.includes(kw);
            }

            col.style.display = (matchType && matchKw) ? '' : 'none';
        });
    }

    // 1. 綁定上方導覽列所有分類按鈕（包含「全部」）
    navLinks.forEach(link => {
        link.addEventListener('click', e => {
            const href = link.getAttribute('href') || '';
            const explicitFilter = link.getAttribute('data-filter');
            
            if (explicitFilter || href.includes('#archive')) {
                e.preventDefault();
                if (explicitFilter) {
                    currentType = explicitFilter;
                } else if (href.includes('type=')) {
                    currentType = new URLSearchParams(href.split('?')[1].split('#')[0]).get('type') || 'all';
                } else {
                    currentType = 'all';
                }
                if (currentType === 'all' && searchInput) {
                    searchInput.value = '';
                }
                filterCards();
                const archiveEl = document.getElementById('archive');
                if (archiveEl) archiveEl.scrollIntoView({ behavior: 'smooth' });

                const navCollapse = document.getElementById('navbarNav');
                if (navCollapse && navCollapse.classList.contains('show')) {
                    navCollapse.classList.remove('show');
                }
            }
        });
    });

    // 2. 綁定搜尋欄即時過濾與送出按鈕
    if (searchForm) {
        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            filterCards();
        });
    }
    if (searchInput) {
        searchInput.addEventListener('input', filterCards);
        searchInput.addEventListener('keyup', filterCards);
    }

    // 支援從詳情頁點擊分類跳回首頁時自動套用該分類
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('type')) {
        currentType = urlParams.get('type') || 'all';
        filterCards();
    }
});
</script>
</body>
HTML;
$index_html = str_replace('</body>', $index_script, $index_html);
file_put_contents("$out_dir/index.html", $index_html);

echo "<h1>成功！已修復導覽列與即時搜尋功能於 /docs 資料夾。</h1>";
?>