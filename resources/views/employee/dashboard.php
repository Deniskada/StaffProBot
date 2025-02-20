<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Всего смен</h5>
                <h2 class="card-text"><?= $stats['shifts']['total'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Активные смены</h5>
                <h2 class="card-text"><?= $stats['shifts']['active'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Завершенные смены</h5>
                <h2 class="card-text"><?= $stats['shifts']['completed'] ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?php if ($currentShift): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Текущая смена</h5>
                </div>
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">
                        <?= $this->escape($currentShift->facility_name) ?>
                    </h6>
                    <p class="card-text">
                        Начало: <?= date('H:i', strtotime($currentShift->start_time)) ?>
                    </p>
                    <button class="btn btn-danger" onclick="endShift(<?= $currentShift->id ?>)">
                        Завершить смену
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Начать смену</h5>
                </div>
                <div class="card-body">
                    <form action="/shifts/start" method="POST">
                        <?= $this->csrf() ?>
                        <div class="mb-3">
                            <label class="form-label">Выберите объект</label>
                            <select name="facility_id" class="form-select" required>
                                <option value="">-- Выберите объект --</option>
                                <?php foreach ($facilities as $facility): ?>
                                    <option value="<?= $facility->id ?>">
                                        <?= $this->escape($facility->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Начать смену</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Последние смены</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($recentShifts as $shift): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= $this->escape($shift->facility_name) ?></h6>
                            <small><?= date('d.m.Y', strtotime($shift->start_time)) ?></small>
                        </div>
                        <p class="mb-1">
                            <?= date('H:i', strtotime($shift->start_time)) ?> - 
                            <?= $shift->end_time ? date('H:i', strtotime($shift->end_time)) : 'активна' ?>
                        </p>
                        <?php if ($shift->total_hours): ?>
                            <small class="text-muted">
                                <?= $shift->total_hours ?> ч. / <?= $shift->total_amount ?> руб.
                            </small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($recentShifts)): ?>
                    <div class="list-group-item text-center text-muted">
                        Нет завершенных смен
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function endShift(id) {
    if (confirm('Вы уверены, что хотите завершить смену?')) {
        window.location.href = '/shifts/' + id + '/end';
    }
}
</script> 