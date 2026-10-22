<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($sectionTitle) ?> · Gestión Vehicular</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?php if ($activeSection === 'configuracion' || $activeSection === 'reportes'): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/dataTables.bootstrap5.min.css') ?>">
    <?php endif; ?></head>
<body class="admin-body">

<nav class="navbar navbar-expand-lg app-navbar sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= site_url('admin') ?>">
            <img src="<?= base_url('assets/images/logo-transparent.png') ?>"
                 class="brand-logo" alt="Gestión Vehicular">
            <span class="d-flex flex-column lh-sm">
                <span class="fw-semibold">Gestión Vehicular</span>
                <span class="small text-secondary">Bloomingtec</span>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto gap-lg-1 mt-3 mt-lg-0 mb-2 mb-lg-0">
                <?php foreach ($menu as $key => $item): ?>
                    <?php $url = ($key === 'inicio') ? site_url('admin') : site_url('admin/' . $key); ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $key === $activeSection ? 'active' : '' ?>"
                           href="<?= esc($url) ?>">
                            <i class="bi <?= esc($item['icon']) ?> me-1"></i><?= esc($item['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="user-actions d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
                <span class="user-chip d-inline-flex align-items-center gap-2">
                    <span class="user-avatar"><?= esc($initials) ?></span>
                    <span class="d-none d-md-inline fw-medium small"><?= esc($userName) ?></span>
                </span>
                <a class="btn btn-outline-pine btn-sm" href="<?= site_url('logout') ?>">
                    <i class="bi bi-box-arrow-right me-1"></i>Salir
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container-fluid px-3 px-lg-4 py-4">
    <?= $content ?>
</main>

<footer class="app-footer text-center py-3 small text-secondary">
    Gestión Vehicular · Panel de administración
</footer>

<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<?php if ($activeSection === 'configuracion'): ?>
    <script src="<?= base_url('assets/js/dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/configuracion.js') ?>"></script>
<?php endif; ?>
<?php if ($activeSection === 'vehiculos'): ?>
    <script src="<?= base_url('assets/js/vehiculos.js') ?>"></script>
<?php endif; ?>
<?php if ($activeSection === 'mantenimientos'): ?>
    <script src="<?= base_url('assets/js/mantenimientos.js') ?>"></script>
<?php endif; ?>
<?php if ($activeSection === 'inicio'): ?>
    <script src="<?= base_url('assets/js/chart.umd.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/dashboard.js') ?>"></script>
<?php endif; ?>
<?php if ($activeSection === 'reportes'): ?>
    <script src="<?= base_url('assets/js/dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/dataTables.bootstrap5.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/chart.umd.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/xlsx.full.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/jspdf.umd.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/reportes.js') ?>"></script>
<?php endif; ?>
</body>
</html>
