<?php
$page_title = '深空探索檔案館';
require_once __DIR__ . '/includes/header.php';

// 1. 接收分類篩選參數
$current_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$valid_types = ['all', 'solar', 'moon', 'star', 'zodiac', 'constellation', 'deep_sky'];
if (!in_array($current_type, $valid_types)) {
    $current_type = 'all';
}

// 2. 接收關鍵字搜尋參數
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// 3. 依據分類與關鍵字過濾天體資料
$filtered_bodies = array_filter($celestial_bodies, function($item) use ($current_type, $search_query) {
    $match_type = ($current_type === 'all') || ($item['category'] === $current_type);
    if (!$match_type) return false;

    if ($search_query !== '') {
        $keyword = mb_strtolower($search_query, 'UTF-8');
        $haystack = mb_strtolower(
            $item['name_zh'] . ' ' . $item['name_en'] . ' ' . $item['category_zh'] . ' ' . $item['summary'] . ' ' . $item['description'],
            'UTF-8'
        );
        return mb_strpos($haystack, $keyword) !== false;
    }
    return true;
});
?>

<!-- 1. 電影感滿版主視覺 Hero Section (含自動放大縮小星空層) -->
<header class="hero-section">
    <div class="hero-bg-zoom" style="background-image: url('<?php echo $site_config['hero_bg']; ?>');"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content text-center">
        <p class="hero-subtitle">A JOURNEY THROUGH THE UNIVERSE</p>
        <h1 class="hero-title"><?php echo $site_config['title_zh']; ?></h1>
        <p class="hero-en-title"><?php echo $site_config['title_en']; ?></p>
        <p class="hero-desc mx-auto"><?php echo $site_config['tagline']; ?></p>
        <div class="mt-5">
            <a href="#archive" class="btn-cinema">進入觀測檔案 EXPLORE</a>
        </div>
    </div>
    <div class="scroll-indicator">
        <span>SCROLL</span>
        <div class="line"></div>
    </div>
</header>

<!-- 2. 星際檔案畫廊 -->
<section id="archive" class="archive-section py-5">
    <div class="container py-lg-5">
        <div class="text-center mb-5">
            <span class="section-tag">CELESTIAL ARCHIVE // TOTAL <?php echo count($celestial_bodies); ?> RECORDS</span>
            <h2 class="section-title">全宇宙觀測目錄</h2>
            
            <!-- PHP 關鍵字搜尋列 -->
            <form method="GET" action="index.php#archive" class="row justify-content-center mt-4">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="d-flex gap-2">
                        <?php if ($current_type !== 'all'): ?>
                            <input type="hidden" name="type" value="<?php echo htmlspecialchars($current_type); ?>">
                        <?php endif; ?>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" class="form-control cinema-input" placeholder="搜尋天體名稱、黑洞、星雲或關鍵字...">
                        <button type="submit" class="btn-cinema-sm text-nowrap">搜尋</button>
                        <?php if ($search_query !== ''): ?>
                            <a href="index.php?type=<?php echo urlencode($current_type); ?>#archive" class="btn-cinema-sm text-nowrap">清除</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- 搜尋結果狀態提示 -->
        <?php if (empty($filtered_bodies)): ?>
            <div class="text-center py-5">
                <p class="text-secondary fs-5">在目前的深空扇區中找不到符合「<?php echo htmlspecialchars($search_query); ?>」的天體紀錄。</p>
                <a href="index.php#archive" class="btn-cinema-sm mt-3">重置所有觀測條件</a>
            </div>
        <?php else: ?>
            <div class="row g-4 g-lg-5">
                <?php foreach ($filtered_bodies as $body): ?>
                    <?php 
                        $is_rotating_planet = in_array($body['category'], ['solar', 'moon']) && $body['id'] !== 'halley';
                        $planet_zoom = isset($body['zoom']) ? $body['zoom'] : 1.45;
                        $is_saturn = ($body['id'] === 'saturn');
                        $is_jupiter = ($body['id'] === 'jupiter');
                    ?>
                    <div class="col-12 col-md-6">
                        <a href="detail.php?id=<?php echo urlencode($body['id']); ?>" class="celestial-card">
                            <div class="card-img-wrapper <?php echo $is_rotating_planet ? 'planet-stage' : ''; ?>">
                                <?php if ($is_rotating_planet): ?>
                                    <div class="planet-orbit-ring"></div>
                                    <div class="planet-globe <?php echo $is_saturn ? 'saturn-ring-mode' : ''; ?> <?php echo $is_jupiter ? 'jupiter-3d-globe' : ''; ?>" style="--planet-zoom: <?php echo $planet_zoom; ?>;">
                                        <?php if (!$is_jupiter): ?>
                                            <img src="<?php echo htmlspecialchars($body['image']); ?>" alt="<?php echo htmlspecialchars($body['name_zh']); ?>" loading="lazy">
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($body['image']); ?>" alt="<?php echo htmlspecialchars($body['name_zh']); ?>" loading="lazy">
                                <?php endif; ?>
                                <div class="card-vignette"></div>
                                <span class="card-badge"><?php echo htmlspecialchars($body['category_zh']); ?></span>
                            </div>
                            <div class="card-info">
                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                    <h3 class="card-title-zh mb-0"><?php echo htmlspecialchars($body['name_zh']); ?></h3>
                                    <span class="card-title-en"><?php echo htmlspecialchars($body['name_en']); ?></span>
                                </div>
                                <p class="card-summary mb-3"><?php echo htmlspecialchars($body['summary']); ?></p>
                                <div class="card-meta d-flex justify-content-between align-items-center">
                                    <span>觀測距離：<?php echo htmlspecialchars($body['distance']); ?></span>
                                    <span class="read-more">觀測詳情 &rarr;</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>