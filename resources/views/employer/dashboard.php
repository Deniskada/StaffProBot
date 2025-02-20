<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Объекты</h5>
                <h2 class="card-text"><?= $stats['facilities'] ?></h2>
                <a href="/facilities" class="btn btn-primary">Управление объектами</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Активные смены</h5>
                <h2 class="card-text"><?= $stats['shifts']['active'] ?></h2>
                <a href="/shifts" class="btn btn-primary">Просмотр смен</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Сотрудники</h5>
                <h2 class="card-text"><?= $stats['employees'] ?></h2>
                <a href="/employees" class="btn btn-primary">Список сотрудников</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Активные смены</h5>
                <a href="/shifts" class="btn btn-sm btn-primary">Все смены</a>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($activeShifts as $shift): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= $this->escape($shift->employee_name) ?></h6>
                            <small><?= date('H:i', strtotime($shift->start_time)) ?></small>
                        </div>
                        <p class="mb-1"><?= $this->escape($shift->facility_name) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($activeShifts)): ?>
                    <div class="list-group-item text-center text-muted">
                        Нет активных смен
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Объекты</h5>
                <a href="/facilities" class="btn btn-sm btn-primary">Все объекты</a>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($facilities as $facility): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= $this->escape($facility->name) ?></h6>
                            <span class="badge bg-<?= $facility->status === 'active' ? 'success' : 'secondary' ?>">
                                <?= $facility->status === 'active' ? 'Активен' : 'Неактивен' ?>
                            </span>
                        </div>
                        <p class="mb-1"><?= $this->escape($facility->address) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($facilities)): ?>
                    <div class="list-group-item text-center text-muted">
                        Нет объектов
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 