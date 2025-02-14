<div class="card">
    <div class="card-header">
        <h1 class="card-title">Добавить объект</h1>
    </div>
    <div class="card-body">
        <form action="/facilities/store" method="POST">
            <?= $this->csrf() ?>
            
            <div class="mb-3">
                <label class="form-label">Название</label>
                <input type="text" name="name" class="form-control" required
                       value="<?= $this->escape($this->old('name')) ?>">
                <?php if ($this->hasError('name')): ?>
                    <div class="invalid-feedback d-block">
                        <?= $this->escape($this->error('name')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Адрес</label>
                <textarea name="address" class="form-control" required rows="2"
                ><?= $this->escape($this->old('address')) ?></textarea>
                <?php if ($this->hasError('address')): ?>
                    <div class="invalid-feedback d-block">
                        <?= $this->escape($this->error('address')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Город</label>
                <input type="text" name="city" class="form-control" required
                       value="<?= $this->escape($this->old('city')) ?>">
                <?php if ($this->hasError('city')): ?>
                    <div class="invalid-feedback d-block">
                        <?= $this->escape($this->error('city')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Координаты</label>
                <input type="text" name="coordinates" class="form-control"
                       value="<?= $this->escape($this->old('coordinates')) ?>"
                       placeholder="55.7558, 37.6173">
                <?php if ($this->hasError('coordinates')): ?>
                    <div class="invalid-feedback d-block">
                        <?= $this->escape($this->error('coordinates')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Статус</label>
                <select name="status" class="form-select" required>
                    <option value="active" <?= $this->old('status') === 'active' ? 'selected' : '' ?>>
                        Активен
                    </option>
                    <option value="inactive" <?= $this->old('status') === 'inactive' ? 'selected' : '' ?>>
                        Неактивен
                    </option>
                </select>
                <?php if ($this->hasError('status')): ?>
                    <div class="invalid-feedback d-block">
                        <?= $this->escape($this->error('status')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between">
                <a href="/facilities" class="btn btn-outline-secondary">Отмена</a>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div> 