<div class="row">
    <!-- Управление документацией -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Разделы документации</h5>
                    <button class="btn btn-primary btn-sm" onclick="showSectionModal()">
                        Добавить раздел
                    </button>
                </div>

                <div class="docs-tree" id="docsTree">
                    <!-- Дерево разделов будет загружено через JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Редактор документации -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-body">
                <div id="editorContainer">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title" id="currentDocTitle">Выберите раздел</h5>
                        <div class="btn-group">
                            <button class="btn btn-primary" onclick="saveDocument()">Сохранить</button>
                            <button class="btn btn-outline-primary" onclick="previewDocument()">Предпросмотр</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <input type="text" class="form-control" id="docTitle" placeholder="Заголовок">
                    </div>

                    <div class="mb-3">
                        <textarea id="docContent" class="form-control" rows="20" 
                                placeholder="Markdown контент..."></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="docPublished">
                        <label class="form-check-label">Опубликовано</label>
                    </div>
                </div>

                <div id="previewContainer" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">Предпросмотр</h5>
                        <button class="btn btn-outline-primary" onclick="closePreview()">
                            Вернуться к редактированию
                        </button>
                    </div>
                    <div id="previewContent" class="markdown-preview">
                        <!-- Предпросмотр будет здесь -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика использования -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Статистика использования</h5>
                
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="border rounded p-3 text-center">
                            <h6 class="mb-2">Всего статей</h6>
                            <h3 id="totalDocs">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="border rounded p-3 text-center">
                            <h6 class="mb-2">Просмотров сегодня</h6>
                            <h3 id="viewsToday">0</h3>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="border rounded p-3 text-center">
                            <h6 class="mb-2">Популярные разделы</h6>
                            <div id="topSections">
                                <!-- Будет заполнено через JavaScript -->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="border rounded p-3 text-center">
                            <h6 class="mb-2">Поисковые запросы</h6>
                            <div id="topSearches">
                                <!-- Будет заполнено через JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно раздела -->
<div class="modal fade" id="sectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Новый раздел</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="sectionForm">
                    <div class="mb-3">
                        <label class="form-label">Название раздела</label>
                        <input type="text" class="form-control" id="sectionTitle" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Родительский раздел</label>
                        <select class="form-select" id="parentSection">
                            <option value="">Корневой раздел</option>
                            <!-- Будет заполнено через JavaScript -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Порядок сортировки</label>
                        <input type="number" class="form-control" id="sectionOrder" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveSection()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
let currentDocId = null;
let sectionModal = null;

// Инициализация страницы
document.addEventListener('DOMContentLoaded', () => {
    loadDocTree();
    loadDocStats();
    sectionModal = new bootstrap.Modal(document.getElementById('sectionModal'));
});

async function loadDocTree() {
    try {
        const response = await fetch('/api/admin/docs/tree', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const tree = await response.json();
        renderDocTree(tree);
    } catch (error) {
        console.error('Error loading doc tree:', error);
        alert('Ошибка при загрузке структуры документации');
    }
}

function renderDocTree(tree, parentElement = document.getElementById('docsTree')) {
    parentElement.innerHTML = '';
    
    tree.forEach(item => {
        const node = document.createElement('div');
        node.className = 'docs-node';
        
        const content = document.createElement('div');
        content.className = 'd-flex justify-content-between align-items-center p-2';
        content.innerHTML = `
            <span class="docs-title" onclick="loadDoc(${item.id})">${item.title}</span>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="editSection(${item.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-outline-danger" onclick="deleteSection(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        node.appendChild(content);
        
        if (item.children && item.children.length > 0) {
            const childContainer = document.createElement('div');
            childContainer.className = 'docs-children ms-4';
            renderDocTree(item.children, childContainer);
            node.appendChild(childContainer);
        }
        
        parentElement.appendChild(node);
    });
}

async function loadDoc(id) {
    try {
        const response = await fetch(`/api/admin/docs/${id}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const doc = await response.json();
        currentDocId = doc.id;
        
        document.getElementById('currentDocTitle').textContent = doc.title;
        document.getElementById('docTitle').value = doc.title;
        document.getElementById('docContent').value = doc.content;
        document.getElementById('docPublished').checked = doc.published;
        
        document.getElementById('editorContainer').classList.remove('d-none');
        document.getElementById('previewContainer').classList.add('d-none');
    } catch (error) {
        console.error('Error loading doc:', error);
        alert('Ошибка при загрузке документа');
    }
}

async function saveDocument() {
    if (!currentDocId) return;
    
    const data = {
        title: document.getElementById('docTitle').value,
        content: document.getElementById('docContent').value,
        published: document.getElementById('docPublished').checked
    };
    
    try {
        const response = await fetch(`/api/admin/docs/${currentDocId}`, {
            method: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            alert('Документ сохранен');
            loadDocTree();
        } else {
            throw new Error('Failed to save document');
        }
    } catch (error) {
        console.error('Error saving document:', error);
        alert('Ошибка при сохранении документа');
    }
}

function previewDocument() {
    const content = document.getElementById('docContent').value;
    document.getElementById('previewContent').innerHTML = marked.parse(content);
    
    document.getElementById('editorContainer').classList.add('d-none');
    document.getElementById('previewContainer').classList.remove('d-none');
}

function closePreview() {
    document.getElementById('editorContainer').classList.remove('d-none');
    document.getElementById('previewContainer').classList.add('d-none');
}
</script>

<style>
.docs-tree {
    font-size: 0.9rem;
}

.docs-node {
    border-left: 2px solid #dee2e6;
    margin: 0.5rem 0;
}

.docs-title {
    cursor: pointer;
}

.docs-title:hover {
    color: #0d6efd;
}

.markdown-preview {
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}
</style> 