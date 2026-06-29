<?php require APPROOT . '/views/inc/header.php'; ?>

<style>
.hero-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 4rem 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
    margin-bottom: 3rem;
    text-align: center;
}
.hero-title {
    font-weight: 700;
    color: #343a40;
    margin-bottom: 1rem;
}
.hero-subtitle {
    font-size: 1.25rem;
    color: #6c757d;
    margin-bottom: 2rem;
}
.feature-card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}
.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}
.feature-icon {
    font-size: 2.5rem;
    color: #28a745;
    margin-bottom: 1rem;
}
</style>

<div class="container mt-4 mb-5">
    
    <!-- VISTA PARA INVITADOS (Landing Page) -->
    <div class="hero-section">
        <i class="fas fa-wallet mb-3" style="font-size: 4rem; color: #28a745;"></i>
        <h1 class="hero-title display-4"><?php echo $data['title']; ?></h1>
        <p class="hero-subtitle lead mx-auto" style="max-width: 700px;"><?php echo $data['description']; ?></p>
        <div class="mt-4">
            <a href="<?php echo URLROOT; ?>/users/login" class="btn btn-success btn-lg px-4 mr-2 shadow-sm">
                <i class="fas fa-sign-in-alt mr-1"></i> Iniciar Sesión
            </a>
            <a href="<?php echo URLROOT; ?>/users/register" class="btn btn-outline-dark btn-lg px-4 shadow-sm">
                <i class="fas fa-user-plus mr-1"></i> Crear Cuenta
            </a>
        </div>
    </div>

    <!-- Features / Características -->
    <div class="row text-center mt-5 mb-4">
        <div class="col-12 mb-4">
            <h2 class="font-weight-bold" style="color:#343a40;">Control Total sobre tu Dinero</h2>
            <hr style="width: 50px; border-top: 3px solid #28a745;">
        </div>
    </div>

    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body p-4">
                    <i class="fas fa-money-check-alt feature-icon"></i>
                    <h5 class="font-weight-bold">Gestión de Cuentas</h5>
                    <p class="text-muted small">Registra múltiples cuentas, desde tu billetera diaria hasta tus tarjetas de crédito y ahorros.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body p-4">
                    <i class="fas fa-exchange-alt feature-icon text-primary"></i>
                    <h5 class="font-weight-bold">Ingresos y Gastos</h5>
                    <p class="text-muted small">Mantén un registro de cada movimiento de dinero. Categoriza tus transacciones para un mejor análisis.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card feature-card">
                <div class="card-body p-4">
                    <i class="fas fa-hand-holding-usd feature-icon text-warning"></i>
                    <h5 class="font-weight-bold">Control de Préstamos</h5>
                    <p class="text-muted small">Registra el dinero que prestas, lleva control de los abonos que te realizan y no olvides cobrar.</p>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php require APPROOT . '/views/inc/footer.php'; ?>