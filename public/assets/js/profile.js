function enableEdit() {
    const form = document.getElementById('profile-form');
    const inputs = form.querySelectorAll('input:not([type="hidden"])');
    const actions = form.querySelector('.form-actions');
    
    inputs.forEach(input => {
        if (input.name !== 'email' && input.name !== 'role' && input.name !== 'status') {
            input.removeAttribute('readonly');
        }
    });
    
    actions.style.display = 'flex';
}

function cancelEdit() {
    const form = document.getElementById('profile-form');
    const inputs = form.querySelectorAll('input:not([type="hidden"])');
    const actions = form.querySelector('.form-actions');
    
    inputs.forEach(input => {
        input.setAttribute('readonly', true);
    });
    
    actions.style.display = 'none';
    form.reset();
} 