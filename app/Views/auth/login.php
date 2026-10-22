<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<main class="auth-wrapper">
    <div class="card auth-card">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="<?= base_url('assets/images/logo-transparent.png') ?>"
                     class="auth-logo-img" alt="Gestión Vehicular">
                <h1 class="h4 fw-semibold mt-3 mb-1">Gestión Vehicular</h1>
                <p class="text-secondary small mb-0">Bloomingtec</p>
                <p class="text-secondary small mb-0">Inicia sesión para continuar</p>
            </div>

            <?php if (! empty($errors)): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <div><i class="bi bi-exclamation-circle me-1"></i><?= esc($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-medium">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= esc(old('email')) ?>"
                               placeholder="correo@ejemplo.com" autocomplete="username" autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label small fw-medium">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="••••••••" autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-pine w-100 py-2 fw-medium">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
                </button>
            </form>
        </div>
    </div>
</main>
<?= $this->endSection() ?>
