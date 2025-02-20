<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Объекты</h1>
    <a href="/facilities/create" class="btn btn-primary">Добавить объект</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Адрес</th>
                        <th>Город</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facilities as $facility): ?>
                        <tr>
                            <td><?= $this->escape($facility->name) ?></td>
                            <td><?= $this->escape($facility->address) ?></td>
                            <td><?= $this->escape($facility->city) ?></td>
                            <td>
                                <span class="badge bg-<?= $facility->status === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $facility->status === 'active' ? 'Активен' : 'Неактивен' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="/facilities/<?= $facility->id ?>/edit" 
                                       class="btn btn-outline-primary">
                                        Редактировать
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger"
                                            onclick="deleteFacility(<?= $facility->id ?>)">
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($facilities)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Объекты не найдены
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteFacility(id) {
    if (confirm('Вы уверены, что хотите удалить этот объект?')) {
        fetch('/facilities/' + id + '/delete', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Ошибка при удалении объекта');
            }
        });
    }
}
</script> 