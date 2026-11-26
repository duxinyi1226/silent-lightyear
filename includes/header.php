<?php require_once __DIR__ . '/data.php'; ?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?><?php echo $site_config['title_zh'] . ' ' . $site_config['title_en']; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Montserrat:wght@300;400;600&family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar navbar-expand-xl navbar-dark fixed-top cinema-nav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center text-nowrap" href="index.php">
            <span class="brand-zh"><?php echo $site_config['title_zh']; ?></span>
            <span class="brand-divider">|</span>
            <span class="brand-en d-none d-sm-inline"><?php echo $site_config['title_en']; ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center gap-2 gap-xl-3 text-center py-3 py-xl-0">
                <li class="nav-item">
                    <a class="nav-link text-nowrap" href="index.php#archive">全部 (<?php echo count($celestial_bodies); ?>)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap" href="index.php?type=solar#archive">行星</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap" href="index.php?type=moon#archive">衛星與彗星</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap" href="index.php?type=star#archive">恆星與黑洞</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap" href="index.php?type=zodiac#archive">黃道十二星座</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap" href="index.php?type=constellation#archive">夜空星座</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-nowrap" href="index.php?type=deep_sky#archive">星系與星雲</a>
                </li>
            </ul>
        </div>
    </div>
</nav>